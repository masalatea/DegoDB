# Sample18 Disabled Submit Click Intent Preflight

Plan item: #576 sample18 disabled submit click intent preflight

Status: DONE

## Summary

Proved sample18 generated submit action buttons remain disabled, non-clicking, and non-submitting while exposing click intent metadata for a later guarded click-binding lane.

## Changes

- Added click binding state, submit trigger, and network submit flags to sample18 generated managed action `submit_binding_gate` metadata.
- Rendered stable runtime DOM markers for click binding state, submit trigger, and network submit enablement.
- Extended the sample18 fast fixture and focused PHPUnit assertions for the disabled click intent contract.
- Extended the public runtime disabled-action smoke to programmatically call `button.click()` on disabled generated submit buttons and confirm no click event or action state change occurs.

## Boundary

This slice does not enable generated buttons, does not bind runtime clicks to the generated submit route, does not call DBAccess, and does not enqueue outbox work. Mutation remains disabled.

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

#577 should close the disabled click intent lane and decide whether guarded click binding or mutation dispatcher inventory should be promoted next.
