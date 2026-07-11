# Sample18 Generated Submit Route Browser Preflight

Plan item: #567 sample18 generated submit route browser preflight

Status: DONE

## Summary

Proved the sample18 public runtime can expose the blocked generated submit route marker on disabled managed action buttons without enabling mutation.

## Changes

- Added `submit_route` metadata for sample18 generated managed actions.
- Rendered the blocked submit route as `data-action-submit-url` on generated runtime action buttons.
- Extended sample18 fast contract coverage to assert the route marker in generated JSON/HTML.
- Extended the sample18 public runtime disabled-action browser smoke to assert the submit URL marker while buttons and runtime execute remain disabled.

## Boundary

This slice does not wire button clicks to the blocked submit route, does not enable generated buttons, does not call DBAccess, and does not enqueue outbox work. The route is visible for inspection only.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- JSON parse check for `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`
- `make sample18-pack-runtime-test`
- `make sample18-no-code-public-runtime-disabled-action-smoke`
- `make test`
- `git diff --check`

## Next

#568 should close this blocked submit-route preflight lane and decide whether route binding, HTTP smoke, or mutation dispatch should be promoted next.
