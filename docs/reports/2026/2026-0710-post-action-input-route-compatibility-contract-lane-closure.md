# Post Action/Input Route Compatibility Contract Lane Closure

Status: `DONE`

Plan: #688 post action/input route compatibility contract lane closure

## Summary

#688 accepts #687 as the first fast route compatibility assertion slice for sample18 generated action/input metadata and generated DOM attributes.

The lane is ready to move one level closer to runtime handoff, but should still avoid broad headless browser coverage as the immediate next step. The next slice should stay fast and prove payload assembly semantics before browser smoke becomes the outer representative check.

## Accepted From #687

- Route-compatible generated-submit operations are fixed to `create_task_card`, `update_task_card`, and `complete_task_card`.
- `reopen_task_card` and `delete_task_card` remain disabled metadata-only candidates.
- Generated inventory and screen-definition action fields now match route key/required/optional/server-owned boundaries.
- Generated runtime HTML exposes route-compatible action keys, operation keys, submit URL, CSRF handoff, and blocked binding attributes.
- Mutation and route replacement remain disabled.

## Decision

Promote guarded-submit payload handoff hardening before browser smoke.

Reason: the user-facing risk now sits between generated action intent/input collection and the route POST payload. A fast non-browser contract can lock operation key, selected key handoff, field payload, CSRF token field, submit URL, and fail-closed disabled semantics without paying the headless browser cost on every slice.

## Next

Promote #689: sample18 guarded submit payload handoff fast contract first slice.

That slice should add focused assertions for generated guarded-submit payload assembly before broader browser smoke or availability expansion.
