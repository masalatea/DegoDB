# Runtime-data empty-result query summary closure

Date: 2026-07-07

## Summary

#402 closes the runtime-data empty-result query summary lane after #401.

## Accepted coverage

- Current/alias generated runtime controls keep an active query summary after a read-only no-match search.
- The summary includes the no-match search term and `Rows: 0`.
- The `aria-label` also includes `Rows: 0`.
- The list renders an empty row instead of silently dropping the active query context.

## Preserved boundary

- Runtime-data endpoint parsing is unchanged.
- `runtime-data.json` contracts are unchanged.
- URL/query behavior is unchanged.
- Sample seed data is unchanged.
- Mutation, submit, sync outbox, and artifact-key preview behavior are unchanged.

## Verification baseline

Verification remains #401:

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

The umbrella public runtime smoke passed through sample28, sample29, and sample31 `ok: true` outputs.

## Remaining candidates

- Local runtime-data stack review after empty-result summary coverage, before any push decision.
- Broader error-summary wording can remain separate unless a concrete tryout issue appears.

## Push / history

Push was not performed. History was not rewritten.
