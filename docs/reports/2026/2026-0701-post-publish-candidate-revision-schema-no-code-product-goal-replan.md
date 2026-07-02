# 2026-0701 Post-Publish-Candidate-Revision Schema No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Approval transition planning** as the next docs-only slice.

Docker-backed verification remains unavailable, so this turn should not add the minimal candidate persistence write path yet. The useful continuation is to define the approval transition model that will sit on top of stored candidate revisions once persistence is implemented and verified.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Docker-backed verification rerun | 0.25 - 0.5 day | Attempted and still blocked: Docker daemon unavailable. |
| Minimal candidate persistence | 1 - 2 days | Deferred. It adds a new write path and should wait for Docker-backed verification. |
| Approval transition planning | 0.5 - 1 day | Selected. It reduces ambiguity without adding code or mutation behavior. |
| Public runtime URL / packaging | Replan after candidate persistence and approval transitions | Deferred. Public exposure needs candidate and approval state first. |

## Boundary

In scope:

- Choose the next docs-only step after the candidate revision schema contract.
- Keep the eventual first implementation centered on stored candidate revisions.
- Define approval transition boundaries before approve/reject code exists.

Out of scope:

- Database migration.
- Repository implementation.
- Mutation UI.
- Public runtime URL.
- Artifact copying or packaging.
- Push.

## Verification

- `docker info` attempted and blocked by unavailable Docker daemon.
- Docs-only decision; no code verification required.
