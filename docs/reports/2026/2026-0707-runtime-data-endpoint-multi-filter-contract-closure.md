# Runtime Data Endpoint Multi Filter Contract Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#278 replans after the multi-filter upper-bound smoke and chooses closure before changing generated browser controls. #279 closes the endpoint multi-filter contract lane.

This closure records the accepted endpoint capability for current/alias public runtime previews: `runtime-data.json` supports bounded multi-field read-only filters, while generated browser controls remain intentionally single-filter until a separate UI slice promotes that behavior.

## Accepted Capability

- Current/alias `runtime-data.json` accepts multiple `filter[field]=value` query clauses.
- The filter map is bounded to 8 fields or fewer.
- Empty filter values are ignored.
- Invalid filter field names and invalid filter values fail closed.
- Unknown filter fields fail closed after row-shape validation.
- Multiple filters are combined with AND semantics.
- Matching rows preserve existing query-result selection semantics for detail/form defaults.
- The response echoes accepted filters in `query.filter`.
- The endpoint returns JSON with `ok: false` and HTTP 422 when 9 filter fields are requested.

## Latest Verification Baseline

- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11124 assertions`, `1 skipped`)

## Remaining Candidates

- Generated browser multi-filter controls: expose more than one filter row in the runtime-data control surface.
- URL mirror/replay multi-filter handling: preserve multiple filter clauses in browser URL state when generated controls can emit them.
- Typed filter operators: equality, contains, numeric comparisons, date ranges, and field-type-aware validation.
- Multi-column sort: extend sort after one-sort behavior remains sufficient and understandable.
- Cross-sample endpoint smoke: run sample29/sample31 public runtime browser smoke when their multi-filter coverage needs a dedicated baseline.
- Broader read-model shape: relation-shaped rows, display labels, denormalized context fields, or generated field display metadata beyond the current DBAccess row shape.
- Commit/push cleanup: review the local stack before the next push.

## Boundary

- In scope: closure, accepted endpoint multi-filter capability, latest verification baseline, remaining candidates, and no-push status.
- Out of scope: generated browser UI changes, URL mirror/replay changes, endpoint contract changes, typed operators, multi-column sort, mutation behavior, artifact-key preview changes, and push.
