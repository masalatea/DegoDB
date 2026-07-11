# Dedicated No-Code UI Test Lab Sample

Status: `DONE`

Plan item: #553 dedicated no-code UI test lab sample

## Summary

Added `sample32-no-code-ui-test-lab`, a deliberately small no-code-only sample for fast UI contract testing.

## Scope

- Added sample pack `sample/tutorials/sample32-no-code-ui-test-lab`.
- Added project key `SAMPLE32` and table `no_code_lab_card`.
- Added `NO-CODE-RUNTIME` source output only.
- Added managed-screen metadata for list/detail/form generation.
- Added one disabled managed action fixture: `archive_no_code_lab_card`.
- Added fixed preview rows for `SAMPLE32/no_code_lab_card`.
- Added `Sample32NoCodeUiTestLabTest` using the reusable `NoCodeUiContractAssertions` helper.

## Boundary

This sample is not a product/domain sample and does not add browser smoke coverage. It is a small fixture lab for growing JSON and static DOM contract assertions before using headless Chrome.

## Verification

- `php -l mtool/scripts/lib/sample32_no_code_ui_test_lab_check.php`
- `php -l tests/Integration/Sample32NoCodeUiTestLabTest.php`
- `php -l tests/bootstrap.php`
- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `make sample32-pack-runtime-test`

## Next

#554 should grow this lab fixture ladder in small increments, starting with explicit fixture naming and reusable assertions for additional field/action states.
