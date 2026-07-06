# Runtime Data Query Controls Final Closure

Date: 2026-07-06

Status: `DONE`

## Summary

#260 replans after the combined runtime-data query controls first slice and chooses closure before another read-model or mutation-adjacent behavior lane. #261 closes the current runtime-data query controls lane.

This closure records the current accepted capability for current/alias public runtime previews: users can explore read-only live generated DBAccess rows through a coherent generated control surface while immutable artifact-key previews and submit/outbox mutation behavior remain separate.

## Accepted Capability

- Current/alias runtime previews can fetch authenticated, no-store, read-only `no-code-runtime-data-v0` snapshots.
- Generated list rows support explicit row selection through `selected_key`.
- List rows support bounded pagination, page-size changes, direct page jumps, and total-row visibility.
- Runtime-data controls support bounded global search with `q`.
- Runtime-data controls support one bounded field filter through `filter[field]=value`.
- Runtime-data controls support one bounded sort through `sort[field]=asc|desc`.
- Search, filter, sort, page, and page-size controls preserve one another's active values in combined browser requests.
- Returned `query` / `pagination` metadata restores active generated control values after screen re-render.
- Runtime-data controls expose stable grouping semantics through `data-runtime-data-controls`, `role="group"`, and `aria-label="Runtime data controls"`.
- Normal Refresh remains a no-query full-list reload.
- Immutable artifact-key previews remain static.
- Submit/outbox mutation behavior remains outbox-based and separate from read-only runtime-data queries.

## Latest Verification Baseline

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11116 assertions`, `1 skipped`)

## Remaining Candidates

- Form default semantics: clarify when detail/form values should track selected rows, filtered rows, or explicit form defaults.
- Query reset affordance: add explicit Clear / Reset controls if user testing shows no-query Refresh is not discoverable enough.
- URL/history persistence: mirror current read-only query state into browser URL/history without affecting immutable artifact-key previews.
- Richer filter/sort model: multiple filters, typed operators, or multi-column sort after the one-filter/one-sort contract has proven sufficient.
- Visual density polish: compact the dense control row for smaller screens without changing request behavior.
- Broader read-model shape: relation-shaped rows, display labels, or denormalized context fields beyond the current generated DBAccess row shape.
- Commit/push cleanup: review the local stack before the next push.

## Boundary

- In scope: docs closure, accepted current/alias runtime-data query-control capability, latest verification baseline, remaining candidates, and no-push status.
- Out of scope: new code, new endpoint contract, visual redesign, mutation behavior, history persistence, and push.
