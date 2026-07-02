# Schema-form consumer notes first slice

## Status

`FIRST_SLICE_DONE`

## Summary

Generated schema-form probe artifacts now include human-readable consumer notes.

This keeps the JSON Forms / rjsf path comparison-only while documenting ownership boundaries, stable markers, runtime smoke scope, and editing guidance inside the generated artifact bundle.

## Implementation

- Added generated `CONSUMER-NOTES.md` to `NO-CODE-JSON-FORMS-PROBE`.
- Added structured `consumer_notes` to `schema-form-contract.json`.
- Added `CONSUMER-NOTES.md` to `contract_invariants.required_files`.
- Updated JSON Forms probe README text to mention the notes file.
- Extended sample28 checker and shared foundation coverage.
- Updated sample28 README.

## Boundary

In scope:

- schema-form probe consumer notes;
- comparison-only boundary wording;
- Mtool / schema-form consumer ownership split;
- stable marker guidance;
- focused invariant and publish coverage.

Out of scope:

- product adoption of JSON Forms or rjsf;
- replacing the custom React bridge;
- browser UI smoke changes;
- visual builder;
- server execution;
- transport or sync behavior.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `make sample28-pack-runtime-test`
- `make sample28-no-code-schema-form-runtime-smoke`
- `make test` (309 tests, 10276 assertions, skipped 1)
