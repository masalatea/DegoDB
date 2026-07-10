# Runtime Data Search Query Endpoint First Slice

Date: 2026-07-05

Status: `DONE`

## Summary

#236 replans after the direct page input slice and chooses endpoint search query support before browser search UI. #237 implements the first slice.

This keeps the read-only `runtime-data.json` contract ahead of the generated runtime controls. Browser search UI can be layered later on a fixed endpoint behavior.

## Implemented

- Added bounded `q` query support for current/alias `runtime-data.json`.
- Filters generated DBAccess rows by rendered display value.
- Applies search before pagination and before default detail/form row selection.
- Echoes `query.q` in the runtime-data response.
- Extends the direct endpoint smoke to verify searched rows and detail selection across sample28, sample29, and sample31.

## Boundary

- In scope: current/alias read-only `runtime-data.json` search query.
- In scope: preserving no-query behavior, selected-key behavior, pagination semantics, and outbox mutation separation.
- Out of scope: browser search UI, field-specific filters, sorting controls, advanced operators, artifact-key previews, and submit/outbox mutation behavior.

## Verification

Passed before commit:

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11102 assertions`, `1 skipped`)

Note: Docker-backed checks were run with normal Docker permissions because buildx writes activity metadata under `~/.docker`.
