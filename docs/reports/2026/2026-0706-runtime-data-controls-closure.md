# Runtime Data Controls Closure

Date: 2026-07-06

Status: `DONE`

## Summary

#252 chooses a closure report after row selection, pagination, search, field-filter, and sort controls all reached a coherent first-slice boundary. #253 closes the runtime-data controls lane.

This lane keeps the no-code product surface grounded in the existing generated DBAccess/runtime-data foundation: browser controls issue read-only current/alias `runtime-data.json` queries, not static preview rewrites and not submit/outbox mutations.

## Accepted Capability

- Current/alias public runtime previews can fetch read-only live generated DBAccess rows through `runtime-data.json`.
- List rows can select a row by generated action key and refresh detail/form screens through `selected_key`.
- List screens can page, change page size, jump to a page, and display total row count from pagination metadata.
- List screens can search with bounded `q`.
- List screens can filter with bounded `filter[field]=value`.
- List screens can sort with bounded one-field `sort[field]=asc|desc`.
- Normal Refresh remains a no-query full-list runtime-data reload.
- Immutable artifact-key previews remain static.
- Submit/outbox mutation behavior remains separate from read-only runtime-data queries.

## Verification Baseline

Latest verified baseline for this lane:

- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11113 assertions, skipped 1)

## Remaining Candidates

- Persisted query state or visible combined query state.
- Layout/accessibility polish for the now-denser runtime-data control row.
- Query combination behavior, for example sort plus filter plus pagination in one explicit browser request.
- Form default semantics for create/update flows beyond selected existing rows.
- Broader read-model shape and operator/admin wording for live runtime-data boundaries.

## Boundary

- Push was not performed.
- No runtime behavior changed in this closure step.
