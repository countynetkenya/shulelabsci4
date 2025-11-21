-- final_inventory_patch.sql (schema-correct for shulelabs_staging)
USE shulelabs_staging;
SET SESSION sql_mode='STRICT_ALL_TABLES';

-- 1) Billable / non-billable support on existing tables
ALTER TABLE product
  ADD COLUMN IF NOT EXISTS is_billable_default TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE productsaleitem
  ADD COLUMN IF NOT EXISTS billing_type ENUM('BILLABLE','NON_BILLABLE') NOT NULL DEFAULT 'BILLABLE',
  ADD COLUMN IF NOT EXISTS nonbillable_reason VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS unit_price_override DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS tax_code_override VARCHAR(32) NULL;

DROP INDEX IF EXISTS idx_products_billing ON productsaleitem;
CREATE INDEX idx_products_billing ON productsaleitem (billing_type, productID);

-- 2) Transfers: idempotency + acceptance workflow
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

-- 3) Valuation and Monthly Aggregates
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

-- 4) Unified Inventory Ledger VIEW (derive warehouse for stock lines via mainstock)
DROP VIEW IF EXISTS inventory_ledger;

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

   -- Transfers (final IN/OUT; derive warehouse by sign)
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

   -- Transfers: pending inbound (visible; excluded from on-hand)
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

-- 5) On-hand view
DROP VIEW IF EXISTS inventory_onhand;
CREATE VIEW inventory_onhand AS
SELECT productID, productwarehouseID, SUM(qty_in) - SUM(qty_out) AS onhand
FROM inventory_ledger
GROUP BY productID, productwarehouseID;