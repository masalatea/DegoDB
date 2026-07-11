# Sample18 Post Execution Plan Helper Lane Closure

Date: 2026-07-10
Plan: #647
Status: DONE

## Summary

#647 closes the route-unwired sample18 execution plan helper lane.

#646 is accepted as the current route-unwired composition boundary before any real generated-submit route execution is enabled.

## Accepted Capability From #646

- `app_lab_sample18_task_board_generated_submit_route_execution_plan` composes guard, coordinator, transaction adapter, DBAccess adapter, post-commit recording adapter, and response metadata.
- Feature flag disabled blocks before fake transaction / DBAccess / recording.
- Transaction success plus recording success returns executed success.
- DBAccess failure rolls back and returns failure.
- Post-commit recording failure returns failure with `recovery_required=true`.
- No real route execution is wired.

## Decision

Promote real DBAccess invocation adapter preflight next.

Reason:

- The route-unwired composition is now covered with fake callables.
- Route wiring would be premature until the real `TaskCardDBAccess` invocation boundary is pinned down.
- The next risk is constructing the actual DBAccess input object, normalizing real results, and keeping transaction ownership explicit.

## Next

#648 should define the real DBAccess invocation adapter boundary.

Required boundaries:

- no generated-submit route wiring yet;
- no default-on executor behavior;
- real invocation must be callable only inside an app DB transaction boundary;
- `TaskCardData` / `TaskCardObj` construction must be explicit and allowlisted;
- results must normalize to the existing adapter result shape;
- exceptions must normalize to `dbaccess_exception`;
- focused tests should use an isolated sample18 DB or a fake DBAccess object before route wiring.

## Verification

- `git diff --check`
