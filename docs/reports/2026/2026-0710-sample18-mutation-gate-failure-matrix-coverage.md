# Sample18 Mutation Gate Failure Matrix Coverage

Date: 2026-07-10
Plan: #601
Status: FIRST_SLICE_DONE

## Summary

#601 adds focused coverage for sample18 generated submit mutation gate failure outcomes while keeping DBAccess mutation disabled.

The change is test-focused. It locks flag-on gate outcomes before any mutation dry-run execution path is introduced.

## Covered

- Flag-on duplicate route replay returns a blocked `mutation_gate`.
- Flag-on audit/idempotency failure route response returns a failed `mutation_gate`.
- Helper matrix covers:
  - duplicate idempotency;
  - audit skipped;
  - audit failed;
  - idempotency skipped;
  - idempotency failed;
  - invalid normalized request.
- Flag-on blocked/failed outcomes do not include `enablement_flag_disabled`.
- All matrix outcomes keep:
  - `ready=false` except the existing healthy metadata-only ready case;
  - `mutation_enabled=false`;
  - `executed=false`.

## Boundaries Kept

- DBAccess mutation is not executed.
- The generated submit route still returns HTTP 409 `generated_submit_disabled` for valid blocked submits.
- The dispatcher remains dry-run.
- The added route duplicate replay increments existing audit/idempotency duplicate evidence only for the test scenario.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (7 tests, 490 assertions)`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 389, Assertions: 12298, Skipped: 1.`
- `git diff --check`

## Next

#602 should close the mutation gate failure matrix lane and decide whether duplicate replay contract, dry-run execution preflight, or additional route-level failure coverage should be promoted next.
