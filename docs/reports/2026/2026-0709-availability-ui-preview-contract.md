# Availability UI Preview Contract

Date: 2026-07-09

Status: `DONE`

## Summary

#541 renders the metadata-only availability read model into generated no-code runtime HTML as stable DOM markers and human-readable preview copy. Generated operator buttons remain disabled.

## Changes

- Added availability attributes to operator action item containers and buttons:
  - `data-availability-state`;
  - `data-operation-availability`;
  - `data-preflight-result`;
  - `data-execution-mode`;
  - `data-generated-button-enabled`.
- Added `data-extension-slot-availability` and `data-availability-reason` preview text.
- Kept the existing unavailable reason and route-boundary messages.
- Added focused contract assertions for the generated HTML markers.

## Boundary

- This is still preview-only.
- Runtime HTML action buttons are disabled.
- `generated_button_enabled=false` remains the contract.
- No route execution, mutation, publish request, or generated button dispatch is enabled.
- Push is not performed.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- Focused PHPUnit: `OK (8 tests, 190 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 376, Assertions: 11638, Skipped: 1.`
- `git diff --check`
