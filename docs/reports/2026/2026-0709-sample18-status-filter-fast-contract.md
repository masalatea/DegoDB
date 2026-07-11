# Sample18 Status Filter Fast Contract

Status: `DONE`

Plan item: #558 sample18 status filter fast contract

## Summary

Added the first sample18 status filter fast contract without enabling generated route replacement or mutation.

## Scope

- Extended `sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json` with a `status_filter_contract`.
- The contract ties the curated route status values (`todo`, `doing`, `done`, plus all-status empty value) to generated `task_card_list` runtime preview metadata.
- Added reusable runtime-preview field assertion support to `NoCodeUiContractAssertions`.
- Updated `Sample18MiniTaskBoardDemoTest` to verify the curated route status filter values and generated `status` field metadata from the checklist.

## Boundary

The static generated artifact does not render runtime-data filter DOM controls until public runtime-data binding is present. This slice therefore fixes the metadata and curated-route comparison boundary first. DOM controls remain a later public-runtime binding check.

## Verification

- `php -l tests/Support/NoCodeUiContractAssertions.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`

## Next

#559 should decide whether to add public-runtime status filter DOM coverage for sample18 or move to safe action-input mapping.
