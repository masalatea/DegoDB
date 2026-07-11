# Sample18 Real Runtime Default Binding Preflight

Date: 2026-07-10
Status: DONE
Plan: #668

## Context

The generated-submit route can now execute successfully and surface route-level failures, but execution currently requires tests to inject `sample18_generated_submit_transaction_callables`.

The next step is to let the sample18 route construct the default generated runtime binding itself, while keeping the explicit executor flag disabled by default.

## Boundary

The default binding should:

- Stay sample18-specific.
- Load the generated runtime support and `TaskCardDBAccess` / `TaskCardData` classes from the sample18 reference output only when route execution is explicitly enabled.
- Construct `MtoolGeneratedDbAccessRuntimeDb`.
- Set the legacy `$mtooldb` global for generated DBAccess compatibility.
- Wrap the runtime DB handle with `app_lab_sample18_task_board_generated_submit_transaction_binding_callables()`.
- Prefer injected `sample18_generated_submit_transaction_callables` when present, so tests and future adapters can still override the default.
- Fail closed with a route-visible dependency failure when required generated runtime classes or runtime DB configuration are unavailable.

The default binding must not:

- Enable execution by default.
- Run for method / CSRF / validation failures.
- Run for duplicate idempotency requests.
- Hide commit/post-commit recovery metadata.
- Introduce a generic cross-sample loader before sample18 proves the path.

## Acceptance

The first slice should prove:

- With executor flag off, route behavior remains the current HTTP 409 blocked response and does not load/construct execution dependencies.
- With executor flag on and no injected callables, the route can construct default sample18 generated runtime dependencies and execute a fresh request.
- Duplicate replay with executor flag on still does not execute.
- Missing runtime configuration or missing generated classes produces a fail-closed dependency error.

## Next

Promote #669 as the first default-binding implementation slice.
