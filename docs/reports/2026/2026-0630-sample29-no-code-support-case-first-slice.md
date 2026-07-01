# Sample29 No-Code Support Case First Slice

Status: `FIRST_SLICE_DONE`

Date: 2026-06-30

## Scope

Finished the second data-first no-code domain sample first slice as `sample29-no-code-support-case-demo`.

The goal was to put the polished generated no-code runtime path under slightly richer product pressure than sample28, without adding new metadata tables, a relation engine, or a visual builder.

## Domain Choice

Selected a support-case domain around `support_case` and `update_support_case`.

This keeps the sample small but adds read-model-like context fields:

- `case_number`
- `customer_name`
- `customer_tier`

The editable workflow fields are:

- `subject`
- `status`
- `severity`
- `next_action`

This shape is intentionally richer than sample28 because the generated list/detail/form screens must show readonly context next to editable operation input, while still using the existing shared contract, managed operation, and `NO-CODE-RUNTIME` metadata boundaries.

## Implementation

- Added `sample/tutorials/sample29-no-code-support-case-demo`.
- Registered sample29 in the sample pack catalog and tutorial indexes.
- Seeded project, table, shared contract, managed operation, and `NO-CODE-RUNTIME` metadata for `SAMPLE29`.
- Added `Sample29NoCodeSupportCaseDemoTest` and the sample29 pack checker.
- Added a sample29 profile to `check_no_code_runtime_preview_ui_smoke.js`.
- Added `sample29-pack-runtime-test`, `sample29-runtime-output-test`, and `sample29-no-code-runtime-ui-smoke` Make targets.

## Verification

- `php -l mtool/scripts/lib/sample29_no_code_support_case_demo_check.php`
- `php -l tests/Integration/Sample29NoCodeSupportCaseDemoTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make -n sample29-pack-runtime-test`
- `make sample29-pack-runtime-test`
- `make sample29-no-code-runtime-ui-smoke`
- `make test`

## Result

The first slice is complete. `sample29-no-code-support-case-demo` proves that the current generated runtime path can handle a second data-first domain with readonly context fields and editable operation input through generated list/detail/form smoke and authorized update intent smoke.

## Next

Replan the next product-facing no-code slice from the sample29 result. Current candidates are a small sample29 follow-up if a concrete domain/runtime gap appears, an App-local sync demonstration, an operator/admin no-code workflow, or targeted runtime polish driven by sample29 evidence.
