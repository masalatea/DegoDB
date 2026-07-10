# Runtime Data Typed Filter Operators Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#300 replans after the typed filter operator first slice and chooses closure before starting another runtime-data behavior lane. #301 closes the lane.

## Accepted Capability

Current/alias generated runtime-data filtering now supports a small typed operator layer:

- omitted `filter_op[field]` keeps the existing display-string `contains` behavior;
- explicit `filter_op[field]=contains` records the same compatibility path;
- explicit `filter_op[field]=eq` performs exact display-value matching;
- invalid operators fail closed;
- active operators are echoed through `query.filter_op`;
- generated primary and secondary filter rows expose operator selects;
- generated URL mirror/replay and browser back/forward preserve operator state.

This remains a read-only exploration capability on authenticated current/alias `runtime-data.json`. Immutable artifact-key previews and submit/outbox mutation behavior are unchanged.

## Latest Verification Baseline

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test`
  - `337 tests`, `11131 assertions`, `1 skipped`.

## Remaining Candidates

- Numeric/date comparison operators once field typing metadata is explicit.
- Multi-value operators such as `in` after query-shape and UI density are settled.
- More than two visible generated filter rows.
- Multi-column sort.
- Broader read-model shape and display policy.
- Multi-profile promotion of browser operator smoke to sample29 and sample31.
- Push cleanup when the local ahead stack is ready to publish.

## Boundary

- In scope: closure, accepted capability, latest verification baseline, remaining candidates, and no-push status.
- Out of scope: new code, additional operators, field typing metadata, additional filter-row UI, multi-column sort, broader read-model shape changes, mutation behavior, artifact-key preview changes, and push.
