# Sample18 Generated Submit Idempotency Route Integration First Slice

Date: 2026-07-10
Plan: #596
Status: FIRST_SLICE_DONE

## Summary

#596 wires the valid blocked sample18 generated submit route path to idempotency create-or-reuse after audit append.

The route remains blocked and non-mutating. DBAccess execution is still disabled.

## Implemented

- Added route-local idempotency helper functions.
- Valid blocked generated submit responses now include `idempotency` metadata.
- First valid blocked submit returns `idempotency.status=recorded`.
- Duplicate valid blocked submit returns `idempotency.status=duplicate` and increments `duplicate_count`.
- No-app helper calls return `idempotency.status=skipped` with `reason=no_app`.
- Repository failures return `idempotency.status=failed`.
- Method, CSRF, validation, and unknown-operation failures still do not include audit append or idempotency persistence metadata.
- HTTP smoke now checks the recorded idempotency response.

## Boundaries Kept

- HTTP status remains 409 for valid generated submit.
- `failure_code` remains `generated_submit_disabled`.
- Top-level `mutation_enabled` remains `false`.
- Dispatcher `executed` remains `false`.
- Duplicate audit interaction remains unchanged; duplicates still append route audit events before idempotency reuse.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `php -l mtool/scripts/check_sample18_task_board_http_smoke.php`
- `make sample18-pack-runtime-test`: `OK (6 tests, 428 assertions)`
- `make sample18-http-runtime-smoke`: `OK`
- `make sample18-no-code-public-runtime-disabled-action-smoke`: `OK`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 388, Assertions: 12236, Skipped: 1.`
- `git diff --check`

## Next

#597 should close the route integration lane and decide whether the next slice should cover duplicate audit interaction, idempotency/audit failure matrix, or mutation enablement gate coverage.
