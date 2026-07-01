# Next No-Code Product Goal After Runtime Polish

Status: `DONE`

Date: 2026-06-30

## Scope

Completed the docs-only replan after generated runtime UX/state polish.

The goal was to choose the next product-facing no-code implementation after the generated runtime became more presentable through readable labels, empty states, action feedback, and deterministic runtime/screen/action state markers.

## Decision

Selected `Data-first no-code domain sample 2 first slice` as the next active implementation item.

The current no-code path has one user-facing MVP sample (`sample28-no-code-data-app-mvp`) and a polished generated runtime preview. The next useful product proof is to run the same no-code path against a slightly richer domain shape before expanding into app-local sync, operator/admin workflow, or native targets.

## Candidates Considered

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Data-first no-code domain sample 2 | 2 - 5 days | Selected. It exercises the current runtime and metadata path with more realistic product pressure while staying inside a sample boundary. |
| App-local sync demonstration | 2 - 5 days | Deferred. Strong product story, but it should follow one more generated Web/runtime domain proof. |
| Operator/admin no-code workflow | 1 - 3 days | Deferred. Useful later, but the operator surface needs clearer scope. |
| Additional runtime polish slice | 0.5 - 2 days | Deferred. Runtime polish should now be driven by gaps found in a richer sample. |

## First Slice Boundary

In scope:

- One second data-first no-code sample.
- A slightly richer domain/read-model pressure than sample28.
- Existing shared contract / managed operation / `NO-CODE-RUNTIME` metadata model.
- Existing generated Web / HTML runtime preview.
- Pack/runtime smoke and browser UI smoke profile for the new sample.

Out of scope:

- New visual builder.
- New metadata tables.
- Broad relation engine.
- App-local sync product demo.
- Operator/admin publishing workflow.
- Native / Flutter output targets.

## Verification Contract

Expected focused verification:

- new sample pack/runtime test.
- new no-code runtime UI smoke profile or target.
- focused PHPUnit for sample metadata/runtime artifact generation.

Run `make test` if implementation touches shared generator/runtime behavior.

## Plan Promotion

`docs/current-plans.md` now lists `Data-first no-code domain sample 2 first slice` as `ACTIVE_NEXT`.
