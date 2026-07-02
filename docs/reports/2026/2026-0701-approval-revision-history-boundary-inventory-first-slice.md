# 2026-0701 Approval / Revision History Boundary Inventory First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

The next mutation-capable no-code product surface should start with an approval/revision boundary around a selected generated runtime artifact, not with public publishing or packaging.

The smallest next implementation slice should be **publish candidate revision record planning**: define the metadata needed to record which generated `NO-CODE-RUNTIME` artifact is being proposed, who proposed it, what readiness state it had, and which blockers existed at proposal time.

## Candidate Comparison

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Publish candidate revision record | 1 - 2 days | Selected as the next implementation boundary. It is the smallest durable mutation before approval actions or public URLs. |
| Approval action buttons | 1 - 3 days | Deferred. Actions need a revision object and state model first. |
| Public runtime URL | 2 - 5 days | Deferred. Public exposure should follow explicit revision/approval state. |
| Artifact copying / packaging | 2 - 5 days | Deferred. Packaging should follow revision selection and approval semantics. |
| Publish-readiness detail surface | 0.5 - 1.5 days | Deferred. Useful UI polish, but the product path needs a durable boundary first. |

## Proposed First Implementation Boundary

In scope:

- A read/write model for a publish candidate revision tied to project key, source output key, artifact key, and readiness snapshot.
- No public runtime URL.
- No artifact copying.
- No approval/rollback state machine yet.
- Operator/admin surface may show a create-candidate affordance only if the candidate can be persisted safely.

Out of scope:

- Approval mutation.
- Revision rollback.
- Public publish.
- Packaging.
- Transport or sync behavior.
- Push.

## Recommended Data Shape

Minimum fields:

- `project_key`
- `source_output_key`
- `artifact_key`
- `artifact_archive_exists`
- `preview_files_ready`
- `screen_count`
- `action_count`
- `blocking_reasons_json`
- `created_by`
- `created_at`
- `status`

Initial statuses:

- `draft_candidate`
- `blocked_snapshot`

The first implementation should stay fail-closed: if the artifact is not publishable, it may create only a blocked snapshot, not an approval-ready candidate.

## Verification

Docs-only inventory. Docker-backed verification remains blocked and must be rerun before code changes for this lane.
