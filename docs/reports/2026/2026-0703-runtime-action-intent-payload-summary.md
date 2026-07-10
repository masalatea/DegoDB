# 2026-0703 Runtime Action Intent Payload Summary

Status: `FIRST_SLICE_DONE`

## Summary

Adds a compact payload count row to generated no-code runtime `Action Intent Draft` panels.

The draft panel already shows readiness, policy, metadata, copy affordance, and detailed JSON. This slice adds a small payload summary so a tryout user can see the key/input/filter shape without opening or scanning the JSON.

## Changes

- Adds `data-intent-draft-payload` to each generated draft panel.
- Updates the row from the current local draft as form edits refresh the preview.
- Shows `Payload: N key fields | N input fields | N filter fields`.
- Keeps the non-mutating preview boundary unchanged.

## Verification

- `php -l mtool/app/no_code_runtime.php`: passed inside the existing Docker web-admin container.
- Focused `NoCodeRuntimeTest`: `8 tests, 133 assertions`
- Sample28 artifact PHPUnit: `1 test, 8 assertions`
- Sample28 runtime UI smoke: passed and confirmed `draftPayloadAfterEdit` is `Payload: 0 key fields | 4 input fields | 0 filter fields`
- Full Integration PHPUnit on the clean buildless sample01 stack: `327 tests, 10830 assertions, skipped 1`
- `git diff --check`: passed

Note: normal `make test` was not rerun directly in this pass because Docker build metadata lookup for `ubuntu:24.04` had timed out earlier. The verification above reuses existing images with `--no-build`, resets the sample01 stack before the final full Integration pass, applies the same sample seed set, and runs the same full Integration PHPUnit target.

Push was not performed for this slice.
