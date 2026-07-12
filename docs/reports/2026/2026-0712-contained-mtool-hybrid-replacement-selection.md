# Contained Mtool Hybrid Replacement Selection

## Status

`DONE`

## Decision

Select the existing MTOOL Source Output inspection workflow as the first contained hybrid replacement target.

This is a selection decision only. It does not start a broad Mtool rewrite and does not enable mutation.

## Why this workflow

The Source Output inspection surface already has the strongest safety evidence for a first Mtool self-use slice:

- It is MTOOL-only.
- It is admin-only.
- It is default-off through `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED`.
- It is GET-only and read-only.
- It reads live Source Output repository rows rather than fixture-only data.
- It renders through the shared no-code runtime contract.
- It stays parallel to the canonical Source Outputs page.
- It has browser evidence for zero generated execution controls and zero inspection POST requests.
- Rollback is operationally simple: unset the feature flag and keep the canonical admin page.

This matches the post-sample product direction: partial, contained, hybrid replacement instead of full Mtool no-code conversion.

## Ownership split

Generated/no-code owns:

- read-only list/detail screen model;
- normalized field visibility and labels;
- runtime rendering for inspection rows;
- selected-row/detail handoff;
- disabled action visibility where metadata is shown;
- generated JSON/HTML markers used by contract tests.

Custom Mtool owns:

- admin shell and routing;
- authentication and project authorization;
- real Source Output repository access;
- feature flag and rollback;
- canonical Source Output CRUD/build/publish pages;
- error mapping and operational policy;
- any future mutation, audit, CSRF, idempotency, and Transaction Full boundaries.

Shared/integration owns:

- a declared row adapter for the subset of Source Output fields exposed to no-code;
- exact selector behavior for `source_output_key`;
- canonical return path to `/projects/MTOOL/source-outputs`;
- test evidence that no generated POST/action execution path exists.

## Explicitly not selected

The first contained Mtool hybrid workflow is not:

- Source Output create/edit/delete/reorder;
- build, publish, approval, or rollback UI;
- generic no-code rendering for every Mtool project;
- schema proposal review;
- AI material-to-UI generation;
- a replacement of the canonical Source Outputs page.

## Next lane

#796 should preflight the first product slice for this selected workflow.

The preflight should decide whether the next slice is:

1. a stronger productized inspection entry point around the existing default-off route;
2. a generated/custom contract improvement for the inspection surface;
3. or a small promotion/rollback/test hardening step before any user-facing exposure.

It should not add mutation unless a separate authority/CSRF/audit/Transaction Full plan is explicitly created.
