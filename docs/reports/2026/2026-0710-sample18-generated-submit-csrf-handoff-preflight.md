# Sample18 Generated Submit CSRF Handoff Preflight

Plan item: #574 sample18 generated submit CSRF handoff preflight

Status: DONE

## Summary

Defined and exposed the generated submit CSRF handoff contract for sample18 while keeping generated buttons disabled and mutation parked.

## Changes

- Added CSRF handoff fields to sample18 generated managed action `submit_binding_gate` metadata.
- Exposed `csrf_token_field`, `csrf_source_selector`, `csrf_transport`, and `csrf_submit_field`.
- Rendered stable runtime DOM markers for token field, source selector, and transport.
- Extended the sample18 fast fixture and focused PHPUnit assertions for the handoff contract.
- Extended the public runtime disabled-action smoke to verify the handoff markers.

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

#575 should close the CSRF handoff lane and decide whether disabled click intent or mutation dispatcher inventory should be promoted next.
