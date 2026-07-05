# 2026-0703 Runtime Action Intent State Badge

Status: `FIRST_SLICE_DONE`

## Summary

Adds a visible state badge to generated no-code runtime `Action Intent Draft` panels.

The draft state was already available through the summary text and `data-intent-draft-state`. This slice makes the state a stable visible UI marker that updates with the draft.

## Changes

- Adds `data-intent-draft-state-badge` to each generated draft panel.
- Updates badge text and `data-state` for `ready`, `blocked`, and `empty` draft states.
- Keeps the non-mutating preview boundary unchanged.

## Verification

- `php -l mtool/app/no_code_runtime.php`: passed inside the existing Docker web-admin container.
- Focused `NoCodeRuntimeTest`: `8 tests, 136 assertions`
- Sample28 artifact PHPUnit: `1 test, 8 assertions`
- Sample28 runtime UI smoke: passed and confirmed three `Blocked` draft state badges.
- Full Integration PHPUnit on the clean buildless sample01 stack: `327 tests, 10833 assertions, skipped 1`
- `git diff --check`: passed

Note: normal `make test` was not rerun directly in this pass because Docker build metadata lookup for `ubuntu:24.04` had timed out earlier. The verification above reuses existing images with `--no-build`, resets the sample01 stack before the final full Integration pass, applies the same sample seed set, and runs the same full Integration PHPUnit target.

Push was not performed for this slice.
