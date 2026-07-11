# Post-Availability Sample UI Replan

English companion:
This report records #543 and folds in the lightweight candidate comparison needed to enter L1 sample UI no-code conversion.

## Summary

- Status: `DONE`
- Date: 2026-07-09
- Decision: use `sample18-mini-task-board-demo` as the first existing sample UI conversion target.
- Push: not performed.

## Candidate Comparison

| Candidate | Existing UI shape | Why it matters | Decision |
| --- | --- | --- | --- |
| `sample18-mini-task-board-demo` | Hand-coded task board HTML page plus list/create/edit/complete HTTP smoke | Small one-table app with real user-facing UI and enough workflow surface to expose no-code gaps | `FIRST_TARGET` |
| `sample07-dbaccess-crud-basic` | Metadata-first DB access CRUD sample with existing `NO-CODE-RUNTIME` output | Good minimal no-code contract reference, but not primarily an existing hand-coded UI conversion | `REFERENCE` |
| `sample28-no-code-data-app-mvp` | Mature no-code runtime, React bridge, JSON Forms comparison, browser smoke | Strong reference for expected no-code artifact quality, but already no-code-first | `REFERENCE` |
| `sample29-no-code-support-case-demo` | Second no-code data-first domain | Useful domain variation after the first conversion path is stable | `LATER_REFERENCE` |
| `sample31-no-code-inventory-request-demo` | No-code data app with public submit/outbox path | Too advanced for the first existing UI conversion because sync/outbox behavior would widen the slice | `LATER_REFERENCE` |

## First Target

`sample18-mini-task-board-demo` is the first existing sample UI conversion target.

Reasons:

- It starts from an instruction-style user request and already has a user-facing HTML page.
- It has a small domain: `task_card` with title, description, state, assignee, due date, and priority.
- It includes representative actions: create, edit, and complete.
- It already has `make sample18-pack-runtime-test` and `make sample18-http-runtime-smoke`, so no-code output can be compared against a known working sample.
- It is simpler than public runtime submit/outbox samples and more representative than already-no-code samples.

## Required Gaps Before Conversion

- Add fast no-code JSON/DOM contract harness coverage before relying on browser smoke as the inner loop.
- Freeze a golden fixture for the existing sample18 task board route, including stable data and expected DOM markers.
- Extract readonly no-code metadata for task list/detail/form without replacing the current hand-coded page.
- Model create/edit/complete as disabled or dry-run custom operations with route boundary metadata before enabling mutation.
- Keep generated button execution disabled until a later explicit lane.

## Next Plan

- #545 becomes `ACTIVE_NEXT`: define the sample18 no-code capability checklist and fast-test acceptance criteria.
- #552 remains the first code slice for the fast PHPUnit JSON/DOM harness after the checklist names the contract shape.
