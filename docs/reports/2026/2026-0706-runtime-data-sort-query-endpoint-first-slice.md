# Runtime Data Sort Query Endpoint First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#248 replans after the field filter controls closure and chooses endpoint sort query support before browser sort controls. #249 implements the first endpoint slice.

This keeps the read-only current/alias runtime-data contract explicit before adding another generated runtime control.

## Planned / Implemented

- Add bounded one-field `sort[field]=asc|desc` support for current/alias `runtime-data.json`.
- Validate field names with the same narrow field-key shape used by filter queries.
- Accept only `asc` or `desc` sort directions.
- Fail closed when multiple sort fields, invalid directions, or unknown row fields are requested.
- Apply sorting after global `q` search and field-specific filters.
- Apply sorting before pagination and default detail/form selection.
- Echo `query.sort` in the response.
- Extend direct endpoint smoke coverage for sample28, sample29, and sample31.

## Boundary

- In scope: current/alias read-only `runtime-data.json` sort queries.
- In scope: preserving no-query behavior, global search, field filters, selected-key behavior, pagination semantics, and outbox mutation separation.
- Out of scope: browser sort UI, multi-column sort, persisted sort state, artifact-key previews, and submit/outbox mutation behavior.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11109 assertions, skipped 1)

The sample28, sample29, and sample31 public runtime smokes each verified `current-sort` success and `current-sort-invalid` fail-closed behavior.
