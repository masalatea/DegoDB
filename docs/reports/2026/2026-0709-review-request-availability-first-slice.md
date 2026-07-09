# Review Request Availability First Slice

English companion:
This report records #542, the first narrow availability enablement slice for the no-code dogfooding review request flow.

## Summary

- Status: `DONE`
- Date: 2026-07-09
- Scope: enable only `review_source_output_artifact` as a plan-only available custom operation in Mtool dogfooding metadata.
- Push: not performed.

## What Changed

- `review_source_output_artifact` now reports `availability=available` in the dogfooding screen definition metadata.
- Its availability read model now exposes `availability_state=plan_only_ready`, `preflight_result=not_evaluated`, and `execution_mode=plan-only`.
- The generated no-code runtime HTML exposes the plan-only availability markers while keeping generated buttons disabled through `generated_button_enabled=false`.
- `request_source_output_publish` remains deferred.

## Boundary Kept

- The route-local guard and persistence path is still guard-first.
- Accepted-plan persistence is reachable only after policy, CSRF, source output, and current artifact checks pass.
- Generated no-code operator buttons remain disabled and are not executable from the generated preview.
- Publish, approval transitions, rollback, build, and generated button execution remain out of scope.

## Verification

- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- `php -l tests/Integration/NoCodeCustomOperationDispatchTest.php`
- Focused PHPUnit screen definition: `OK (8 tests, 195 assertions)`
- Focused PHPUnit custom operation dispatch: `OK (6 tests, 54 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 376, Assertions: 11643, Skipped: 1.`
- `git diff --check`

## Next

- #543 Post-availability sample UI replan becomes `ACTIVE_NEXT`.
