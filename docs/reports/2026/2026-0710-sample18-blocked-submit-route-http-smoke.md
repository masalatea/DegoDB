# Sample18 Blocked Submit Route HTTP Smoke

Plan item: #569 sample18 blocked submit route HTTP smoke

Status: DONE

## Summary

Extended the sample18 HTTP smoke to prove the generated submit endpoint returns its blocked and failure JSON through the authenticated HTTP stack before runtime binding or mutation dispatch.

## Changes

- Added JSON response decoding to `mtool/scripts/check_sample18_task_board_http_smoke.php`.
- Extended `make sample18-http-runtime-smoke` coverage for `/samples/sample18-task-board/no-code/generated-submit`.
- Verified authenticated GET returns 405 `method_not_allowed`.
- Verified authenticated valid POST returns 409 `generated_submit_disabled`.
- Verified authenticated invalid POST returns 422 `validation_error`.
- Verified authenticated unknown operation POST returns 404 `unknown_operation`.

## Boundary

This slice does not enable generated buttons, does not wire runtime clicks to the submit route, does not call DBAccess, and does not enqueue outbox work. The route remains intentionally blocked and mutation-disabled.

## Verification

- `php -l mtool/scripts/check_sample18_task_board_http_smoke.php`
- `make sample18-http-runtime-smoke`
- `make test`
- `git diff --check`

## Next

#570 should define the runtime binding and enablement gates before any generated button click path or mutation dispatch is promoted.
