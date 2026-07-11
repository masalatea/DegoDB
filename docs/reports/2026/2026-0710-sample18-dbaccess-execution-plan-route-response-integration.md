# Sample18 DBAccess Execution-Plan Route Response Integration

Date: 2026-07-10
Plan: #606
Status: FIRST_SLICE_DONE

## Summary

#606 wires non-mutating DBAccess execution-plan metadata into valid sample18 generated-submit route responses.

The route still returns HTTP 409 `generated_submit_disabled`. The new metadata is inspectable only; it does not execute DBAccess or open a transaction.

## Implemented

- Valid generated-submit route responses now include `dbaccess_execution_plan`.
- Default disabled route responses expose blocked execution-plan metadata with:
  - `mutation_gate_not_ready`;
  - `enablement_flag_disabled`;
  - `mutation_enabled=false`;
  - `executed=false`;
  - `transaction=not_opened`.
- Flag-on duplicate route replay exposes blocked execution-plan metadata and carries duplicate reasons.
- Audit/idempotency failure route responses expose failed execution-plan metadata and carry failure reasons.
- Method, CSRF, validation, and unknown-operation failures still omit execution-plan metadata.

## Boundaries Kept

- DBAccess mutation is not executed.
- No transaction is opened.
- Top-level route response remains HTTP 409 for valid blocked submits.
- Top-level `mutation_enabled=false` remains unchanged.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (8 tests, 566 assertions)`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 390, Assertions: 12374, Skipped: 1.`
- `git diff --check`

## Next

#607 should close the execution-plan route metadata lane and decide whether transaction boundary preflight, route-level ready-plan coverage, or execution audit update preflight should be promoted next.
