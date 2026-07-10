# Runtime Data Dynamic Row Builder First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#345 implements the first dynamic filter/sort row-builder slice for generated current/alias runtime-data controls.

The implementation keeps the existing fixed 3-filter / 3-sort DOM contract, but changes the visible default: only the primary filter row and primary sort row are shown initially. Secondary and tertiary rows are progressively revealed by explicit add controls or by existing URL/query payload values.

## Accepted Behavior

- Primary filter and sort rows remain visible by default.
- Secondary and tertiary filter rows are generated but hidden until needed.
- Secondary and tertiary sort rows are generated but hidden until needed.
- `Add filter` and `Add sort` reveal the next hidden row and disable once all three rows are visible.
- `Remove filter 2` / `Remove filter 3` clear and hide those rows.
- Removing the secondary row also clears the tertiary row so hidden stale values are not submitted.
- `Remove sort 2` / `Remove sort 3` clear and hide those rows with the same stale-value protection.
- URL replay / payload sync reveals hidden rows when secondary or tertiary query values exist.
- Sortable table header clicks still act as a primary-sort shortcut and collapse secondary/tertiary sort rows.

## Boundaries Kept

- The read-only `runtime-data.json` endpoint contract did not change.
- Filter request keys remain `filter[field]` and `filter_op[field]`.
- Sort request keys remain ordered `sort[field]=asc|desc`.
- Endpoint limits remain max 8 filters and max 3 ordered sorts.
- Browser URL mirror, initial replay, and back/forward replay stay on the existing read-only refresh path.
- Artifact-key previews remain static.
- No mutation, retry, outbox processing, numeric/date comparison, or null-placement behavior was added.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11152 assertions`, `1 skipped`)

## Push

Push was not performed.
