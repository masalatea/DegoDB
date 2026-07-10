# Sample18 Selected Row/Key Handoff Fast Contract First Slice

Status: `FIRST_SLICE_DONE`

Plan: #691 sample18 selected row/key handoff fast contract first slice

## Summary

#691 adds fast selected-row/key handoff coverage for sample18 generated runtime preview rows and keyed generated action intents.

This uncovered and fixed a real gap: static runtime list rendering did not expose row key markers because the list action set did not include a key-bearing action. The runtime now preserves `is_key` field metadata and falls back to key fields when deriving row identity.

## Changes

- Preserved `is_key` in no-code runtime render fields.
- Added a runtime key-field helper and emitted `data-runtime-row-key` on static list rows when a key display value exists.
- Added JS fallback so runtime-data-backed list selection can derive the key field from render fields when action fields do not provide one.
- Added sample18 assertions for generated runtime row id, static row key marker, update/complete key payload handoff, missing-key fail-closed behavior, and selected-key refresh source markers.
- Updated `docs/no-code-ui-testing.md` with the selected-row/key handoff contract.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (28 tests, 1663 assertions)`

## Next

Promote #692: post selected row/key handoff lane closure.

That closure should decide whether sample18 is now ready for browser smoke, generated availability expansion, or one more fast runtime contract.
