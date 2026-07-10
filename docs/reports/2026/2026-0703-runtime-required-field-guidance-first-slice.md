# 2026-0703 Runtime Required Field Guidance First Slice

Status: `FIRST_SLICE_DONE`

## Summary

Adds inline required-field guidance to generated no-code runtime forms.

The draft summary already reports missing required input fields after the local draft is built. This slice makes required fields visible directly beside the generated form controls, so the user can understand the input contract before inspecting the draft panel.

## Changes

- Adds a visible `Required` badge for generated required form fields.
- Adds a short required-field hint connected through `aria-describedby`.
- Keeps browser-side draft building, copy behavior, and non-mutating preview behavior unchanged.

## Verification

- `php -l mtool/app/no_code_runtime.php`: passed inside the existing Docker web-admin container.
- Focused `NoCodeRuntimeTest`: `8 tests, 145 assertions`
- Sample28 artifact PHPUnit on the existing buildless sample28 stack: `1 test, 8 assertions`
- Sample28 runtime UI smoke: passed and confirmed required badges / hints for rendered required controls.
- Full Integration PHPUnit on the clean buildless sample01 stack: `327 tests, 10842 assertions, skipped 1`

Note: the first full Integration attempt failed because the sample01 lab DB had a stale `external_article` table from a previous lab source test path. The stack was reset, the sample seed set was reapplied, and the full Integration PHPUnit rerun passed.

Push was not performed for this slice.
