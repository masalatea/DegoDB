# Review Workflow Persistence Repository First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#475 adds repository-first persistence for review workflow requests without enabling `review_source_output_artifact` route mutation.

The slice adds the config DB table and a PDO-backed repository that can create a review request or reuse an existing open request for the same project/source-output/artifact/operation.

Generated buttons remain disabled, custom operation availability remains deferred, and no approval transition, publish route, generated runtime execution, or route mutation is added.

## Added Persistence Surface

- `no_code_review_requests` config DB table.
- `app_no_code_review_workflow_create_or_reuse_request()`.
- `app_no_code_review_workflow_fetch_latest_requests()`.
- Open status reuse for `requested` and `in_review`.
- Stored request shape includes `review_request_key`, `project_key`, `source_output_key`, `artifact_key`, `operation_key`, `adapter_handoff`, `status`, `requested_by`, `requested_at`, `source_output_dir`, `policy_key`, `audit_event`, and `metadata_json`.

## Idempotency Boundary

The repository reuses an existing open request when the project, source output, artifact, and operation match.

Repository results:

- New row: `accepted`.
- Existing open row reused: `duplicate`.
- Repository failure: `failed`.

This does not yet define the final HTTP route response contract.

## Out Of Scope

- Making `review_source_output_artifact` available.
- Calling the repository from the POST route.
- Generated button execution.
- Stale-artifact route request handling beyond the stored metadata shape.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.

## Verification

- `php -l mtool/app/no_code_review_workflow_repository.php`
- `php -l mtool/app/no_code_review_workflow_repository_pdo.php`
- `php -l mtool/app/config_db_bootstrap.php`
- `php -l tests/Integration/NoCodeReviewWorkflowRepositorySqliteTest.php`
- Focused PHPUnit review workflow repository: `OK (2 tests, 23 assertions)`
- Focused PHPUnit config DB bootstrap: `OK (1 test, 6 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 357, Assertions: 11429, Skipped: 1.`
- `git diff --check`

## Next Candidate

#476 should decide how the existing route guard will call repository persistence while preserving the current execution boundary: generated buttons stay disabled and availability remains deferred until route-level persistence, stale-artifact, and audit behavior are covered together.
