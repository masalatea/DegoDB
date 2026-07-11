# React Bridge Custom Operation Handoff First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#454 adds the first React bridge custom operation handoff index.

This is still metadata-only. It makes custom operation declarations easy for React consumers to discover without granting execution rights or wiring server routes.

## Implemented

- `bridge-contract.json` now includes `custom_operation_handoffs`.
- Each handoff includes contract key, operation key, label, category, target, side-effect class, availability, unavailable reason, policy key, CSRF requirement, audit event, adapter handoff key, and runtime screen keys.
- Generated TypeScript exports `MtoolCustomOperationHandoff`.
- React bridge consumer notes document the custom operation handoff boundary.
- React bridge build smoke summary reports custom operation handoff count and adapter handoff keys.
- Mtool dogfooding coverage verifies Review Artifact and Request Publish handoffs.

## Preserved Boundary

- No custom operation execution route is added.
- No build, publish, review-request, approval, or mutation action is wired.
- No custom React component execution is added.
- React consumers receive metadata for adapter planning only.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- Focused PHPUnit: `OK (8 tests, 150 assertions)`
- Focused PHPUnit: `OK (11 tests, 552 assertions)`
- `make sample28-no-code-react-bridge-build-smoke`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11304, Skipped: 1.`
- `git diff --check`
