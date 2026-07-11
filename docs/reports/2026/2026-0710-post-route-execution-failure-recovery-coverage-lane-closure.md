# Post Route Execution Failure/Recovery Coverage Lane Closure

Date: 2026-07-10
Status: DONE
Plan: #667

## Accepted

#666 is accepted as route-level failure/recovery coverage for the explicit executor path.

Accepted capability:

- Missing executor transaction callables fail before execution with a clear route-level failure code.
- DBAccess failure after transaction begin rolls back and skips post-commit recording.
- Post-commit idempotency failure after commit surfaces recovery metadata and does not get reported as success.
- The route-level all-success-or-failure policy now has success and failure coverage.

## Decision

Promote real sample runtime default binding preflight next.

Reasoning:

- The route currently executes only when tests inject transaction callables.
- The next product-shaped step is to define how sample18 can construct its own generated runtime DB handle and `TaskCardDBAccess` binding in the route boundary.
- UI success/error rendering should wait until the runtime binding is no longer test-only.
- Additional commit-unknown coverage remains useful, but the default binding boundary should come first so future failure coverage exercises the real path.

## Next

Promote #668: sample18 real runtime default binding preflight.
