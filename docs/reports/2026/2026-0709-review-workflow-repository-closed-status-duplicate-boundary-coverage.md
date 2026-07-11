# Review Workflow Repository Closed-Status Duplicate Boundary Coverage

Date: 2026-07-09

Status: `FIRST_SLICE_DONE`

## Summary

#502 adds focused coverage for the closed-status duplicate boundary in review workflow repository persistence.

This stays non-executable and does not change availability or generated buttons.

## Covered Behavior

- A closed review request can be stored for an identity.
- A later requested review request for the same identity is created as a new row.
- Closed and new requested rows can both be fetched by the same identity filters.

## Out Of Scope

- Push.
- Changing `review_source_output_artifact` availability to `available`.
- Generated button execution.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.

## Verification

- `php -l tests/Integration/NoCodeReviewWorkflowRepositorySqliteTest.php`
- Focused PHPUnit review workflow repository closed-status duplicate boundary: `OK (6 tests, 67 assertions)`
- Full `make test` after Docker Desktop restart and one-time `ubuntu:24.04` base image pull: `OK, but incomplete, skipped, or risky tests! Tests: 367, Assertions: 11523, Skipped: 1.`
- `git diff --check`

## Next Candidate

#503 should close closed-status duplicate boundary coverage and decide whether to pause local commits again.
