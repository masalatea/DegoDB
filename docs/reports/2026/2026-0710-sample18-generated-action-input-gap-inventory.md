# Sample18 Generated Action/Input Gap Inventory

Status: `DONE`

Plan: #686 sample18 generated action/input gap inventory

## Summary

#686 inventories the remaining gap between sample18 generated no-code managed-action metadata/input drafts and the executable generated-submit route before adding broader browser smoke or enabling availability.

The key finding is that the route is now stricter than the generated metadata surface. The executable generated-submit route contracts cover `create_task_card`, `update_task_card`, and `complete_task_card`. Generated metadata also keeps `reopen_task_card` and `delete_task_card` visible as curated-route candidates, but those remain disabled until DBAccess/custom adapter metadata exists.

## Accepted Boundary

- Generated-submit execution keeps the all-success-or-failure policy: user-facing success is valid only after request validation, CSRF, audit append, idempotency, transaction, DBAccess execution, post-commit execution audit append, and idempotency outcome update all succeed.
- The generated UI handoff should be judged against the executable route contract first, not against broader browser behavior.
- `create_task_card`, `update_task_card`, and `complete_task_card` are the first route-compatible operation set.
- `reopen_task_card` and `delete_task_card` remain disabled curated-route candidates.
- Fast PHPUnit JSON/DOM assertions should come before broader browser smoke.

## Inventory

| Area | Current state | Gap / next contract |
| --- | --- | --- |
| Operation set | Route executes create/update/complete. Metadata also names reopen/delete. | Assert create/update/complete as route-compatible; assert reopen/delete stay disabled candidates. |
| Payload shape | Route accepts `operation_key`, flat action fields, and `_csrf_token`. | Assert generated managed actions expose matching operation keys, submit URL, CSRF handoff, and accepted field names. |
| Key fields | Update/complete require `id`; create has no key field. | Assert generated DOM/metadata exposes reliable row identity sources for keyed actions. |
| Required fields | Create/update require `title`; create/update allow different optional fields. | Assert input draft/metadata marks required client fields and does not treat readonly/server-managed fields as client authority. |
| Availability | Buttons are disabled/blocked by default; route execution requires explicit mutation and executor enablement. | Assert disabled/blocked state, unavailable reason, and executor readiness metadata before any browser smoke expansion. |

## Next

Promote #687: sample18 generated action/input route compatibility contract first slice.

That slice should add focused fast assertions comparing generated managed-action metadata and generated DOM attributes against the route-compatible create/update/complete contract while preserving reopen/delete as disabled curated-route candidates.
