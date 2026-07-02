# 2026-0701 Approval Route/Test Implementation Plan First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Defined the implementation plan for no-code publish candidate persistence and first approval transition routes without adding code.

This closes the planning gap between the docs-only candidate schema / approval UI contracts and the eventual code slice. The next code work should start from candidate persistence, not approval mutation.

## Implementation Order

1. Candidate revision repository/helper.
2. Candidate creation route.
3. Candidate list/read route surface inside Source Outputs inspection.
4. Approval transition helper tests.
5. Approval transition routes/actions.

Approval actions should not be implemented before candidate create/list/read is stored and tested.

## Route Plan

Candidate revision routes:

- `POST /mtool/projects/{project_key}/source-outputs/{source_output_key}/no-code/publish-candidates`
  - Creates a candidate revision from the current `publish_readiness` snapshot.
  - Required inputs: project key, source output key, expected artifact key, expected readiness state, CSRF token.
  - Fail-closed cases: missing readiness, artifact mismatch, source output is not `NO-CODE-RUNTIME`, missing CSRF, non-operator principal.

- `GET /mtool/projects/{project_key}/source-outputs/{source_output_key}/no-code/publish-candidates`
  - Lists latest candidate revisions for the source output.

- `GET /mtool/projects/{project_key}/source-outputs/{source_output_key}/no-code/publish-candidates/{revision_id}`
  - Reads one candidate revision within the project/source-output boundary.

Approval transition routes:

- `POST /mtool/projects/{project_key}/source-outputs/{source_output_key}/no-code/publish-candidates/{revision_id}/transitions`
  - Applies `request_review`, `approve`, `reject`, or `supersede`.
  - Required inputs: expected current status, transition, CSRF token, reason when required.
  - Fail-closed cases: status mismatch, invalid transition, missing reason for reject/supersede, newer candidate exists, non-operator principal.

## Repository Boundary

Candidate repository:

- create candidate from readiness snapshot;
- store blocked candidates as first-class candidate records;
- list latest candidates by project/source output;
- read by revision id within project/source output;
- never copy artifacts or expose public URLs.

Transition helper/repository:

- compute allowed transitions and blocked reasons;
- append transition event;
- update current candidate status only after event append succeeds;
- never publish, roll back, or package artifacts.

## Test Plan

Focused tests before route wiring:

- create publishable candidate from readiness snapshot;
- create blocked candidate from blocked readiness snapshot;
- reject candidate creation on artifact/readiness/source-output mismatch;
- list/read candidates within project/source-output boundary;
- allow `draft_candidate -> review_requested`;
- allow `review_requested -> approved`;
- allow `review_requested -> rejected` only with reason;
- allow `draft_candidate -> superseded` only when a newer candidate exists;
- reject status mismatch and invalid transitions.

Route/source-contract tests:

- route requires operator/admin permission;
- route requires CSRF for POST;
- disabled UI reasons match transition helper reasons;
- Source Outputs no-code inspection links to candidate list/detail;
- no public URL or packaging controls appear in this first route slice.

## Verification Gate

Before code implementation:

- Close Docker-backed verification gaps with `make sample28-no-code-schema-form-runtime-smoke`.
- Run focused repository/transition tests for the new helpers.
- Run route/source-contract tests if routes are added.
- Run `make test` before local commit.

If Docker remains unavailable and the user explicitly accepts the gap, record that acceptance in the implementation report before adding code.
