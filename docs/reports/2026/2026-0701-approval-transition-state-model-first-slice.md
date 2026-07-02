# 2026-0701 Approval Transition State Model First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Defined the first approval transition state model for no-code publish candidate revisions as a docs-only slice.

The model keeps candidate persistence, approval actions, public runtime exposure, and rollback as separate steps. A candidate revision can be created and inspected before approval exists; approval transitions later move that stored candidate through explicit states.

## State Model

Initial candidate states:

- `draft_candidate`: readiness snapshot was publishable when captured.
- `blocked_candidate`: readiness snapshot was blocked when captured, but the record is still stored for audit/debug.

Review states:

- `review_requested`: a draft candidate has been submitted for approval review.
- `approved`: candidate is approved as the selected publish revision.
- `rejected`: candidate is rejected with a reason.
- `superseded`: a newer candidate replaces this candidate for the same project/source output.

Reserved later states:

- `published`: approved candidate is exposed through a public/runtime delivery mechanism.
- `rolled_back`: a later published candidate is rolled back to a prior approved/published revision.

## Transition Rules

Allowed first transitions:

- `draft_candidate` -> `review_requested`
- `review_requested` -> `approved`
- `review_requested` -> `rejected`
- `draft_candidate` -> `superseded`
- `blocked_candidate` -> `superseded`

Blocked first transitions:

- `blocked_candidate` -> `review_requested`
- `blocked_candidate` -> `approved`
- any state -> `published` until public runtime URL / packaging is separately designed
- any state -> `rolled_back` until published revision history exists

## Transition Record Contract

Future implementation should store transition events separately from the candidate snapshot:

- `transition_id`
- `revision_id`
- `project_key`
- `from_status`
- `to_status`
- `transition_reason`
- `created_at`
- `created_by`
- `metadata_json`

The candidate record may denormalize current status for listing, but transition history should be append-only.

## UI Boundary

First UI behavior should remain operator/admin only:

- show candidate current status and transition history;
- show blocked transition reasons before enabling actions;
- require a reason for reject/supersede;
- do not expose publish/public URL actions in the approval UI slice.

## Verification Boundary

Before code implementation:

- close or explicitly accept Docker-backed verification gaps;
- add focused repository tests for transition allow/deny rules;
- add focused UI/source contract coverage only when action buttons are introduced;
- run `make test` before committing any code slice.
