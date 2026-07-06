# Sample31 No-Code Inventory Request First Slice / sample31 no-code inventory request first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-05

## Summary

#182 adds `sample31-no-code-inventory-request-demo` as a third data-first no-code domain sample.

The purpose is to prove that the generated no-code runtime flow repeats beyond sample28 tickets and sample29 support cases. Sample31 uses an inventory request table with warehouse, item, quantity, status, and fulfillment note fields.

## Implemented

- Added `sample/tutorials/sample31-no-code-inventory-request-demo`.
- Added sample31 project, table, shared contract, managed operation, and Source Output seed files.
- Added `Sample31NoCodeInventoryRequestDemoTest`.
- Added sample31 helper coverage for runtime artifact generation and published runtime files.
- Added `sample31-pack-runtime-test`.
- Added `sample31-no-code-runtime-ui-smoke`.
- Added sample31 profile to `check_no_code_runtime_preview_ui_smoke.js`.
- Registered sample31 in the sample pack catalog and sample documentation.

## Boundary

- This first slice proves generated runtime artifact and static runtime browser behavior.
- Public current/alias runtime submit smoke is not included in this slice.
- Outbox processing smoke for sample31 is not included in this slice.
- The sample is intentionally lightweight and avoids regulated-domain complexity.

## Verification

- `php -l mtool/scripts/lib/sample31_no_code_inventory_request_demo_check.php`
- `php -l tests/Integration/Sample31NoCodeInventoryRequestDemoTest.php`
- `php -l mtool/app/sample_pack_catalog.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-pack-runtime-test`
- `make sample31-no-code-runtime-ui-smoke`
- `make test` (`Tests: 335, Assertions: 11044, Skipped: 1`)

Push was not performed.
