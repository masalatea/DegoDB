# Post-G-L5 Roadmap Consistency Selection

Date: 2026-07-12

## Decision

The user's "continue" is treated as local-only continuation, not as permission to push or open a PR.

The next safe lane is roadmap consistency cleanup.

## Reason

The active plan already says the branch should be held locally until explicit push/PR direction. Since push/PR was not explicitly requested, the next useful local step is to remove stale roadmap wording that still implies N6/G-L5 is active even though Sample19 now satisfies the current G-L5 feasibility evidence target.

## Scope for #832

Update `docs/current-plans.md` so that:

- N6 is no longer `ACTIVE_NEXT`.
- G-L5 feasibility completion and product rollout parking are consistently described.
- The plan does not imply automatic continuation into broad productization.
- Push/PR remains held until explicit user direction.

No code or route changes are in scope.
