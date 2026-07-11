# Sample18 Generated Action/Input Route Compatibility Contract First Slice

Status: `FIRST_SLICE_DONE`

Plan: #687 sample18 generated action/input route compatibility contract first slice

## Summary

#687 adds focused fast assertions that compare sample18 generated no-code action/input metadata and generated DOM attributes against the executable generated-submit route contract.

This keeps the slice inside PHPUnit/JSON/DOM contract coverage. It does not enable mutation, replace the route, or broaden browser smoke.

## Changes

- Added route compatibility assertions to `Sample18MiniTaskBoardDemoTest`.
- Verified the executable operation set is limited to `create_task_card`, `update_task_card`, and `complete_task_card`.
- Verified `reopen_task_card` and `delete_task_card` remain metadata-only disabled candidates until DBAccess/custom adapter metadata exists.
- Compared generated action inventory against route key fields, required client fields, optional client fields, and server-managed fields.
- Compared generated screen-definition action fields against route-compatible role, required, and client-write expectations.
- Verified generated runtime HTML exposes matching `data-action-key`, `data-operation-key`, submit URL, CSRF handoff, and blocked-route binding attributes.
- Updated `docs/no-code-ui-testing.md` with the accepted fast compatibility coverage.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (28 tests, 1599 assertions)`

## Next

Promote #688: post action/input route compatibility contract lane closure.

The closure should decide whether to promote browser smoke coverage, route-compatible guarded submit handoff hardening, or broader generated availability documentation next.
