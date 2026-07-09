# Sample18 Managed Action Dispatch Guard Preflight

Plan item: #564 sample18 managed action dispatch guard preflight

Status: DONE

## Summary

Added a fail-closed preflight contract for future sample18 generated managed action submit paths without enabling generated mutation.

## Changes

- Added `web_lab_login` support to the custom operation dispatch preflight guard.
- Aligned sample18 dry-run route-boundary policy with the existing `project.edit` permission key.
- Added focused dispatch preflight coverage proving an authenticated sample18 editor still stops at `deferred_availability`.
- Added focused coverage proving insufficient project role fails with `policy_denied` before availability is considered.

## Boundary

This slice does not add a generated submit route, execute a managed action, enqueue outbox work, call generated DBAccess, or replace the curated sample18 page. It only fixes the guard contract that must be true before any submit path is promoted.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_custom_operation_dispatch.php`
- `php -l tests/Integration/NoCodeCustomOperationDispatchTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/NoCodeCustomOperationDispatchTest.php`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

Note: Directly running `Sample18MiniTaskBoardDemoTest` inside the existing container requires the sample18 seed/project to be loaded first; the sample pack wrapper covers that setup.

## Next

#565 should define the generated submit request payload and validation/normalization contract before adding any HTTP route or mutation dispatch.
