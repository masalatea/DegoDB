# Runtime Data Query Summary Polish Closure

Date: 2026-07-07

## Summary

#390 closes the runtime-data query summary polish lane.

## Accepted Capability

Generated current/alias runtime controls now show a readable active query summary for:

- Search
- Filters
- Sort
- Page size

The summary uses rendered control labels:

- Field labels such as `Status`, `QuantityNeeded`, and `ItemSku`
- Filter operator labels such as `Contains`
- Sort direction labels such as `Asc` and `Desc`

## Preserved Boundary

Unchanged:

- URL query values remain key/token based.
- Runtime-data endpoint parsing remains key/token based.
- `runtime-data.json` contract is unchanged.
- Sample data is unchanged.
- Mutation behavior is unchanged.
- Sync outbox behavior is unchanged.

## Verification Baseline

Latest passed verification from #389:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

The public runtime smoke matrix completed through sample28, sample29, and sample31 with `ok: true` outputs.

## Remaining Candidates

Potential follow-up candidates, not active until a fresh priority decision:

- Visual density polish for the runtime-data controls if the query row becomes too wide.
- A compact token/chip style for active query pieces.
- Dedicated accessibility copy for active filter/sort state.
- A local stack review before the next push decision.

## Status

Done locally. Push was not performed.
