# Post Visible Slot Renderer Replan

Date: 2026-07-08

Status: `DONE`

## Summary

#447 chooses the next main lane after the visible custom slot renderer closure and local stack review.

The next lane is custom operation manifest inventory. Generated HTML can now show disabled operator action affordances, but those affordances must not become side-effecting buttons until the manifest contract is explicit.

## Decision

Promote custom operation manifest inventory before any build, publish, review-request, approval, or other Mtool custom operation execution.

## Why This Comes Next

- Visible operator action slots now exist, so the next product gap is the contract behind those actions.
- Directly adding execution would mix UI affordance, policy, routing, audit, and operation semantics too early.
- Mtool dogfooding needs custom operations eventually, but the source of truth should remain no-code metadata / manifests rather than generated HTML hand edits.
- The same manifest should be usable by generated HTML runtime, React bridge, and later adapter surfaces.

## Inventory Scope

The inventory should define:

- operation identity and stable operation keys
- operation category, for example build, publish, review-request, approval, rollback, or navigation handoff
- side-effect class and whether the action is read-only, mutating, queued, or approval-gated
- required policy/auth/CSRF expectations
- generated HTML binding rules for disabled and future enabled states
- React/custom adapter handoff expectations
- audit/event expectations
- failure and unavailable states
- explicit non-goals for the first slice

## Non-Goals

- Do not add execution routes.
- Do not make generated runtime buttons mutate server state.
- Do not add custom React/component execution.
- Do not bypass existing Source Output review, publish candidate, approval, or sync outbox boundaries.

## Next Step

#448 should record the first custom operation manifest inventory. If it becomes code-backed, the first implementation should carry manifest metadata through the screen-definition/runtime JSON path without enabling execution.

## Verification

Docs-only replan. Latest code verification remains #444:

- PHP syntax checks
- Focused PHPUnit: `OK (8 tests, 121 assertions)`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11273, Skipped: 1.`
