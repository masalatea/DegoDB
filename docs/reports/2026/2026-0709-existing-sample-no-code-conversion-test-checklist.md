# Existing Sample No-Code Conversion Test Checklist

Status: `DONE`

Plan item: #556 existing sample no-code conversion test checklist

## Summary

Applied the fast contract checklist pattern to the first existing sample no-code conversion target, `sample18-mini-task-board-demo`.

## Scope

- Added `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`.
- The checklist records the metadata contract, generated HTML DOM contract, disabled dry-run action keys, conversion boundary, and remaining gaps before browser smoke.
- Updated `Sample18MiniTaskBoardDemoTest` so the existing sample conversion assertions read from the checklist rather than hard-coded expectations.

## Boundary

This slice does not replace the curated `/samples/sample18-task-board` route, enable generated mutation buttons, or add a new browser smoke. `make sample18-http-runtime-smoke` remains the outer route-level confidence check.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`

## Next

#557 should replan the next L1 sample conversion increment: either close sample18 filter/action-input gaps or pick the next existing sample candidate.
