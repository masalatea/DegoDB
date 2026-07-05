# 2026-0703 Runtime Action Intent Field Summary

Status: `FIRST_SLICE_DONE`

## Summary

Adds a compact field summary row to generated no-code runtime `Action Intent Draft` panels.

The draft panel already shows state, readiness, policy, metadata, payload counts, copy affordance, and detailed JSON. This slice adds a small field summary so a tryout user can see the key/input/filter field names without opening or scanning the JSON.

## Changes

- Adds `data-intent-draft-fields` to each generated draft panel.
- Summarizes action field metadata as `Fields: key=... | input=... | filter=...`.
- Keeps the non-mutating preview boundary unchanged.

## Verification

- `php -l mtool/app/no_code_runtime.php`: passed inside the existing Docker web-admin container.
- Focused `NoCodeRuntimeTest`: `8 tests, 140 assertions`
- Sample28 artifact PHPUnit on the clean buildless sample28 stack: `1 test, 8 assertions`
- Sample28 runtime UI smoke: passed and confirmed three field summary rows plus `Fields: key=id | input=body, priority, status, title | filter=(none)`
- Full Integration PHPUnit on the clean buildless sample01 stack: `327 tests, 10837 assertions, skipped 1`

Note: `make sample28-pack-runtime-test` was attempted first, but Docker build metadata lookup for `ubuntu:24.04` timed out. The verification above reuses existing images with `--no-build`, resets the sample stacks, applies the same sample seed set, and runs the focused/full PHPUnit targets plus the browser smoke.

Push was not performed for this slice.
