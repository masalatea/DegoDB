# 2026-0701 Publish Candidate Revision Record Replan

Status: `DONE`.

## Decision

Selected **Revision record schema/docs only** as the next slice.

Docker-backed verification remains unavailable, so this turn should not add another mutation-capable code path. The useful continuation is to define the durable publish candidate revision contract first, then implement storage/UI once Docker-backed verification can be rerun.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Docker-backed verification rerun | 0.25 - 0.5 day | Attempted and still blocked: Docker daemon unavailable. |
| Revision record schema/docs only | 0.5 - 1 day | Selected. It reduces implementation ambiguity without adding unverified mutation code. |
| Minimal publish candidate persistence | 1 - 2 days | Deferred. Requires Docker-backed verification before adding a new write path. |
| Approval action planning | Replan after candidate record | Deferred. Approval transitions need the candidate revision object first. |

## Boundary

In scope:

- Define the minimal publish candidate revision record contract.
- Define required identity fields and readiness snapshot fields.
- Define initial lifecycle states.
- Define repository / UI responsibilities for a later implementation slice.

Out of scope:

- Database migration.
- Repository implementation.
- Mutation UI.
- Approval / reject / rollback actions.
- Public runtime URL.
- Artifact copying or packaging.
- Push.

## Verification

- `make sample28-no-code-schema-form-runtime-smoke` attempted and blocked by unavailable Docker daemon.
- Docs-only decision; no code verification required.
