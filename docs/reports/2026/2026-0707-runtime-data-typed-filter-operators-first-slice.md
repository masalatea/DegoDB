# Runtime Data Typed Filter Operators First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#298 chooses the typed filter operator first implementation slice after the boundary plan. #299 implements it.

## Implemented

- Added optional `filter_op[field]=contains|eq` parsing for current/alias read-only `runtime-data.json`.
- Preserved omitted-operator `filter[field]=value` behavior as the existing display-string `contains` match.
- Added exact display-value `eq` matching as the first explicit operator.
- Echoed normalized active operators through `query.filter_op`.
- Added generated operator selects for the primary and secondary runtime-data filter controls.
- Extended generated URL building, browser URL mirroring, initial URL replay, and back/forward replay to retain `filter_op[...]`.
- Extended endpoint and browser smokes for default `contains`, explicit `eq`, invalid operator fail-closed behavior, URL replay, and history preservation.

## Boundary

- In scope: current/alias read-only runtime-data filter operators, two visible generated filter rows, response query metadata, URL mirror/replay, and smoke coverage.
- Out of scope: numeric/date comparison semantics, multi-value filters, more visible filter rows, multi-column sort, broader read-model shape, mutation behavior, artifact-key preview behavior, and push.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11131 assertions`, `1 skipped`)

## Notes

- The boundary plan was adjusted before implementation because the existing field-filter endpoint was already display-string `contains`, not exact equality.
- `eq` is therefore additive behavior, while omitted operators and `contains` remain the compatibility path.
