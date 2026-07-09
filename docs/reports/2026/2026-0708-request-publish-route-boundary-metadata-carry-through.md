# Request Publish Route Boundary Metadata Carry-Through

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#463 carries the `request_source_output_publish` route boundary from Mtool dogfooding metadata into the existing no-code custom operation pipeline.

This remains non-executing. No POST route, approval mutation, build publish, review request, or custom component execution is added.

## Scope

- Added structured `route_boundary` metadata for `request_source_output_publish`.
- Reused the existing custom operation normalization path.
- Reused the existing operator action panel route-boundary wording.
- Confirmed React bridge custom operation handoff carries the publish route boundary.
- Confirmed runtime preview action item metadata carries the publish route boundary.

## Route Boundary

- Method: `POST`
- Path: `/projects/{project_key}/source-outputs/{source_output_key}/operations/request-source-output-publish`
- Response shape: `html_redirect`
- Auth guard: `mtool_operator_admin`
- Idempotency: `duplicate_safe`
- Failure modes:
  - `unavailable`
  - `unauthorized`
  - `missing_csrf`
  - `missing_artifact`
  - `stale_artifact`
  - `duplicate_request`

## Verification

- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- Focused PHPUnit: `OK (8 tests, 170 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11324, Skipped: 1.`
- `git diff --check`

## Boundary

- Generated HTML and React bridge handoffs remain metadata-only.
- Generated operator action buttons remain disabled.
- Route boundary text may be shown for `request_source_output_publish`, but no execution right is created.
- A future implementation must separately connect authorization, CSRF verification, audit append, duplicate handling, stale-artifact checks, and operation availability.
