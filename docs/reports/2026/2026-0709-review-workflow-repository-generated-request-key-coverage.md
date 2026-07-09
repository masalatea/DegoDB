# Review Workflow Repository Generated Request Key Coverage

Date: 2026-07-09

Status: `FIRST_SLICE_DONE`

## Summary

#517 adds focused coverage for generated review request keys in the review workflow repository.

This stays non-executable and does not change availability or generated buttons.

## Covered Behavior

- Blank `review_request_key` inputs generate a non-empty key.
- Generated request keys use the `review_` prefix.
- Generated request keys are persisted and can be fetched back by artifact identity.

## Out Of Scope

- Push.
- Changing `review_source_output_artifact` availability to `available`.
- Generated button execution.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.

## Verification

- `php -l tests/Integration/NoCodeReviewWorkflowRepositorySqliteTest.php`
- Focused PHPUnit review workflow repository generated request key: `OK (11 tests, 131 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 372, Assertions: 11587, Skipped: 1.`
- `git diff --check`

## Next Candidate

#518 should close generated request key coverage and decide whether to pause local commits again.
