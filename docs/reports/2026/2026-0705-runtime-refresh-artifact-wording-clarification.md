# Runtime Refresh Artifact Wording Clarification

Status: `DONE`.

Date: 2026-07-05.

## Context

#199 confirmed that `Refresh preview` reloads the current generated preview artifact. It does not fetch fresh DB rows or regenerate/publish a new runtime preview.

## Implemented

- Updated generated runtime refresh status copy to say it reloads the generated preview artifact.
- Updated submit follow-up copy to say users should process the outbox item, then reload the generated preview artifact or open the outbox detail.
- Updated demo-processing copy to avoid implying a fresh data fetch.
- Updated focused PHPUnit expectations and browser smoke expectations.

## Boundary

No fresh data endpoint was added.

No regenerate/publish/current-revision workflow was added.

No runtime retry or inline processing behavior was added.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- focused `NoCodeRuntimeTest`: `13 tests, 241 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test`

Full test result: `Tests: 337, Assertions: 11063, Skipped: 1`.

## Push Boundary

No push was performed.
