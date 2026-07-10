# Runtime Data Multi Filter Endpoint Coverage First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#274 replans after the URL persistence closure and chooses direct endpoint multi-filter coverage before changing generated browser controls. #275 implements the first small slice.

The endpoint already accepts a bounded map of `filter[field]=value` clauses. This slice promotes that behavior into public runtime endpoint smoke coverage so the contract is visible and protected before any generated UI expansion.

## Planned / Implemented

- Add a second filter field/value to the sample28, sample29, and sample31 endpoint smoke profiles.
- Add a direct `runtime-data.json` smoke that requests two simultaneous `filter[field]=value` clauses.
- Assert both filter clauses are echoed in `query.filter`.
- Assert the filtered list returns the expected selected row and both filtered fields match.
- Assert detail/form default selection still uses `query-result-first-row`.

## Boundary

- In scope: endpoint smoke coverage for bounded multi-field filter behavior.
- Out of scope: generated browser UI multi-filter controls, URL mirror/replay changes, endpoint contract changes, sort behavior changes, mutation behavior, artifact-key preview changes, and push.

## Verification

- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11124 assertions`, `1 skipped`)
