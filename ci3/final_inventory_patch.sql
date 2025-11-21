-- ============================================================
-- final_inventory_patch.sql — Consolidated, idempotent
-- ============================================================

-- ⛳ Set your target DB:
USE shulelabs_staging;   -- change to USE shule; for production

SET SESSION sql_mode = 'STRICT_ALL_TABLES';

-- ============================================================
-- 1) Billable / Non-billable support
-- ============================================================
ALTER TABLE product
  ADD COLUMN IF NOT EXISTS is_billable_default TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE productsaleitem
  ADD COLUMN IF NOT EXISTS billing_type ENUM('BILLABLE','NON_BILLABLE') NOT NULL DEFAULT 'BILLABLE',
  ADD COLUMN IF NOT EXISTS nonbillable_reason VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS unit_price_override DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS tax_code_override VARCHAR(32) NULL;

DROP INDEX IF EXISTS idx_products_billing ON productsaleitem;
CREATE INDEX idx_products_billing ON productsaleitem (billing_type, productID);

-- ------------------------------------------------------------
-- 1b) Subject ordering (report sort)
-- ------------------------------------------------------------
ALTER TABLE subject
  ADD COLUMN IF NOT EXISTS report_order INT NOT NULL DEFAULT 0 AFTER type;

CREATE INDEX IF NOT EXISTS idx_subject_report_order ON subject (report_order);

-- ============================================================
-- 2) Transfers: idempotency + acceptance workflow
-- ============================================================
ALTER TABLE mainstock
  ADD COLUMN IF NOT EXISTS client_request_id VARCHAR(64) NULL,
  ADD COLUMN IF NOT EXISTS transfer_ref VARCHAR(64) NULL,
  ADD COLUMN IF NOT EXISTS transfer_status ENUM('PENDING','IN_TRANSIT','RECEIVED','REJECTED') NOT NULL DEFAULT 'IN_TRANSIT',
  ADD COLUMN IF NOT EXISTS received_by INT NULL,
  ADD COLUMN IF NOT EXISTS received_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS rejected_by INT NULL,
  ADD COLUMN IF NOT EXISTS rejected_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS reject_reason VARCHAR(255) NULL;

DROP INDEX IF EXISTS uq_mainstock_client_req ON mainstock;
CREATE UNIQUE INDEX uq_mainstock_client_req ON mainstock (client_request_id);

DROP INDEX IF EXISTS idx_mainstock_transfer ON mainstock;
CREATE INDEX idx_mainstock_transfer ON mainstock (type, transfer_status, stocktowarehouseID, mainstockcreate_date);

ALTER TABLE stock
  ADD COLUMN IF NOT EXISTS is_inbound_pending TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_canceled TINYINT(1) NOT NULL DEFAULT 0;

DROP INDEX IF EXISTS idx_stock_transfer_pending ON stock;
CREATE INDEX idx_stock_transfer_pending ON stock (mainstockID, is_inbound_pending, is_canceled);

-- ============================================================
-- 3) Valuation + Monthly (materialized) tables
-- ============================================================
CREATE TABLE IF NOT EXISTS valuation (
  valuationID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  productID INT NOT NULL,
  productwarehouseID INT NOT NULL,
  effective_date DATE NOT NULL,
  qty_in DECIMAL(16,4) NOT NULL DEFAULT 0,
  cost_in DECIMAL(16,4) NOT NULL DEFAULT 0,
  wac DECIMAL(16,6) NOT NULL DEFAULT 0,
  PRIMARY KEY (valuationID),
  KEY idx_val_wac (productID, productwarehouseID, effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_monthly (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  month DATE NOT NULL,
  productID INT NOT NULL,
  productwarehouseID INT NOT NULL,
  purchases DECIMAL(16,4) NOT NULL DEFAULT 0,
  sales DECIMAL(16,4) NOT NULL DEFAULT 0,
  adjustments DECIMAL(16,4) NOT NULL DEFAULT 0,
  nonbillable_issues DECIMAL(16,4) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_invmon (month, productID, productwarehouseID),
  KEY idx_invmon_fast (productID, productwarehouseID, month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 4) Views (drop dependents BEFORE parents)
-- ============================================================
DROP VIEW IF EXISTS inventory_onhand;   -- dependent first
DROP VIEW IF EXISTS inventory_ledger;   -- parent after

-- -----------------------------
-- View: inventory_ledger
-- -----------------------------
CREATE VIEW inventory_ledger AS
   -- Purchases
   SELECT
     CONCAT('P-', ppi.productpurchaseitemID) AS ledger_id,
     ppi.productID,
     ph.productwarehouseID,
     DATE(ph.productpurchasedate) AS txn_date,
     'purchase' AS source,
     ppi.productpurchasequantity AS qty_in,
     0 AS qty_out,
     ppi.productpurchaseunitprice AS unit_cost,
     ppi.productpurchaseitemID AS ref_id,
     NULL AS user_id,
     COALESCE(ph.productpurchasedescription,'') AS memo,
     NULL AS transfer_ref
   FROM productpurchaseitem ppi
   JOIN productpurchase ph ON ph.productpurchaseID = ppi.productpurchaseID

   UNION ALL
   -- Sales (billable)
   SELECT
     CONCAT('S-', psi.productsaleitemID) AS ledger_id,
     psi.productID,
     sh.productwarehouseID,
     DATE(sh.productsaledate) AS txn_date,
     'sale' AS source,
     0 AS qty_in,
     psi.productsalequantity AS qty_out,
     NULL AS unit_cost,
     psi.productsaleitemID AS ref_id,
     NULL AS user_id,
     COALESCE(sh.productsaledescription,'') AS memo,
     NULL AS transfer_ref
   FROM productsaleitem psi
   JOIN productsale sh ON sh.productsaleID = psi.productsaleID
   WHERE COALESCE(psi.billing_type,'BILLABLE') = 'BILLABLE'

   UNION ALL
   -- Non-billable issues
   SELECT
     CONCAT('N-', psi.productsaleitemID) AS ledger_id,
     psi.productID,
     sh.productwarehouseID,
     DATE(sh.productsaledate) AS txn_date,
     'issue_nonbillable' AS source,
     0 AS qty_in,
     psi.productsalequantity AS qty_out,
     NULL AS unit_cost,
     psi.productsaleitemID AS ref_id,
     NULL AS user_id,
     CONCAT('Non-billable: ', COALESCE(psi.nonbillable_reason,'')) AS memo,
     NULL AS transfer_ref
   FROM productsaleitem psi
   JOIN productsale sh ON sh.productsaleID = psi.productsaleID
   WHERE COALESCE(psi.billing_type,'BILLABLE') = 'NON_BILLABLE'

   UNION ALL
   -- Adjustments (derive warehouse by qty sign)
   SELECT
     CONCAT('A-', s.stockID) AS ledger_id,
     s.productID,
     CASE WHEN s.quantity < 0 THEN m.stockfromwarehouseID ELSE m.stocktowarehouseID END AS productwarehouseID,
     DATE(m.mainstockcreate_date) AS txn_date,
     'adjustment' AS source,
     CASE WHEN s.quantity > 0 AND s.is_canceled=0 THEN s.quantity ELSE 0 END AS qty_in,
     CASE WHEN s.quantity < 0 AND s.is_canceled=0 THEN ABS(s.quantity) ELSE 0 END AS qty_out,
     NULL AS unit_cost,
     s.stockID AS ref_id,
     m.mainstockuserID AS user_id,
     COALESCE(m.memo,'') AS memo,
     NULL AS transfer_ref
   FROM stock s
   JOIN mainstock m ON m.mainstockID = s.mainstockID
   WHERE m.type = 'adjustment' AND s.is_canceled = 0

   UNION ALL
   -- Transfers (final IN/OUT; visible in ledger, not in P&L/monthly)
   SELECT
     CONCAT('T-', s.stockID) AS ledger_id,
     s.productID,
     CASE WHEN s.quantity < 0 THEN m.stockfromwarehouseID ELSE m.stocktowarehouseID END AS productwarehouseID,
     DATE(m.mainstockcreate_date) AS txn_date,
     'transfer' AS source,
     CASE WHEN s.quantity > 0 AND s.is_inbound_pending=0 AND s.is_canceled=0 THEN s.quantity ELSE 0 END AS qty_in,
     CASE WHEN s.quantity < 0 AND s.is_canceled=0 THEN ABS(s.quantity) ELSE 0 END AS qty_out,
     NULL AS unit_cost,
     s.stockID AS ref_id,
     m.mainstockuserID AS user_id,
     COALESCE(m.memo,'') AS memo,
     m.transfer_ref
   FROM stock s
   JOIN mainstock m ON m.mainstockID = s.mainstockID
   WHERE m.type = 'transfer' AND s.is_canceled = 0

   UNION ALL
   -- Transfers: pending inbound (visible; excluded from on-hand/monthly)
   SELECT
     CONCAT('TP-', s.stockID) AS ledger_id,
     s.productID,
     m.stocktowarehouseID AS productwarehouseID,
     DATE(m.mainstockcreate_date) AS txn_date,
     'transfer_pending_in' AS source,
     CASE WHEN s.quantity > 0 AND s.is_inbound_pending=1 AND s.is_canceled=0 THEN s.quantity ELSE 0 END AS qty_in,
     0 AS qty_out,
     NULL AS unit_cost,
     s.stockID AS ref_id,
     m.mainstockuserID AS user_id,
     CONCAT('Pending inbound; ', COALESCE(m.memo,'')) AS memo,
     m.transfer_ref
   FROM stock s
   JOIN mainstock m ON m.mainstockID = s.mainstockID
   WHERE m.type = 'transfer' AND s.is_inbound_pending = 1 AND s.is_canceled = 0;

-- -----------------------------
-- View: inventory_onhand (drop-order respected)
-- -----------------------------
CREATE VIEW inventory_onhand AS
SELECT
  productID,
  productwarehouseID,
  SUM(qty_in) - SUM(qty_out) AS onhand
FROM inventory_ledger
WHERE source <> 'transfer_pending_in'  -- exclude pending inbound
GROUP BY productID, productwarehouseID;

-- ============================================================
-- 5) Monthly Aggregates VIEW (non-materialized)
-- ============================================================
DROP VIEW IF EXISTS inventory_monthly_view;

CREATE VIEW inventory_monthly_view AS
SELECT
  DATE(DATE_FORMAT(l.txn_date, '%Y-%m-01')) AS month,
  l.productID,
  l.productwarehouseID,
  SUM(CASE WHEN l.source = 'purchase'          THEN l.qty_in                     ELSE 0 END) AS purchases,
  SUM(CASE WHEN l.source = 'sale'              THEN l.qty_out                    ELSE 0 END) AS sales,
  SUM(CASE WHEN l.source = 'adjustment'        THEN (l.qty_in - l.qty_out)       ELSE 0 END) AS adjustments,
  SUM(CASE WHEN l.source = 'issue_nonbillable' THEN l.qty_out                    ELSE 0 END) AS nonbillable_issues
FROM inventory_ledger l
WHERE l.source NOT IN ('transfer', 'transfer_pending_in')
GROUP BY
  DATE(DATE_FORMAT(l.txn_date, '%Y-%m-01')),
  l.productID,
  l.productwarehouseID;

-- ============================================================
-- 6) Permissions: legacy cleanup + ensure new module & actions
-- ============================================================
-- Remove legacy 'studenttranscript%' mappings and permissions
DELETE pr
  FROM permission_relationships pr
  INNER JOIN permissions p ON p.permissionID = pr.permission_id
  WHERE p.name LIKE 'studenttranscript%';

DELETE FROM permissions
  WHERE name LIKE 'studenttranscript%';

-- Ensure base module & action permissions (upsert)
INSERT INTO permissions (description, name, active)
SELECT 'Exam Transcript Report', 'examtranscriptreport', 1
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='examtranscriptreport');

INSERT INTO permissions (description, name, active)
SELECT 'Exam Transcript Report - View', 'examtranscriptreport_view', 1
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='examtranscriptreport_view');

INSERT INTO permissions (description, name, active)
SELECT 'Exam Transcript Report - Add', 'examtranscriptreport_add', 1
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='examtranscriptreport_add');

INSERT INTO permissions (description, name, active)
SELECT 'Exam Transcript Report - Edit', 'examtranscriptreport_edit', 1
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='examtranscriptreport_edit');

INSERT INTO permissions (description, name, active)
SELECT 'Exam Transcript Report - Delete', 'examtranscriptreport_delete', 1
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='examtranscriptreport_delete');

-- Map base + actions to usertypes 1 & 2, with schoolID handling if required
DROP TEMPORARY TABLE IF EXISTS _target_perms;
CREATE TEMPORARY TABLE _target_perms (permissionID INT PRIMARY KEY)
SELECT permissionID FROM permissions WHERE name IN (
  'examtranscriptreport',
  'examtranscriptreport_view',
  'examtranscriptreport_add',
  'examtranscriptreport_edit',
  'examtranscriptreport_delete'
);

SET @needs_school := EXISTS (
  SELECT 1
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'permission_relationships'
    AND column_name = 'schoolID'
    AND is_nullable = 'NO'
    AND column_default IS NULL
);

SET @sql_map := IF(
  @needs_school,
  "
  INSERT INTO permission_relationships (permission_id, usertype_id, schoolID)
  SELECT tp.permissionID, ut.usertypeID, 1
  FROM _target_perms tp
  JOIN (SELECT 1 AS usertypeID UNION ALL SELECT 2) ut ON 1=1
  WHERE NOT EXISTS (
    SELECT 1 FROM permission_relationships r
    WHERE r.permission_id = tp.permissionID
      AND r.usertype_id = ut.usertypeID
      AND r.schoolID = 1
  )
  ",
  "
  INSERT INTO permission_relationships (permission_id, usertype_id)
  SELECT tp.permissionID, ut.usertypeID
  FROM _target_perms tp
  JOIN (SELECT 1 AS usertypeID UNION ALL SELECT 2) ut ON 1=1
  WHERE NOT EXISTS (
    SELECT 1 FROM permission_relationships r
    WHERE r.permission_id = tp.permissionID
      AND r.usertype_id = ut.usertypeID
  )
  "
);

PREPARE stmt_map FROM @sql_map;
EXECUTE stmt_map;
DEALLOCATE PREPARE stmt_map;

DROP TEMPORARY TABLE _target_perms;

-- (Optional quick verify)
SELECT 'verify_permission_examtranscriptreport' AS check_name,
       permissionID, name, description, active
FROM permissions
WHERE name LIKE 'examtranscriptreport%';

-- ============================================================
-- (Optional) Performance indexes
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_productpurchaseitem_prod ON productpurchaseitem (productID);
CREATE INDEX IF NOT EXISTS idx_productsaleitem_prod ON productsaleitem (productID, billing_type);
CREATE INDEX IF NOT EXISTS idx_stock_main ON stock (mainstockID, productID, is_inbound_pending, is_canceled);
CREATE INDEX IF NOT EXISTS idx_mainstock_dates ON mainstock (type, mainstockcreate_date);

