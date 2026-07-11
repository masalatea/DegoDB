# Post Review Route Guard Replan

Date: 2026-07-08

Status: `DONE`

## Summary

#473 replans after the `review_source_output_artifact` route guard lane and local stack review.

The route guard lane is closed, but execution remains disabled by metadata availability and no review workflow mutation exists.

## Options Considered

- Keep execution parked and stop feature work until a push decision.
- Prepare push decision immediately.
- Improve disabled UI explanation around route guard readiness.
- Promote review workflow persistence inventory before any availability enablement.
- Promote `request_source_output_publish` route work.

## Decision

Promote review workflow persistence inventory next.

Reasons:

- The route guard can now block, render, and audit, but `availability: available` would be unsafe without a persistence target.
- Review workflow creation needs its own storage, idempotency, stale artifact, audit, and rollback boundary before any mutation.
- `request_source_output_publish` should remain deferred until review workflow persistence is understood.
- Disabled UI explanation is useful but less important than defining the mutation boundary that would make availability meaningful.
- Push remains explicitly not requested.

## Next Scope

#474 should be docs/inventory first:

- Define the review workflow record shape.
- Define idempotency for repeated artifact review requests.
- Define stale artifact behavior.
- Define audit result mapping after persistence exists.
- Define how `availability` would eventually move from `deferred` to `available`.
- Keep generated button execution disabled and avoid mutation implementation.

## Boundary

- No new route implementation.
- No review workflow persistence.
- No generated button enablement.
- No publish request route.
- No Source Output mutation.

## Verification

#473 is docs-only.

- `git diff --check`
