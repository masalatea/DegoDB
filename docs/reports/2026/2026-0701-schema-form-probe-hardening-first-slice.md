# Schema-form probe hardening first slice

## Status

`FIRST_SLICE_DONE`

## Summary

The schema-form comparison artifact now carries Mtool-aware field and action metadata.

This slice keeps the JSON Forms / rjsf path comparison-only. It does not add a schema-form runtime UI or replace the custom React bridge.

## Implementation

- Added JSON Schema property metadata:
  - `description`
  - optional `format`
  - `x-mtool-field-key`
  - `x-mtool-field-type`
  - `x-mtool-required`
  - `x-mtool-readonly`
  - `x-mtool-action-field-role`
  - `x-mtool-client-write`
- Added `contract_invariants.mtool_extension_keys`.
- Added UI Schema options:
  - `required`
  - `mtoolFieldKey`
  - `mtoolFieldType`
  - `mtoolActionFieldRole`
  - `mtoolClientWrite`
  - `mtoolValidationHint`
- Added field mapping metadata for schema type/format, action role, client write, and schema keywords.
- Extended sample28 checker and shared foundation coverage for the new metadata.

## Boundary

In scope:

- Mtool extension metadata in schema-form probe artifacts;
- action field role / client-write hints;
- UI Schema options;
- focused checker coverage.

Out of scope:

- JSON Forms / rjsf runtime UI;
- enum/options from new metadata tables;
- visual builder;
- server execution;
- transport.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `make sample28-pack-runtime-test`
- `make test` (309 tests, 10269 assertions, skipped 1)

## Next

Next replan should choose between generated runtime visual polish follow-up, a schema-form runtime smoke, retry audit trail, or another product-facing no-code gap.
