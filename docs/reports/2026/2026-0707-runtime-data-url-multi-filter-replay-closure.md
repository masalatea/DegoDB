# Runtime Data URL Multi Filter Replay Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#288 replans after the URL multi-filter replay first slice and chooses closure before starting another behavior lane. #289 closes the lane.

## Accepted Capability

Current/alias generated runtime previews can now carry a combined read-only runtime-data exploration through the browser URL when the query includes:

- `q`
- `page` / `page_size`
- one-field `sort[field]=asc|desc`
- up to two generated browser filter controls represented as `filter[field]=value`

The initial URL replay path parses multiple `filter[...]` clauses, sends them through the existing read-only `runtime-data.json` refresh path, and restores the primary and secondary generated filter controls after screen re-render.

## Latest Verification Baseline

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test`
  - `337 tests`, `11126 assertions`, `1 skipped`.

## Remaining Candidates

- Browser back/forward replay for runtime-data query changes.
- Typed filter operators.
- More than two visible generated filter rows.
- Multi-column sort.
- Broader read-model shape and display policy.

## Boundary

- In scope: closure, accepted capability, latest verification baseline, and remaining candidates.
- Out of scope: new code, endpoint contract changes, browser history navigation behavior, mutation behavior, artifact-key preview changes, and push.
