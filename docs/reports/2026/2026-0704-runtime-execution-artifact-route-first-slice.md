# Runtime Execution Artifact Route First Slice

Date: 2026-07-04
Status: FIRST_SLICE_DONE

## Summary

Added the first route wiring slice for server-backed no-code runtime execution. The new route is artifact-key scoped:

`/runs/no-code/{project}/{artifact}/execute.json`

The route is registered and handled by the app, but generated runtime preview buttons are still not wired to submit to it. Preview HTML remains public; the execution route requires authentication.

## Accepted Capability

- `app_no_code_public_runtime_execution_path()` builds the artifact-key execution endpoint URL.
- `router.php` resolves `/runs/no-code/{project}/{artifact}/execute.json` to `no_code_public_runtime_execution`.
- `http.php` dispatches the route to `app_render_no_code_public_runtime_execution_page()`.
- `app_route_requires_auth()` requires auth for `no_code_public_runtime_execution`.
- The handler finds an approved no-code publish candidate for the artifact key before execution.
- The handler loads `screen-definition.json` from the approved artifact bundle.
- The handler reuses the existing POST/CSRF/project/artifact/action/input request contract.
- The handler reuses the existing runtime dispatch and endpoint response helpers.
- The dispatcher bridges runtime action intent into managed-operation sync intent and enqueues it through the existing outbox path.

## Deferred

- Current and alias execution endpoint variants.
- Generated runtime preview form submission wiring.
- Browser-side success/error rendering from server responses.
- Data refresh after a successful mutation.
- Dedicated runtime execution audit trail beyond the managed-operation outbox path.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`: passed.
- `php -l mtool/app/router.php`: passed.
- `php -l mtool/app/http.php`: passed.
- Focused `OpenApiSourceOutputContractTest`: `22 tests, 1829 assertions`.
- Focused `NoCodeRuntimeTest`: `12 tests, 195 assertions`.
- `make sample28-no-code-runtime-ui-smoke`: passed.
- `git diff --check`: passed.
- Full `make test`: `331 tests, 10900 assertions, skipped 1`.

Push was not performed.
