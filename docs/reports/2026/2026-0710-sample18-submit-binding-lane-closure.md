# Sample18 Submit Binding Lane Closure

Plan item: #571 sample18 submit binding lane closure

Status: DONE

## Summary

Closed the sample18 submit binding gate lane and promoted CSRF guard preflight as the next required increment before disabled click intent or mutation dispatcher work.

## Accepted capability

- #569 proves the generated submit route returns blocked and validation JSON through the authenticated HTTP stack.
- #570 exposes `submit_binding_gate` metadata and stable runtime DOM markers for binding state, CSRF source, and fail-closed result.
- Generated buttons remain disabled and runtime clicks are not bound to the submit route.

## Decision

#572 should add fail-closed CSRF handling and HTTP smoke coverage for `/samples/sample18-task-board/no-code/generated-submit`.

This should happen before disabled click intent work because the binding gate now names a CSRF source, but the route-level missing/invalid CSRF behavior still needs to be fixed as a contract. Mutation dispatcher work remains parked until the route guard behavior is explicit.

## Verification

- `git diff --check`

## Next

#572 should cover valid CSRF, missing CSRF, and invalid CSRF outcomes for the generated submit route while keeping mutation disabled.
