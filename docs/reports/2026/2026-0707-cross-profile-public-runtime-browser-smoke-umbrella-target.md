# Cross-profile public runtime browser smoke umbrella target

Date: 2026-07-07

## Summary

#384 adds one Makefile umbrella target for the current product-facing public no-code runtime browser smoke matrix.

`make sample-no-code-public-runtime-browser-smoke` now runs:

- `sample28-no-code-public-runtime-browser-smoke`
- `sample29-no-code-public-runtime-browser-smoke`
- `sample31-no-code-public-runtime-browser-smoke`

The generated runtime contracts, sample data, public routes, submit/outbox behavior, and runtime-data endpoint behavior are unchanged.

## Implementation

- Added `sample-no-code-public-runtime-browser-smoke` to `Makefile`.
- Kept the existing per-sample smoke targets as the source of truth.
- Fixed the shared browser smoke probe so invalid typed-filter validation is only exercised on the typed sample31 path.
- Kept sample29 focused on multi-filter retention coverage.

The first umbrella run exposed a smoke-probe responsibility issue. The probe briefly reused the invalid typed-filter path while validating sample29 multi-filter retention, which cleared secondary/tertiary filter state in the validation path. That was not a generated runtime contract failure. The probe now uses sample31 `QuantityNeeded = 1.5` to verify integer validation copy and fetch prevention, while sample29 continues to verify retained multi-filter controls.

## Verification

Passed:

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make sample-no-code-public-runtime-browser-smoke`

The first sandboxed umbrella run failed before executing the target logic because Docker buildx attempted to write under `~/.docker/buildx/activity`, which is outside the workspace sandbox. The target was rerun with escalated local permissions and passed.

Full `make test` was not rerun because the code change is limited to Makefile orchestration plus browser-smoke probe behavior, and the full public runtime browser smoke matrix was run.

## Push Boundary

No push was performed.
