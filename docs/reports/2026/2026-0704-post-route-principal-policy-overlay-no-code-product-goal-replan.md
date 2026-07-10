# Post-Route Principal Policy Overlay No-Code Product Goal Replan

Status: `DONE`

Date: 2026-07-04

## Decision

After route-level principal action policy overlay landed, the next smallest product-facing slice is a sample28 successful endpoint tryout.

This comes before UI result refresh, direct business-row mutation, or another sample because the public runtime path already has auth, CSRF, current/alias addressing, request normalization, principal policy overlay, and managed-operation outbox dispatch. The missing proof is that a normal local tryout principal can submit the generated action and receive an accepted server response.

## Scope

- Keep generated immutable artifact previews public and non-session-specific.
- Keep current/alias execution endpoints authenticated.
- Allow the sample28 local stub admin to execute the sample tryout action.
- Treat successful runtime execution as managed-operation sync intent enqueue, not direct row mutation.
- Verify both current and alias endpoint POSTs.

## Not In This Slice

- UI refresh from the outbox result.
- Direct mutation of the sample business table.
- Outbox processing into server DBAccess.
- Broader role model redesign.

## Next Work Unit

#127 Sample28 runtime execution success smoke.

