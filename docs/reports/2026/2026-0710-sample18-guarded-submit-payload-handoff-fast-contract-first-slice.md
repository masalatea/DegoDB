# Sample18 Guarded Submit Payload Handoff Fast Contract First Slice

Status: `FIRST_SLICE_DONE`

Plan: #689 sample18 guarded submit payload handoff fast contract first slice

## Summary

#689 adds a fast non-browser contract for sample18 generated guarded-submit payload handoff.

The slice proves that generated action intent fields can be assembled into route-compatible key/input payloads and normalized by the executable generated-submit route contract. It also checks that generated runtime HTML includes the guarded submit JS path for flat POST payload assembly.

## Changes

- Required `no_code_runtime.php` in `Sample18MiniTaskBoardDemoTest` so the test can use runtime action intent helpers directly.
- Added assertions that create/update/complete action intents split generated fields into key/input payloads according to the route contract.
- Verified the assembled intent payload normalizes through `app_lab_sample18_task_board_normalize_generated_submit_request()`.
- Verified missing create title fails closed as `input.missing:title`.
- Verified generated runtime HTML includes guarded submit source markers for `operation_key`, configured CSRF token field, flat input fields, POST method, same-origin credentials, and JSON accept headers.
- Updated `docs/no-code-ui-testing.md` with the accepted non-browser payload handoff contract.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (28 tests, 1636 assertions)`

## Next

Promote #690: post guarded submit payload handoff lane closure.

The closure should decide whether the next step is browser smoke, selected-row/key handoff hardening, or generated availability expansion.
