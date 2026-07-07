# Runtime-data datetime/time native filter smoke plan

Date: 2026-07-07

## Summary

#380 chooses smoke-level coverage for generated datetime/time native filter controls.

The current sample31 contract covers date and numeric fields. The next useful slice is to extend browser smoke coverage for the shared generated sync path so datetime and time metadata are also asserted without changing sample data or endpoint contracts.

## Planned Scope

- Keep sample31 seed data unchanged.
- Keep endpoint contracts unchanged.
- Extend sample31 public runtime browser smoke coverage.
- Probe generated filter-control behavior for:
  - `datetime` field metadata;
  - `time` field metadata.
- Assert native input type, placeholder/title copy, and validation error copy.

## Preserved Boundaries

- Endpoint validation remains authoritative and fail-closed.
- URL replay and history replay behavior remain unchanged.
- Artifact-key preview behavior remains static.
- Mutation, retry, outbox processing, and status polling are unchanged.

## Verification Target

The first implementation should run:

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` can remain deferred if the change is limited to smoke coverage.

## Push Status

No push was performed for #380.
