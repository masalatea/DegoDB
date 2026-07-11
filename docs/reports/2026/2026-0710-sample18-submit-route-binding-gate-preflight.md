# Sample18 Submit Route Binding Gate Preflight

Plan item: #570 sample18 submit route binding gate preflight

Status: DONE

## Summary

Defined the sample18 generated submit route binding gate in metadata and exposed stable runtime DOM markers without enabling runtime clicks or mutation dispatch.

## Changes

- Added `submit_binding_gate` metadata for sample18 generated managed actions.
- Carried the gate metadata through screen actions into runtime render actions.
- Rendered stable action button markers for binding state, CSRF source, and fail-closed result.
- Added fixture and PHPUnit assertions for the gate contract.
- Extended the sample18 disabled-action browser smoke to assert the gate markers in the public runtime.

## Boundary

This slice does not enable generated buttons, does not bind runtime clicks to `/samples/sample18-task-board/no-code/generated-submit`, does not call DBAccess, and does not enqueue outbox work. The generated submit route still fails closed with `generated_submit_disabled`.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- JSON parse check for `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample18-pack-runtime-test`
- `make sample18-no-code-public-runtime-disabled-action-smoke`
- `make test`
- `git diff --check`

## Next

#571 should close the binding gate lane and decide whether disabled click intent, CSRF handoff, or mutation dispatcher work should be promoted next.
