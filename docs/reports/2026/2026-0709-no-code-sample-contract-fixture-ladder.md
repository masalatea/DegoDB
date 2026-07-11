# No-Code Sample Contract Fixture Ladder

Status: `DONE`

Plan item: #554 no-code sample contract fixture ladder

## Summary

Grew `sample32-no-code-ui-test-lab` from hard-coded test expectations into the first explicit no-code UI contract fixture ladder rung.

## Scope

- Added `sample/tutorials/sample32-no-code-ui-test-lab/fixtures/no-code-ui-contract-fixtures.json`.
- Moved sample32 screen, field, action, and preview-row expectations into the fixture JSON.
- Updated `Sample32NoCodeUiTestLabTest` to use the fixture for JSON and `DOMDocument` contract assertions.
- Updated the sample32 pack checker to read the same fixture, so `make sample32-pack-runtime-test` validates the fixture contract.
- Added reusable disabled managed-action DOM assertions to `NoCodeUiContractAssertions`.

## Boundary

This slice does not add JavaScript event execution, browser smoke coverage, or new generated UI capabilities. It only makes the contract fixture explicit and reusable for later no-code fixture increments.

## Verification

- `php -l mtool/scripts/lib/sample32_no_code_ui_test_lab_check.php`
- `php -l tests/Support/NoCodeUiContractAssertions.php`
- `php -l tests/Integration/Sample32NoCodeUiTestLabTest.php`
- `make sample32-pack-runtime-test`
- `make test`

## Next

#555 should evaluate a lightweight JS DOM interaction test only for behavior PHP JSON / `DOMDocument` contracts cannot prove.
