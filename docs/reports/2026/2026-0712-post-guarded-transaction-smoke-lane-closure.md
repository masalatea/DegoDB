# Post Guarded Transaction Smoke Lane Closure

## Decision

The guarded execution evidence lane is complete. Broad or default generated UI enablement remains parked. A narrow, explicit-flag, single-operation UI enablement preflight may advance next.

## Evidence available

- generated DBAccess caller-owned transactions work for PDO and mysqli-compatible runtime paths;
- Sample14 proves multiple required same-database updates commit together or roll back together;
- Sample18 proves an authenticated real HTTP generated-submit mutation commits through MariaDB;
- a failure returned after real SQL causes the Sample18 application transaction to roll back and leave zero rows;
- availability is server-generated, authenticated, selector-bound, fail-closed, and diagnostics-only;
- browser diagnostics never issue POST or alter control readiness;
- config DB post-commit persistence remains an explicit recovery domain rather than false distributed atomicity.

## What is still not authorized

- availability GET is not execution authority;
- no generated action becomes enabled by default;
- no current/alias selector response may bypass click-time auth, CSRF, artifact, input, idempotency, executor config, or transaction checks;
- reopen/delete and non-route-compatible actions remain excluded;
- broad sample conversion remains excluded.

## Next candidate

Preflight one Sample18 `create_task_card` opt-in UI path:

- current or alias authenticated preview only;
- explicit server availability flag, Transaction Full gate, mutation flag, executor flag, and a separate UI enablement flag;
- immutable artifact identity match before presentation;
- click POST still targets the existing guarded route and re-evaluates every server guard;
- disabled/unavailable/stale state fails closed;
- real success, rollback, duplicate, auth, CSRF, and recovery-visible coverage required before implementation is considered complete.

This is a planning promotion only. Current generated defaults stay unchanged.

