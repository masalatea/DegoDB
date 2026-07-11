# Custom Operation Metadata Adapter Handoff Lane Closure

Date: 2026-07-08

Status: `DONE`

## Summary

#455 closes the custom operation metadata and adapter handoff lane before adding execution.

This lane makes custom operations visible, inspectable, and adapter-ready. It intentionally does not enable build, publish, review-request, approval, mutation, or custom component execution.

## Accepted Capability

- Custom operation manifest inventory defines identity, category, target, side-effect class, policy/auth/CSRF expectations, audit expectations, generated HTML binding expectations, adapter handoff, and non-goals.
- `contract_metadata.custom_operations` is normalized into the no-code screen definition.
- Runtime preview JSON carries custom operations per rendered screen.
- Generated operator action panels expose stable disabled bindings through `data-extension-slot-operation` and `data-extension-slot-operation-key`.
- Generated operator action panels display explicit unavailable reasons through `data-extension-slot-unavailable-reason`.
- Mtool dogfooding inspection reports operation keys, categories, side-effect classes, availability, unavailable reasons, adapter handoffs, per-screen carry-through, and HTML boundary flags.
- React bridge `bridge-contract.json` exposes `custom_operation_handoffs` for adapter consumers.
- Generated React bridge TypeScript exports `MtoolCustomOperationHandoff`.

## Preserved Boundary

- No custom operation execution route is added.
- No build, publish, review-request, approval, or mutation action is wired.
- Generated operator action buttons remain disabled.
- React handoffs are metadata-only and do not grant execution rights.
- No custom React/component execution is added.

## Next Candidates

- Local commit stack review before any push decision.
- Add policy/auth/CSRF/audit route inventory for one custom operation, without implementation.
- Add an explicit adapter-side display surface for React custom operation handoffs.
- Add execution routes only after policy, auth, CSRF, audit, and approval boundaries are explicit.

## Verification

Latest code verification remains #454:

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- Focused PHPUnit: `OK (8 tests, 150 assertions)`
- Focused PHPUnit: `OK (11 tests, 552 assertions)`
- `make sample28-no-code-react-bridge-build-smoke`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11304, Skipped: 1.`

#455 is docs-only. `git diff --check` was run for #455.
