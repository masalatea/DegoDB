# Review Workflow Repository In-Review Duplicate Reuse Coverage

Date: 2026-07-09

Status: `FIRST_SLICE_DONE`

## Summary

#532 adds focused coverage that an existing `in_review` review workflow request is treated as an open request and reused for a duplicate request with the same identity.

## Scope

- Added `testInReviewWorkflowRequestIsReusedForDuplicateArtifactRequest`.
- Covered an existing `in_review` request for the same project, source output, artifact, and operation.
- Confirmed duplicate creation returns `result: duplicate`.
- Confirmed the duplicate response reuses the existing review request key and preserves `status: in_review`.

## Boundary

- Repository behavior was already configured with `in_review` in open statuses; this slice adds coverage only.
- Availability enablement remains parked.
- Generated button execution remains disabled.
- No build, publish, review-request, approval, rollback, mutation, custom component execution, or custom operation dispatch route is enabled by this slice.
- Push is not performed.

## Verification

- `php -l tests/Integration/NoCodeReviewWorkflowRepositorySqliteTest.php`
- Focused PHPUnit review workflow repository in-review duplicate reuse: `OK (15 tests, 159 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 376, Assertions: 11615, Skipped: 1.`
- `git diff --check`
