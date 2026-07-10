# Runtime-data datetime/time native filter smoke first slice

Date: 2026-07-07

## Summary

#381 adds smoke-level coverage for generated datetime/time native filter controls.

The sample31 public runtime browser smoke now probes the shared generated filter-control sync path for `datetime` and `time` metadata, while leaving sample data and endpoint contracts unchanged.

## Changes

- Extended `mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`.
- Temporarily exercises `datetime` field metadata on the existing generated filter controls.
- Temporarily exercises `time` field metadata on the existing generated filter controls.
- Asserts:
  - `datetime-local` native input type;
  - `time` native input type;
  - `YYYY-MM-DDTHH:MM:SS` placeholder/title copy;
  - `HH:MM:SS` placeholder/title copy.

## Preserved Boundaries

- Sample31 seed data is unchanged.
- Generated endpoint contracts are unchanged.
- URL replay and history replay behavior are unchanged.
- Artifact-key preview behavior remains static.
- Mutation, retry, outbox processing, and status polling are unchanged.

## Verification

Completed verification:

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`

Full `make test` was not rerun for #381 because the change is limited to smoke coverage.

## Push Status

No push was performed for #381.
