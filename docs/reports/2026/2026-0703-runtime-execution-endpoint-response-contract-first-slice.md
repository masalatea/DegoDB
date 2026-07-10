# Runtime Execution Endpoint Response Contract First Slice

Date: 2026-07-03
Status: FIRST_SLICE_DONE

## Summary

Added an internal endpoint-response contract helper for server-backed no-code runtime execution. The helper maps execution results to an HTTP status code and endpoint-ready JSON payload without sending headers, registering a public route, or wiring generated preview forms to mutation.

This slice prepares the route boundary while keeping the user-facing runtime preview unchanged.

## Accepted Capability

- `app_no_code_runtime_execution_endpoint_response()` returns `status_code` plus a JSON-ready `payload`.
- Successful execution maps to HTTP `200` and preserves `request`, `intent`, and `result`.
- Bad method, missing action key, and invalid input shape map to HTTP `400`.
- CSRF failure maps to HTTP `403`.
- Project or artifact binding mismatch maps to HTTP `409`.
- Action-intent or dispatch validation errors map to HTTP `422`.
- The helper does not call `header()`, echo JSON, or register a public route.

## Verification

- `php -l mtool/app/no_code_runtime.php`: passed.
- Focused `NoCodeRuntimeTest`: `12 tests, 195 assertions`.
- `make sample28-no-code-runtime-ui-smoke`: passed.
- `git diff --check`: passed.
- Full `make test`: `331 tests, 10892 assertions, skipped 1`.

The focused PHPUnit run also emitted a `.phpunit.result.cache` write warning because the container filesystem is read-only for that cache file; the test run itself passed.

## Remaining Candidates

- Add the guarded public runtime mutation route.
- Wire generated runtime preview action submission to the route.
- Add server response success/error display to the generated preview.
- Refresh rendered data after successful mutation.
- Add runtime execution audit trail.

Push was not performed for this slice.
