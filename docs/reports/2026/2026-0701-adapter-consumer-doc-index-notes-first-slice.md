# Adapter consumer doc index notes first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-01

## Summary

Added generated documentation index notes to React bridge and JSON Forms/rjsf probe consumer notes and structured contract metadata.

## Changes

- Added `adapter_doc_index` to React bridge `bridge-contract.json`.
- Added `Adapter Documentation Index` to React bridge `CONSUMER-NOTES.md`.
- Added `adapter_doc_index` to schema-form probe `schema-form-contract.json`.
- Added `Adapter Documentation Index` to schema-form probe `CONSUMER-NOTES.md`.
- Covered doc index text in sample28 checker and shared foundation tests.
- Updated sample28 README and current plan index.

## Boundary

In scope:

- generated documentation index notes;
- section reading order for parity, checklist, troubleshooting, stable markers, and contract sections;
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
- `make sample28-pack-runtime-test`
- `make test` (`309 tests, 10310 assertions, skipped 1`)
