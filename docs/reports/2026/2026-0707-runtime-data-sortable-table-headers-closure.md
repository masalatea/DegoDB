# Runtime Data Sortable Table Headers Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#332 chooses a closure report after the sortable runtime-data table header first slice landed. #333 closes the lane before adding visible sorted-column state, dynamic row builders, richer sort semantics, broader read-model shape, or push cleanup.

The accepted capability is intentionally small: generated current/alias runtime-data list tables expose clickable column headers, and a header click uses the existing read-only runtime-data query path to set that field as the primary sort.

## Accepted Boundary

- Current/alias runtime-data list headers are clickable when a runtime-data binding is available.
- Header sorting sets the clicked field as the primary sort.
- Clicking the already-primary ascending field toggles it to descending; otherwise header sorting starts ascending.
- Header sorting clears secondary and tertiary sort rows so the header action remains a simple one-column affordance.
- Existing search, filter, page-size, URL mirror, and control-retention behavior remain part of the same generated runtime path.
- Artifact-key previews remain static and do not become dynamic business-data readers.

## Verification Baseline

The implementation slice was verified before this closure with:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11140 assertions`, `1 skipped`)

This closure is documentation-only.

## Remaining Candidates

- Add visible sorted-column state such as `aria-sort`.
- Consider a compact sorted-column indicator in generated table headers.
- Add dynamic add/remove filter and sort rows only if the fixed three-row controls become too dense or too limiting.
- Add numeric/date-aware comparison and explicit null placement after the read-model can expose field typing cleanly.
- Improve grouped or mobile-specific query-control layout if the runtime-data control surface starts to crowd smaller screens.

## Push

Push was not performed.
