# 2026-0701 Approval Action UI Contract First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Defined the first operator/admin UI contract for future no-code publish candidate approval actions as a docs-only slice.

This does not add buttons or mutations yet. It fixes what the UI should expose once candidate persistence and transition repositories exist.

## Action Surface

Future first action buttons:

- `Request review`: `draft_candidate` -> `review_requested`.
- `Approve`: `review_requested` -> `approved`.
- `Reject`: `review_requested` -> `rejected`.
- `Supersede`: `draft_candidate` / `blocked_candidate` -> `superseded`.

Reserved later actions:

- `Publish`: reserved until public runtime URL / packaging is designed.
- `Rollback`: reserved until published revision history exists.

## Availability Contract

Actions should be shown as disabled with reasons rather than hidden when a candidate exists but the transition is blocked.

Required availability inputs:

- current candidate status;
- readiness snapshot state;
- latest candidate marker for the same project/source output;
- operator/admin permission result;
- CSRF readiness for POST actions;
- required transition reason state for reject/supersede.

Blocked reason examples:

- `candidate is blocked and cannot request review`;
- `candidate is not in review`;
- `newer candidate already supersedes this revision`;
- `transition reason is required`;
- `public publish is not implemented yet`.

## UI Placement

First UI placement should remain inside the operator/admin no-code source-output inspection path:

- show current candidate status;
- show transition history summary;
- show action availability and blocked reasons;
- link back to publish readiness inputs;
- avoid public URLs or packaging controls.

## Request Contract

Future POST actions should require:

- project key;
- source output key;
- revision id;
- expected current status;
- target transition;
- CSRF token;
- transition reason when required.

Handlers should fail closed if the expected current status does not match the stored candidate status.

## Verification Boundary

Before implementation:

- Docker-backed verification gaps must be closed or explicitly accepted.
- Add focused transition allow/deny tests.
- Add focused route/source contract tests for disabled reasons and CSRF.
- Run `make test` before committing any code slice.

## Out Of Scope

- Database migration.
- Candidate persistence implementation.
- Approval mutation implementation.
- Public runtime URL.
- Artifact copying or packaging.
- Push.
