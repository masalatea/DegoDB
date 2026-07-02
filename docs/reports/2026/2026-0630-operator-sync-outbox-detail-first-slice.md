# Operator Sync Outbox Detail First Slice

Date: 2026-06-30
Status: `FIRST_SLICE_DONE`

## Summary

Added a read-only project-scoped sync outbox item detail page for operator/admin inspection.

The page is linked from the existing Source Outputs `Sync Outbox Inspection` failed-item list and shows the existing outbox fields plus the decoded intent payload. It intentionally does not add retry, requeue, status mutation, remote transport, conflict resolution, or a broad dashboard.

## Implemented

- Added `app_project_sync_outbox_detail_path()`.
- Added route `/projects/{project}/sync-outbox/{dedupe_key}` as `project_sync_outbox_detail`.
- Added `project_sync_outbox_detail_page.php`.
- Wired the page into `http.php`.
- Added route auth contract entry using the existing `source_output.download` audited boundary.
- Linked failed sync outbox items from Source Outputs admin to the detail page.
- Updated route/auth contract tests.

## Boundary

In scope:

- read-only operator/admin outbox item detail
- existing outbox fields
- decoded intent payload
- project-scoped route and permission audit
- focused route/auth tests

Out of scope:

- retry/requeue action
- status mutation
- remote transport
- conflict resolution
- broad dashboard

## Verification

- `php -l mtool/app/project_sync_outbox_detail_page.php`
- `php -l mtool/app/project_source_outputs_page.php`
- `php -l mtool/app/router.php`
- `php -l mtool/app/http.php`
- `php -l mtool/app/project_route_authorization.php`
- `php -l tests/Integration/SecurityFoundationContractTest.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `php -l tests/Integration/ProjectRouteAuthorizationContractTest.php`
- `git diff --check`
- `make test`

## Result

`make test` passed with 306 tests, 10102 assertions, and 1 skipped test.

## Next

Run the post-operator sync outbox detail no-code product-goal replan before choosing the next implementation slice.
