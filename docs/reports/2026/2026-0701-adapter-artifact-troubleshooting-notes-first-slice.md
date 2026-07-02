# Adapter artifact troubleshooting notes first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-01

## Summary

Added generated troubleshooting notes to React bridge and JSON Forms/rjsf probe consumer notes and structured contract metadata.

## Changes

- Added `adapter_troubleshooting_notes` to React bridge `bridge-contract.json`.
- Added `Adapter Troubleshooting Notes` to React bridge `CONSUMER-NOTES.md`.
- Added `adapter_troubleshooting_notes` to schema-form probe `schema-form-contract.json`.
- Added `Adapter Troubleshooting Notes` to schema-form probe `CONSUMER-NOTES.md`.
- Covered troubleshooting text in sample28 checker and shared foundation tests.
- Updated sample28 README and current plan index.

## Boundary

In scope:

- React bridge build/display/action-intent troubleshooting hints;
- schema-form smoke/field-mapping/action-role troubleshooting hints;
- generated consumer handoff notes;
- focused assertions.

Out of scope:

- new artifact kind;
- new smoke command implementation;
- React bridge replacement;
- JSON Forms/rjsf product runtime adoption;
- runtime behavior changes.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `make sample28-pack-runtime-test` (`1 test, 7 assertions`)
- `make test` (`309 tests, 10304 assertions, skipped 1`)
