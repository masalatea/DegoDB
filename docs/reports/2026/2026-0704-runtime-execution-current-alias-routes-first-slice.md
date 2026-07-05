# Runtime Execution Current/Alias Routes First Slice

Date: 2026-07-04
Status: FIRST_SLICE_DONE

## Summary

Added authenticated current and custom-alias no-code runtime execution JSON routes:

- `/runs/no-code/{project}/current/execute.json`
- `/runs/no-code/{project}/alias/{alias}/execute.json`

Both routes preserve the public preview split: preview HTML remains public, while mutation endpoints require authentication. The new routes resolve the same approved publish candidates as the existing current and alias preview URLs, then reuse the existing runtime execution response helper and managed-operation dispatch path.

Generated runtime preview form submission is still deferred. This slice only makes the server-backed execution endpoint addressable through the same stable URL shapes as public preview delivery.

## Implementation Notes

- Added current and alias execution path helpers.
- Added route matching before the generic artifact-key execution route where needed.
- Added HTTP dispatch cases for the new route names.
- Added render functions that fail closed on invalid project or alias bindings.
- Added static contract coverage for route names, auth requirements, and helper presence.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`: passed.
- `php -l mtool/app/router.php`: passed.
- `php -l mtool/app/http.php`: passed.
- Focused `OpenApiSourceOutputContractTest`: `22 tests, 1843 assertions`.
- `make sample28-no-code-runtime-ui-smoke`: passed.
- `git diff --check`: passed.
- `make test`: `331 tests, 10914 assertions, skipped 1`.

Push was not performed for this slice.
