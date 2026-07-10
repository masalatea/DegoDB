# Sample18 Blocked Guarded Submit Click Binding First Slice

Date: 2026-07-10
Plan: #580
Status: DONE

## Summary

Sample18 generated managed actions now have a guarded click binding that can submit to the generated-submit route while the route still fails closed with `generated_submit_disabled`.

The action availability read model remains disabled. The runtime button enablement is separate and is only allowed when the submit binding gate explicitly says:

- `click_binding_state=blocked_route_enabled`
- `submit_trigger=guarded_click`
- `network_submit_enabled=true`
- `runtime_click_binding=true`
- `mutation_enabled=false`

## Changes

- Updated sample18 submit binding metadata and golden checklist from disabled preflight to blocked-route guarded click.
- Added runtime guard logic so generated action buttons become clickable only for the blocked-route submit path.
- Added guarded submit JavaScript that posts flat action payload fields plus CSRF to `/samples/sample18-task-board/no-code/generated-submit`.
- Added blocked feedback rendering with `data-state=blocked`, last submit result, and failure code markers.
- Updated the public runtime browser smoke to click `create_task_card` and assert `generated_submit_disabled` feedback.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- JSON parse: `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`
- `make sample18-pack-runtime-test`
- `make sample18-http-runtime-smoke`
- `make sample18-no-code-public-runtime-disabled-action-smoke`
- `make test` (`382 tests`, `12047 assertions`, `Skipped: 1`)
- `git diff --check`

## Next

Promote #581 to close this lane and decide whether mutation dispatcher inventory or extra blocked-feedback hardening should come next.
