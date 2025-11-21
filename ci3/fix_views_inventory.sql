USE shulelabs_staging;

-- If someone previously created these as TABLEs, drop them first.
DROP TABLE IF EXISTS inventory_ledger;
DROP VIEW  IF EXISTS inventory_ledger;

DROP TABLE IF EXISTS inventory_onhand;
DROP VIEW  IF EXISTS inventory_onhand;

-- Recreate inventory_ledger (VIEW)
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

-- Recreate inventory_onhand (VIEW)
CREATE VIEW inventory_onhand AS
SELECT
  productID,
  productwarehouseID,
  SUM(qty_in) - SUM(qty_out) AS onhand
FROM inventory_ledger
GROUP BY productID, productwarehouseID;
