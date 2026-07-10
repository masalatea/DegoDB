# Runtime Data Sortable Header State Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#336 chooses a closure report after the generated sortable header state first slice landed. #337 closes the lane before adding compact icon treatment, dynamic row builders, richer sort semantics, broader read-model field typing, or push cleanup.

The accepted capability is intentionally narrow: generated current/alias runtime-data list tables now expose both a clickable sort affordance and a synchronized sorted-column state for the primary sort.

## Accepted Boundary

- Sortable generated list headers start with `aria-sort="none"`.
- Sortable generated list header buttons start with `data-runtime-sort-state="none"`.
- The primary sorted column syncs to `aria-sort="ascending"` or `aria-sort="descending"` after runtime-data query controls are updated from a payload.
- Non-primary sortable headers return to `aria-sort="none"`.
- Header state represents only the primary sort, even when the explicit controls include secondary and tertiary sort rows.
- Artifact-key previews remain static.
- The read-only `runtime-data.json` endpoint contract did not change.

## Verification Baseline

The implementation slice was verified before this closure with:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11142 assertions`, `1 skipped`)

This closure is documentation-only.

## Remaining Candidates

- Consider a compact icon treatment for active sort state.
- Dynamic add/remove filter and sort rows.
- Numeric/date-aware comparison and explicit null placement.
- Richer read-model field typing for stronger filter/sort semantics.
- Grouped or mobile-specific query-control layout.

## Push

Push was not performed.
