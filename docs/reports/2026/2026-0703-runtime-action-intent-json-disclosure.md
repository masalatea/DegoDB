# 2026-0703 Runtime Action Intent JSON Disclosure

Status: `FIRST_SLICE_DONE`

## Summary

Wraps the detailed local draft JSON in a `Draft JSON` disclosure in generated no-code runtime previews.

The summary rows are now sufficient for normal scanning. The detailed JSON remains available for inspection and copy, but no longer dominates the `Action Intent Draft` panel by default.

## Changes

- Adds `data-intent-draft-json-details` around the draft JSON output.
- Keeps `data-intent-draft-output` unchanged so copy and smoke coverage still read the same draft text.
- Keeps the non-mutating preview boundary unchanged.

## Verification

- `php -l mtool/app/no_code_runtime.php`: passed inside the existing Docker web-admin container.
- Focused `NoCodeRuntimeTest`: `8 tests, 142 assertions`
- Sample28 artifact PHPUnit on the existing buildless sample28 stack: `1 test, 8 assertions`
- Sample28 runtime UI smoke: passed and confirmed three `Draft JSON` disclosure summaries.
- Full Integration PHPUnit on the clean buildless sample01 stack: `327 tests, 10839 assertions, skipped 1`

Note: the first full Integration attempt failed because the sample01 lab DB had a stale `external_article` table from a previous lab source test path. The stack was reset, the lab DB was confirmed clean with only `lab_experiments`, the sample seed set was reapplied, and the full Integration PHPUnit rerun passed.

Push was not performed for this slice.
