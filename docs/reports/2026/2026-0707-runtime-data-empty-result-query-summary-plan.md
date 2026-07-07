# Runtime-data empty-result query summary plan

Date: 2026-07-07

## Summary

#400 chooses runtime-data empty-result query summary coverage as the next small lane after the mobile-density stack review.

## Planned slice

Add browser-smoke coverage that exercises an existing current/alias read-only runtime-data query returning zero rows, then verifies the generated active query summary stays visible and includes `Rows: 0`.

This is intentionally coverage-first. The generated runtime already has the mechanics for active query summaries and pagination metadata; the goal is to lock the zero-row UX behavior so it does not regress.

## Preserved boundary

- Do not change runtime-data endpoint contracts.
- Do not change URL/query behavior.
- Do not change sample seed data.
- Do not change mutation, submit, sync outbox, or artifact-key preview behavior.

## Verification target

The expected implementation verification is:

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- A focused public runtime browser smoke covering the touched path, or the full `make sample-no-code-public-runtime-browser-smoke` matrix if the shared smoke path is touched.

## Push / history

Push is not part of this plan. History rewrite is not planned.
