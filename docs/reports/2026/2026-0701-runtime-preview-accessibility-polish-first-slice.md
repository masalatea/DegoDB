# Runtime preview accessibility polish first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-01

## Summary

Added small accessibility affordances to generated `runtime-preview.html` while keeping runtime behavior unchanged.

## Changes

- Added `aria-labelledby="no-code-preview-title"` to the generated preview `<main>`.
- Added a stable `id="no-code-preview-title"` to the preview title.
- Added `role="region"` and stable `aria-labelledby` values to generated screen sections.
- Added stable IDs to generated screen headings.
- Added captions to generated list tables.
- Added `aria-label` values to generated action navigation.
- Updated runtime PHPUnit, sample28 checker, runtime UI smoke, sample28 README, and current plan records.

## Boundary

In scope:

- generated runtime preview semantic labels;
- generated list table captions;
- generated action nav labels;
- focused DOM and smoke assertions.

Out of scope:

- full accessibility audit;
- keyboard interaction redesign;
- visual builder;
- React bridge behavior changes;
- schema-form runtime behavior changes;
- action execution behavior changes.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js --help`
- `make sample28-pack-runtime-test` (`1 test, 7 assertions`)
- `make sample28-no-code-runtime-ui-smoke`
- `make test` (`309 tests, 10284 assertions, skipped 1`)
