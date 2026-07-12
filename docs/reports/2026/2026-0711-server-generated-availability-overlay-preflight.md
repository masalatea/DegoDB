# 2026-0711 Server-Generated Availability Overlay Preflight

Status: `DONE`

## Current State

Sample18 readiness metadata reaches screen-definition actions, runtime-preview JSON, and stable HTML markers. A generic action-policy overlay function can replace action availability, but current enabled-candidate coverage constructs that policy manually in tests. There is no server-generated policy derived from readiness metadata.

## First Implementation Boundary

Add a side-effect-free server policy builder that may promote an action from `disabled` to `enabled` candidate presentation only when every gate passes:

- explicit overlay feature flag is enabled; default is off;
- the existing authorization policy is evaluated and allowed;
- `route_compatible=true`;
- `readiness_state=candidate_ready`;
- `availability_candidate=true`;
- `failure_reasons=[]`;
- submit route and guarded click binding are present;
- Transaction Full capability gate is satisfied;
- the action is one of the declared generated-submit route contracts.

Any missing, malformed, or conflicting value remains `disabled` and emits stable failed-check reasons.

## Safety Boundary

This overlay does not enable real mutation.

- `submit_binding_gate.mutation_enabled` remains `false`;
- `readiness_metadata.can_submit` remains `false` while executor config is disabled;
- the existing guarded route may still return the explicit disabled result;
- no readiness metadata may override failed authorization;
- no client-side script may manufacture the server overlay decision;
- cross-store atomicity is not implied by candidate presentation.

The overlay changes server-generated availability presentation and explanation only. Real guarded execution remains a separate opt-in lane.

## Proposed Contract

The builder should return a policy action for every managed action:

- `action_key`;
- `availability`: `enabled` or `disabled`;
- `policy.failed_checks`;
- `overlay_source=server_readiness_v1`;
- `overlay_flag_enabled`;
- `transaction_full_gate`;
- copied readiness summary for diagnostics.

The existing `app_no_code_runtime_definition_with_action_policy_overlay()` remains the application mechanism. The new builder is the trusted source of the policy definition.

## Verification Plan

1. Unit matrix: flag off, auth denied, candidate ready, route missing, runtime failure, malformed readiness, and Transaction Full gate missing.
2. Sample18 JSON contract: only route-compatible actions become enabled candidates with the flag on.
3. DOM contract: availability and enabled markers match the server policy; readiness markers remain unchanged.
4. Browser smoke: remove manual policy mutation and consume the server-generated overlay.
5. Assert the generated-submit mutation flag remains disabled throughout this lane.

## Next Slice

Implement the policy builder and fast JSON/DOM coverage. Do not add real execution smoke in the same commit.
