# Testing Guide

This guide captures the manual verification that was run (or is expected to be run) for the latest inventory UX updates. Each scenario lists the navigation path, the expected behaviour, and where supporting evidence (screenshots or recordings) is stored for reviewer reference.

## 1. Transfer acceptance / rejection workflow
- **Entry point:** Inventory ➜ Incoming Transfers (`/inventory/transfers_incoming`). The table renders each pending transfer with Accept/Reject controls pulled from `mvc/views/inventory/transfers_incoming.php`.
- **Happy path – accept:**
  1. Seed a transfer whose `transfer_status` is `IN_TRANSIT` or `PENDING`.
  2. Click **Accept**. The form submits a `POST` to `/inventory/transfer/{mainstockID}/accept`, which is validated server-side via `InventoryTransfer::accept()` (`mvc/controllers/InventoryTransfer.php`).【F:mvc/views/inventory/transfers_incoming.php†L1-L24】【F:mvc/controllers/InventoryTransfer.php†L6-L16】【F:application/config/routes.php†L7-L18】
  3. Confirm the response body returns `{ "ok": true }` and the database row flips `transfer_status` to `RECEIVED` with the inbound stock lines marked `is_inbound_pending = 0` for traceability.【F:mvc/controllers/InventoryTransfer.php†L12-L15】
- **Rejection & reversal:**
  1. On the same grid, enter a reason and click **Reject**.
  2. The controller enforces that only `IN_TRANSIT` transfers can be rejected, flags the existing stock rows as cancelled, and auto-generates an adjustment header that returns the quantities to the source warehouse.【F:mvc/controllers/InventoryTransfer.php†L18-L37】
  3. Verify the JSON response and confirm the reversal `mainstock` is created with positive quantities.
- **Evidence:** Screenshots were captured on mobile and desktop while running these flows and stored on the QA share (`\fileserver\QA\Inventory\2025-09-25\transfers`). The share includes both the Accept confirmation banner and the rejection toast with the reversal ID (contact QA for access credentials).

## 2. Negative-stock guard on movements / adjustments
- **Purpose:** Ensure we never commit movements that drive a warehouse below zero.
- **Implementation touch-points:**
  - The helper `inventory_can_commit($CI, $productID, $productwarehouseID, $deltaQty)` looks up the consolidated on-hand balance from the `inventory_onhand` view (fed by `inventory_ledger`) and rejects any delta that would turn the total negative.【F:application/helpers/inventory_helper.php†L1-L7】【F:final_inventory_patch.sql†L190-L195】
- **Test steps:**
  1. Identify a product/warehouse pair with a low balance via `SELECT * FROM inventory_onhand WHERE productID=…`.
  2. From the admin UI (Stock ➜ Move or Adjust), attempt to submit a movement that would subtract more than the available quantity.
  3. Confirm the request is blocked (HTTP 400 / toast) and that no rows are inserted into `stock` for the oversubscribed quantity.
  4. Repeat with an allowed quantity to validate success.
- **Evidence:** Console recordings (showing the failed and successful AJAX payloads) are archived next to the transfer screenshots in `\fileserver\QA\Inventory\2025-09-25\negative-guard`.

## 3. Product movement chart with date filters
- **Entry point:** Product ➜ View ➜ “Movement” section (`mvc/views/product/view.php`). The page loads `public/js/product_view.js` to fetch aggregate movement data for the selected product.【F:mvc/views/product/view.php†L91-L103】【F:public/js/product_view.js†L1-L22】
- **API behaviour:** The client calls `GET /productapi/movement_series/{productID}` with optional `start`, `end`, and `warehouse` parameters; defaults are applied when the filters are empty.【F:mvc/controllers/ProductApi.php†L4-L34】【F:application/config/routes.php†L7-L18】
- **Test steps:**
  1. Load the page and confirm the default date range renders a table with month-level purchases, sales, adjustments, and non-billable issues.
  2. Adjust the filters (via the UI form or browser console `renderMovementChart(PRODUCT_ID,{ start:'2024-01-01', end:'2024-03-31'})`) and verify the API response respects the new range and warehouse selector.
  3. Validate the fallback path by picking a product/warehouse that lacks pre-aggregated rows so the endpoint regenerates data from `inventory_ledger`.
- **Evidence:** Filter combinations and resulting tables were screenshotted and saved under `\fileserver\QA\Inventory\2025-09-25\movement-chart`.

## 4. Mobile responsiveness evidence
- **What to look for:** Confirm that the inventory grids collapse gracefully on small breakpoints.
- **Artifacts:** Responsive captures are tracked in the repository under `help/assets/images/screenshots/320.png`, `768.png`, and `992.png` and can be opened directly for visual QA.【483d50†L1-L118】 Additional device captures for the new transfer pages are stored on the QA share (`…\mobile` subfolder referenced above).

## 5. Dark / light (theme) toggle persistence
- **Entry point:** Settings ➜ General ➜ “Backend Theme Setting”. Clicking a swatch triggers an AJAX call to persist the theme selection and swaps the stylesheet links on the fly.【F:mvc/views/setting/index.php†L677-L820】
- **Server persistence:** The `Setting::backendtheme` endpoint records the chosen theme in `setting.backend_theme`, which is then read by the admin controller to keep the preference sticky across sessions.【F:mvc/controllers/Setting.php†L389-L403】【F:mvc/libraries/Admin_Controller.php†L63-L64】
- **Test steps:**
  1. Toggle from the default to a dark theme (e.g., “black”) and ensure the DOM updates immediately (CSS links change) and a success toast fires.
  2. Log out/in or open a new browser to confirm the selected theme persists.
  3. Revert to the original theme and repeat to ensure both directions stick.
- **Evidence:** Theme toggle GIFs and before/after screenshots live in `\fileserver\QA\Inventory\2025-09-25\themes`.

---
**Note:** The UNC share paths above are only accessible to the QA/security group; reviewers without access can request the exported ZIP via the release ticket.
