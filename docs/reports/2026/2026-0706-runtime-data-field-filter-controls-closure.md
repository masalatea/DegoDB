# Runtime Data Field Filter Controls Closure

Date: 2026-07-06

Status: `DONE`

## Summary

#246 chooses a closure report after the endpoint and browser field-filter slices. #247 closes the runtime-data field filter controls lane before starting sort controls, persisted query state, or broader read-model polish.

This lane keeps the no-code surface grounded in the existing generated DBAccess/runtime-data foundation: filtering is a read-only current/alias data query, not a static preview rewrite and not part of submit/outbox mutation.

## Accepted Capability

- Current/alias `runtime-data.json` accepts bounded `filter[field]=value` queries.
- Field filters apply after global `q` search and before pagination/default detail-form selection.
- Invalid filter shape, invalid field names/values, too many filters, or unknown row fields fail closed.
- Current/alias generated runtime list screens expose explicit field filter controls.
- The filter field selector is generated from rendered screen fields.
- Filter requests start at page 1 when a page-size control is present.
- sample28, sample29, and sample31 prove browser controls request the expected `filter[field]=value` query and render the expected filtered row.

## Preserved Boundaries

- Normal Refresh remains a no-query full-list runtime-data reload.
- Search remains explicit through bounded `q`.
- Pagination and direct page navigation remain query-driven and metadata-driven.
- `selected_key` remains the detail/form row selection mechanism.
- Artifact-key previews remain immutable/static.
- Submit/outbox mutation remains separate from read-only runtime-data queries.

## Remaining Candidates

- Sort endpoint contract and browser sort controls.
- Clearer query reset affordance or state display for search/filter/page controls.
- Persisted query state, if product usage shows it is needed.
- Advanced filter operators or multiple visible filter rows.
- Form default semantics for create/edit flows beyond selected-row rendering.
- Accessibility and layout polish once the runtime-data control set stabilizes.

## Verification Baseline

Latest full verification before closure:

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11109 assertions`, `1 skipped`)

Push was not performed for this closure.
