# Runtime Data URL Multi Filter Replay First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#286 replans after the pushed #281 generated browser two-filter controls boundary and chooses URL multi-filter replay/mirror as the next small lane. #287 implements the first slice.

Generated current/alias runtime previews can already send up to two filter controls through `runtime-data.json`. This slice makes that state portable through the browser URL as well.

## Planned / Implemented

- Parse multiple `filter[field]=value` clauses from the browser URL into the runtime-data query object.
- Replay those filters through the existing read-only `runtime-data.json` refresh path on initial preview load.
- Retain replayed primary and secondary filter control values after screen re-render.
- Extend the public runtime browser smoke so combined query mirror and initial URL replay both prove the second filter.

## Boundary

- In scope: URL mirror/replay handling for the generated two-filter browser control surface.
- Out of scope: endpoint contract changes, more than two visible browser filter rows, typed filter operators, multi-column sort, browser back/forward replay, mutation behavior, artifact-key preview changes, and push.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
  - Confirms current/alias combined query mirror and initial URL replay both preserve `filter[status]=triage&filter[priority]=20`.
- `make test`
  - `337 tests`, `11126 assertions`, `1 skipped`.
