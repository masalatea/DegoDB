# No-Code UI Contract Test Harness First Slice

Status: `DONE`

Plan item: #552 no-code UI contract test harness first slice

## Summary

Added the first reusable fast no-code UI contract test harness without launching headless Chrome. The first consumer is `sample18-mini-task-board-demo`.

## Scope

- Added `tests/Support/NoCodeUiContractAssertions.php`.
- The helper reads generated JSON artifacts and parses `runtime-preview.html` with PHP `DOMDocument` / `DOMXPath`.
- Added reusable assertions for:
  - runtime preview screen keys;
  - generated screen/body/summary DOM markers;
  - generated form fields;
  - disabled extension-slot action buttons;
  - route-boundary markers for dry-run custom operations.
- Wired the helper into `Sample18MiniTaskBoardDemoTest`.

## Boundary

This harness checks generated metadata and static HTML structure only. It does not execute JavaScript, evaluate layout, submit mutations, or replace browser smoke tests.

## Verification

- `php -l tests/Support/NoCodeUiContractAssertions.php`
- `php -l tests/bootstrap.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`

## Next

#553 can add a dedicated no-code-only test lab sample that starts from small fixtures and uses this harness as its inner-loop contract.
