# Sample18 Generated Submit Mutation Gate Helper First Slice

Date: 2026-07-10
Plan: #599
Status: FIRST_SLICE_DONE

## Summary

#599 adds a non-mutating mutation gate helper for sample18 generated submit.

The helper can report readiness metadata but does not execute DBAccess and does not enable mutation.

## Implemented

- Added explicit enablement flag helper:
  - app-level `sample18_generated_submit_mutation_enabled`;
  - environment fallback `MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED=1`.
- Added mutation gate helper with statuses:
  - `disabled`;
  - `blocked`;
  - `failed`;
  - `ready`.
- Added route response metadata `mutation_gate`.
- Added focused coverage that:
  - default/missing flag is disabled;
  - healthy audit/idempotency states can return `ready` only as non-mutating metadata;
  - duplicate idempotency blocks mutation;
  - audit/idempotency failures block mutation;
  - route responses still keep `mutation_enabled=false` and `executed=false`.
- Updated HTTP smoke to assert default disabled gate behavior.

## Boundaries Kept

- DBAccess mutation is not executed.
- Top-level route response remains HTTP 409 `generated_submit_disabled`.
- Dispatcher remains dry-run.
- The helper does not change TaskCard rows.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `php -l mtool/scripts/check_sample18_task_board_http_smoke.php`
- `make sample18-pack-runtime-test`: `OK (7 tests, 454 assertions)`
- `make sample18-http-runtime-smoke`: `OK`
- `make sample18-no-code-public-runtime-disabled-action-smoke`: `OK`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 389, Assertions: 12262, Skipped: 1.`
- `git diff --check`

## Next

#600 should close the mutation gate helper lane and decide whether the next slice is gate failure matrix coverage, duplicate replay contract, or DBAccess mutation dry-run execution.
