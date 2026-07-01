# Next No-Code Product Goal Replan

Status: `DONE`

Date: 2026-06-30

## Scope

Completed the post-sample28 no-code product-goal replan.

The goal was to choose the next product-facing no-code work unit after `sample28-no-code-data-app-mvp`, define its first implementation boundary, define verification expectations, and promote the selected work into `docs/current-plans.md`.

## Decision

Selected `Generated no-code runtime UX polish first slice` as the next active implementation item.

This is the smallest useful product-facing step after sample28 because it improves the generated Web / HTML runtime preview that already exists, uses the current sample07 and sample28 smoke surfaces, and avoids broadening into a new domain, sync story, operator workflow, visual builder, or native app target before the current runtime is easier to read and demonstrate.

## Candidates Considered

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Generated runtime UX polish | 0.5 - 2 days | Selected. Lowest-risk product-facing polish on the existing runtime and smoke coverage. |
| Data-first no-code domain sample 2 | 2 - 5 days | Deferred. Useful after the current generated runtime is more presentable. |
| App-local sync demonstration | 2 - 5 days | Deferred. Product story is strong, but it touches more foundations and should follow a clearer runtime surface. |
| Operator/admin no-code workflow | 1 - 3 days | Deferred. Needs a tighter admin/operator scope before implementation. |

## First Slice Boundary

In scope:

- Generated `runtime-preview.html` / preview data presentation.
- Better generated list/detail/form titles, field labels, button text, and empty-state copy.
- Deterministic empty, error, disabled, and loading/working states where current metadata is enough.
- Simple success / failure action feedback for the existing browser dispatch helper.
- sample07 and sample28 no-code runtime smoke expectation updates.
- Plan/report documentation updates.

Out of scope:

- New visual builder.
- New metadata tables.
- App-local sync expansion.
- New sample domain.
- Native / Flutter output targets.
- Broad generated runtime architecture redesign.

## Verification Contract

Expected focused verification:

- no-code runtime generator/unit coverage for the rendered output changes.
- `make sample07-no-code-runtime-ui-smoke`
- `make sample28-no-code-runtime-ui-smoke`
- `make sample28-pack-runtime-test`

Run `make test` if the implementation touches shared generator/runtime behavior beyond the no-code runtime preview surface.

## Plan Promotion

`docs/current-plans.md` now lists `Generated no-code runtime UX polish first slice` as `ACTIVE_NEXT`.
