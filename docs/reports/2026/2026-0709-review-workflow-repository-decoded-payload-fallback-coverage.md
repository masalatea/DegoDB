# Review Workflow Repository Decoded Payload Fallback Coverage

Date: 2026-07-09

Status: `FIRST_SLICE_DONE`

## Summary

#523 adds focused coverage for decoded payload fallback in the review workflow repository read model.

This stays non-executable and does not change availability or generated buttons.

## Covered Behavior

- Malformed stored `audit_event` JSON decodes to an empty array.
- Malformed stored `metadata_json` decodes to an empty array.
- Other row identity fields remain readable when payload JSON is malformed.

## Out Of Scope

- Push.
- Changing `review_source_output_artifact` availability to `available`.
- Generated button execution.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.

## Verification

- `php -l tests/Integration/NoCodeReviewWorkflowRepositorySqliteTest.php`
- Focused PHPUnit review workflow repository decoded payload fallback: `OK (13 tests, 138 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 374, Assertions: 11594, Skipped: 1.`
- `git diff --check`

## Next Candidate

#524 should close decoded payload fallback coverage and decide whether to pause local commits again.
