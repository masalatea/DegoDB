# Sample18 Generated Submit CSRF Guard Preflight

Plan item: #572 sample18 generated submit CSRF guard preflight

Status: DONE

## Summary

Added fail-closed CSRF handling and HTTP smoke coverage for the sample18 generated submit route before runtime click binding or mutation dispatch.

## Changes

- Added route-level CSRF guard evaluation for generated submit POST requests.
- Returned JSON 403 `missing_csrf` for missing `_csrf_token`.
- Returned JSON 403 `invalid_csrf` for invalid `_csrf_token`.
- Kept valid generated submit POST requests blocked with `generated_submit_disabled`.
- Extended focused PHPUnit coverage for missing and invalid CSRF guard outcomes.
- Extended `sample18-http-runtime-smoke` to cover valid blocked, missing CSRF, invalid CSRF, validation, unknown operation, and method guard outcomes.

## Boundary

This slice does not bind runtime clicks to the generated submit route, does not enable generated buttons, does not call DBAccess, and does not enqueue outbox work. Mutation remains disabled.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `php -l mtool/scripts/check_sample18_task_board_http_smoke.php`
- `make sample18-pack-runtime-test`
- `make sample18-http-runtime-smoke`
- `make test`
- `git diff --check`

## Next

#573 should close the generated submit guard lane and decide whether disabled click intent, guarded CSRF handoff, or mutation dispatcher inventory should be promoted next.
