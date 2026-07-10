# Sample18 Generated Submit Route Blocked Wrapper

Plan item: #566 sample18 generated submit route blocked wrapper

Status: DONE

## Summary

Added a narrow authenticated sample18 generated submit wrapper that validates request payloads and still returns blocked before any mutation dispatch.

## Changes

- Added `/samples/sample18-task-board/no-code/generated-submit`.
- Added a JSON response builder for non-POST, unknown operation, validation failure, and valid-but-blocked generated submit requests.
- Registered the route in the router, HTTP dispatcher, and auth-required list.
- Added focused PHPUnit coverage for route matching/auth, method failure, valid blocked response, validation error response, and unknown operation response.

## Boundary

This slice does not enable generated action buttons, call DBAccess, enqueue outbox work, or replace the curated sample18 page. Valid generated submit requests return `generated_submit_disabled` with the normalized payload for inspection only.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l mtool/app/router.php`
- `php -l mtool/app/http.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

## Next

#567 should prove the generated/runtime UI can carry or reference the blocked submit route without enabling buttons or mutation.
