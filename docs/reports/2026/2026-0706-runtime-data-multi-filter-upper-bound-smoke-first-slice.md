# Runtime Data Multi Filter Upper Bound Smoke First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#276 replans after direct endpoint multi-filter coverage and chooses an upper-bound smoke before generated multi-filter browser UI. #277 implements the first small slice.

The endpoint contract accepts a bounded map of `filter[field]=value` clauses. This slice protects the upper bound by verifying that 9 simultaneous filter fields fail closed with the documented 8-field limit.

## Planned / Implemented

- Add a direct `runtime-data.json` smoke that requests 9 simultaneous `filter[field]=value` clauses.
- Assert the endpoint returns HTTP 422.
- Assert the response remains JSON and has `ok: false`.
- Assert the error message is `runtime data filter query accepts 8 fields or less.`

## Boundary

- In scope: fail-closed endpoint smoke coverage for the multi-filter upper bound.
- Out of scope: generated browser UI multi-filter controls, URL mirror/replay changes, endpoint contract changes, sort behavior changes, mutation behavior, artifact-key preview changes, and push.

## Verification

- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11124 assertions`, `1 skipped`)
