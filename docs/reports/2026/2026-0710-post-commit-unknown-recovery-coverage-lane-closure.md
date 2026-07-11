# Post Commit-Unknown Recovery Coverage Lane Closure

Date: 2026-07-10
Status: DONE
Plan: #672

## Accepted

#671 is accepted as route-level commit-unknown recovery coverage.

Accepted capability:

- Commit failure and commit exception return route-level failure responses.
- `transaction_status=commit_failed` is exposed.
- `recovery_required=true` and `recovery_reason=commit_status_unknown` are preserved.
- Post-commit recording is skipped when commit status is unknown.

## Decision

Promote generated-submit UI success/error rendering preflight next.

Reasoning:

- The route-level execution contract now covers success, duplicate non-execution, rollback failure, post-commit recording failure, dependency failure, default runtime binding, and commit-unknown recovery.
- The next product-facing risk is how the no-code generated submit UI presents those route outcomes.
- Production runtime config hardening is still useful, but UI rendering can start from the covered route contract without changing default-off execution.

## Next

Promote #673: sample18 generated-submit UI success/error rendering preflight.
