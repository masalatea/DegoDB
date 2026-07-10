# Runtime Data Browser Two-Sort Controls Closure / runtime-data browser two-sort controls closure

Status: DONE

#320 chooses a closure report after the secondary generated sort row landed. #321 closes the browser-visible multi-sort lane before another behavior expansion or push cleanup.

## Accepted Capability / 受け入れ済み capability

Generated current/alias runtime-data exploration now exposes two visible ordered sort rows:

- Primary `Sort` / `Direction`.
- Secondary `Sort 2` / `Direction 2`.
- Combined read-only runtime-data requests carry both sort fields in request order.
- Returned `query.sort` metadata restores both sort controls after live data rendering.
- Browser URL mirroring and initial URL replay preserve both sort fields.
- Shared public runtime browser smoke coverage proves the behavior across sample28, sample29, and sample31.

The read-only `runtime-data.json` endpoint remains the stronger foundation: it accepts up to three ordered sort fields and fails closed above that limit. The generated UI intentionally exposes only two rows for this lane.

## Verification Baseline / 検証 baseline

Latest implementation verification from #319:

- `git diff --check`
- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`

Full `make test` result: 337 tests, 11136 assertions, 1 skipped.

## Remaining Candidates / 残候補

- Add a third visible sort row to match the endpoint max-3 contract.
- Add dynamic sort-row builders instead of fixed visible rows.
- Add sortable table headers that set the same read-only query controls.
- Add numeric/date-aware comparison and explicit null placement.
- Add richer read-model field typing so sort semantics can move beyond display-value sorting.
- Run local commit stack review or push cleanup when the next boundary is a release/push decision.

## Push Boundary / push 境界

No push was performed for #320/#321.
