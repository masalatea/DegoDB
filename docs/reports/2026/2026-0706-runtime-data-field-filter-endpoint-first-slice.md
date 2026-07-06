# Runtime Data Field Filter Endpoint First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#242 replans after the runtime-data query controls closure and chooses endpoint field-specific filters before browser filter UI or sort controls. #243 implements the first slice.

This keeps the read-only endpoint contract explicit before adding more generated runtime controls.

## Implemented

- Added bounded `filter[field]=value` support for current/alias `runtime-data.json`.
- Filters generated DBAccess rows by field display value.
- Applies field filters after global `q` search and before pagination/default detail-form selection.
- Echoes `query.filter` in the response.
- Fails closed for invalid filter query shape, invalid field names/values, too many filters, or unknown row fields.
- Extends direct endpoint smoke coverage for sample28, sample29, and sample31.

## Boundary

- In scope: current/alias read-only `runtime-data.json` field-specific filters.
- In scope: preserving no-query behavior, global search behavior, selected-key behavior, pagination semantics, and outbox mutation separation.
- Out of scope: browser filter UI, sort controls, advanced operators, persisted filter state, artifact-key previews, and submit/outbox mutation behavior.

## Verification

Passed before commit:

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11105 assertions`, `1 skipped`)

Note: Docker-backed checks were run with normal Docker permissions because buildx writes activity metadata under `~/.docker`.
