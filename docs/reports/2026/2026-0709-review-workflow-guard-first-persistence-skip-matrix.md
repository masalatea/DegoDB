# Review Workflow Guard-First Persistence Skip Matrix

Date: 2026-07-09

Status: `FIRST_SLICE_DONE`

## Summary

#489 adds focused coverage for the route-local persistence helper's guard-first skip matrix.

Non-allowed guard results must never create review workflow persistence records.

## Covered Cases

- `stale_artifact`
- `policy_denied`
- `missing_csrf`
- `unknown_operation`

Each case verifies:

- review request persistence status is `skipped`,
- audit result / failure metadata is preserved,
- no review request rows are created.

## Out Of Scope

- Push.
- Changing `review_source_output_artifact` availability to `available`.
- Generated button execution.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.

## Verification

- `php -l tests/Integration/ProjectSourceOutputOperationPersistenceTest.php`
- Focused PHPUnit guard-first persistence skip matrix: `OK (6 tests, 50 assertions)`
- Full `make test`: attempted twice, then retried outside the sandbox; each run stalled while Docker was loading metadata for `docker.io/library/ubuntu:24.04` and was interrupted.
- `git diff --check`

## Next Candidate

#490 should close the guard-first skip matrix slice and decide whether to pause or continue non-executable hardening.
