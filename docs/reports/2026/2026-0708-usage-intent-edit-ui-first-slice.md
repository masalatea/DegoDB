# Usage Intent Edit UI First Slice

Date: 2026-07-08

## Status

Done as #427.

This slice adds the first admin edit UI for persistent no-code interface usage intent.

## What changed

- Added `/projects/{project_key}/shared-contracts`.
- Added a minimal shared contract route bootstrap for admin/config users.
- Added a shared contract metadata page that lists `project_shared_contracts`.
- The page edits only contract-level `usage_intent`.
- Existing `no_code_role`, `sync_role`, and `app_persistence_role` remain visible and are preserved as derived fallback inputs.
- Project detail now includes a Shared Contract module entry.
- Source Outputs no-code interface profiles link to Shared Contracts for editing explicit usage intent.
- Route matching, authentication requirement, and project authorization contract are updated for the new route.

## Boundary

This is not a broad visual builder.

It deliberately changes only the explicit usage intent field. View variant selection remains a separate layer so the same interface can later choose different presentation variants without changing data-flow intent.

## Verification

Completed verification:

- `php -l mtool/app/project_shared_contract_route_common.php`
- `php -l mtool/app/project_shared_contracts_page.php`
- `php -l mtool/app/router.php`
- `php -l mtool/app/http.php`
- `php -l mtool/app/project_detail_page.php`
- `php -l mtool/app/project_source_outputs_page.php`
- `php -l mtool/app/project_route_authorization.php`
- `git diff --check`
- `make test`

Result:

- `Tests: 341, Assertions: 11190, Skipped: 1.`
