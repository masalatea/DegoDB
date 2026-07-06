# Runtime Data URL Persistence Closure

Date: 2026-07-06

Status: `DONE`

## Summary

#272 replans after initial URL query replay and chooses closure before starting richer filter/sort models or broader read-model shape. #273 closes the runtime-data URL persistence lane.

This closure records the accepted capability for current/alias public runtime previews: read-only runtime-data exploration can now be represented in the browser URL and replayed on initial page load, while immutable artifact-key previews and mutation behavior remain separate.

## Accepted Capability

- Successful current/alias read-only runtime-data fetches mirror known query parameters into `runtime-preview.html` with `history.replaceState`.
- Mirrored parameters include `selected_key`, `q`, `page`, `page_size`, one `filter[field]`, and one `sort[field]`.
- The generated `Clear` control removes known runtime-data query parameters from the browser URL after a no-query refresh.
- Initial current/alias preview load consumes known runtime-data query parameters once through the existing read-only `runtime-data.json` refresh path.
- Returned `query` / `pagination` metadata continues to restore generated controls after screen re-render.
- Immutable artifact-key previews remain static because they do not receive a `runtime_data_url` binding.
- Endpoint contracts, submit/outbox mutation behavior, and generated artifact-key behavior remain unchanged.

## Latest Verification Baseline

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11124 assertions`, `1 skipped`)

## Remaining Candidates

- Browser history navigation: decide separately whether query changes should use `pushState` and whether `popstate` should replay runtime data.
- Richer filter/sort model: multiple filters, typed operators, or multi-column sort after the one-filter/one-sort contract has proven sufficient.
- Broader read-model shape: relation-shaped rows, display labels, denormalized context fields, or generated field display metadata beyond the current DBAccess row shape.
- Cross-sample URL replay smoke: extend the initial replay browser smoke beyond sample28 if sample29/sample31 need URL-specific coverage.
- Commit/push cleanup: review the local stack before the next push.

## Boundary

- In scope: closure, accepted current/alias URL persistence capability, latest verification baseline, remaining candidates, and no-push status.
- Out of scope: new code, endpoint contract changes, `pushState` / `popstate`, multi-filter/multi-sort behavior, read-model expansion, mutation behavior, and push.
