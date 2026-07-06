# Fresh Runtime Data Endpoint Route Contract First Slice

Status: DONE
Date: 2026-07-05

## Summary

This slice adds the first current/alias `runtime-data.json` route contract for generated no-code runtime screens. It wires the route and JSON contract, attempts generated DBAccess reads, and records the current fail-closed behavior when a web request does not have a runtime database binding.

## Implemented

- Added current data path helper: `/runs/no-code/{project}/current/runtime-data.json`.
- Added alias data path helper: `/runs/no-code/{project}/alias/{alias}/runtime-data.json`.
- Added route parser and HTTP dispatch entries for both routes.
- Marked both data routes as auth-required, matching the execution endpoint boundary rather than public preview HTML.
- Added `no-code-runtime-data-v0` JSON responses with `Cache-Control: no-store`.
- Added generated DBAccess list-read attempt for each no-code contract, using existing runtime screen rendering to shape successful screen data.
- Added fail-closed JSON responses when the selected artifact, screen definition, materialized DBAccess, or runtime DB connection is unavailable.
- Extended the sample28 public runtime endpoint smoke so it checks runtime-data current/alias routes before execution endpoints.

## Current Behavior

In the default sample28 public browser smoke, the web request can resolve the current/alias artifact and generated DBAccess code, but Apache does not yet have a runtime database binding for the business table. The endpoint therefore returns:

- HTTP 422
- `ok: false`
- `contract_version: no-code-runtime-data-v0`
- `runtime_preview_version: no-code-runtime-v0`
- a non-empty `error`
- `Cache-Control: no-store`

The smoke treats this as the expected fail-closed result for this slice. Existing public runtime submit, outbox enqueue, live polling, and outbox processing smokes continue to pass.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/app/router.php`
- `php -l mtool/app/http.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- Focused `OpenApiSourceOutputContractTest`: 22 tests, 1882 assertions
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test`: 337 tests, 11079 assertions, skipped 1

## Remaining Next Slice

The next implementation slice should bind a deterministic runtime database source into the web request path, then upgrade the runtime-data smoke from fail-closed JSON to a successful live row read for sample28. After sample28 succeeds, repeat the same route/data smoke for sample29 and sample31.
