# Review Workflow Persistence Inventory

Date: 2026-07-08

Status: `DONE`

## Summary

#474 defines the persistence boundary needed before `review_source_output_artifact` can move from route guard infrastructure to executable review workflow behavior.

No persistence implementation, availability enablement, generated button execution, mutation, publish request route, or approval transition is added.

## Proposed Record Shape

Review workflow persistence should be a project-scoped record with at least:

- `review_request_key`
- `project_key`
- `source_output_key`
- `artifact_key`
- `operation_key`
- `adapter_handoff`
- `status`
- `requested_by`
- `requested_at`
- `source_output_dir`
- `policy_key`
- `audit_event`
- `metadata_json`

Recommended statuses:

- `requested`
- `in_review`
- `accepted`
- `rejected`
- `cancelled`
- `superseded`

## Idempotency Boundary

Repeated `review_source_output_artifact` requests for the same project/source-output/artifact should reuse an existing open review request when one exists.

Open statuses:

- `requested`
- `in_review`

Closed statuses:

- `accepted`
- `rejected`
- `cancelled`
- `superseded`

The route should return an `accepted` or `duplicate` audit result depending on whether a new record was created or an existing open record was reused.

## Stale Artifact Boundary

The route must require an expected artifact key from the request.

- Missing current artifact: block with `missing_artifact`.
- Request artifact differs from current artifact: block with `stale_artifact`.
- Current artifact matches expected artifact: allow the persistence preflight to continue.
- A newer artifact must never be silently selected for review.

## Audit Boundary

Persisted review workflow requests should use:

- Event type: `mtool.source_output.artifact_review_requested`
- Target type: `source_output_artifact`
- Target key: `{project_key}:{source_output_key}:{artifact_key}`
- Results:
  - `accepted`
  - `blocked`
  - `unauthorized`
  - `invalid`
  - `stale`
  - `duplicate`

Minimum metadata:

- `operation_key`
- `project_key`
- `source_output_key`
- `artifact_key`
- `review_request_key`
- `adapter_handoff`
- `policy_key`
- `failure_code`

## Availability Boundary

`availability` should remain `deferred` until all of the following exist:

- persistence schema/repository
- idempotency tests
- stale artifact tests
- audit append success/failure coverage
- route wrapper tests against real persistence behavior
- disabled/generated UI behavior reviewed separately

Changing metadata to `availability: available` should be a separate commit after this persistence boundary is implemented and tested.

## Out Of Scope

- Creating the persistence table/repository.
- Enabling generated buttons.
- Creating a review UI.
- Approval transition mutation.
- Publish request route.
- Review acceptance/rejection workflow.
- Cross-system adapter execution.

## Verification

#474 is docs-only.

- `git diff --check`

## Next Candidate

Either add a repository-first review workflow persistence slice, or pause for a push decision before introducing mutation storage.
