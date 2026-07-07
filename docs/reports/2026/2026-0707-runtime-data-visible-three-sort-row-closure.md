# Runtime Data Visible Three-Sort Row Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#326 chooses closure after the third visible generated runtime-data sort row landed. #327 closes the visible three-sort-row lane before dynamic row builders, sortable column headers, richer sort semantics, broader read-model shape, or push cleanup.

## Accepted Capability

Generated current/alias runtime-data exploration now exposes all three ordered sort rows supported by the read-only endpoint contract:

- Primary `Sort` / `Direction`.
- Secondary `Sort 2` / `Direction 2`.
- Tertiary `Sort 3` / `Direction 3`.

Those rows are carried through generated query capture, payload sync, initial URL replay, URL mirror, and browser smoke probes. The endpoint contract remains bounded at three ordered sort fields and continues to fail closed above that limit.

The visible runtime-data browser surface now has matching first-slice capacities for the current fixed-row controls:

- Three visible filter rows on top of the endpoint max-8 additive filter contract.
- Three visible sort rows matching the endpoint max-3 ordered sort contract.

## Verification Baseline

Latest implementation verification from #325:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11138 assertions, 1 skipped)

This closure is docs-only, so no additional runtime test was required.

## Remaining Candidates

- Dynamic add/remove filter and sort rows.
- Sortable table headers that drive the same read-only query contract.
- Numeric/date-aware comparison and explicit null placement.
- Richer read-model field typing so sort semantics can move beyond display-value sorting.
- Grouped or mobile-specific query-control layout.
- Local commit stack review or push cleanup when the next boundary is a release/push decision.

## Boundary

- In scope: closure, accepted capability, verification baseline, remaining candidates, and current plan status.
- Out of scope: code changes, endpoint contract changes, new operators, sortable headers, layout redesign, history rewrite, and push.
