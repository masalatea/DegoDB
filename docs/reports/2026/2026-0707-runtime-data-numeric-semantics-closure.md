# Runtime Data Numeric Semantics Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#357 closes the runtime-data numeric semantics lane after the field typing, numeric filter, and numeric sort slices.

This lane adds numeric behavior only where the generated read-model field metadata explicitly marks a field as `integer` or `number`. It does not infer types from row values and does not change browser controls or public endpoint versioning.

## Accepted Capability

- Current/alias `runtime-data.json` exposes read-model field metadata from generated screen definitions.
- Numeric filter operators are accepted for explicit numeric fields:
  - `gt`,
  - `gte`,
  - `lt`,
  - `lte`.
- Numeric sort is applied to explicit numeric fields while keeping `sort[field]=asc|desc`.
- Numeric query and row values use strict parsing.
- Invalid numeric comparisons fail closed with HTTP 422.
- Direct endpoint smoke coverage verifies sample28, sample29, and sample31.

## Preserved Boundaries

- Omitted filter operators keep display-string `contains` behavior.
- `filter_op[field]=contains` remains display-string based.
- `filter_op[field]=eq` remains display-string exact matching, including numeric fields.
- Non-numeric sort fields remain display-string `strnatcasecmp()` sorted.
- Multi-sort order and stable original-row tie-breaking remain unchanged.
- Generated browser filter/sort controls remain unchanged.
- Endpoint version remains `no-code-runtime-data-v0`.
- Artifact-key previews remain static.
- Mutation, retry, outbox processing, and status polling are unchanged.

## Verification Baseline

Latest implementation verification from #356:

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`

`make test` result: 337 tests, 11152 assertions, 1 skipped.

## Remaining Candidates

- Date/time filter and sort semantics.
- Type-driven browser operator choices after endpoint semantics are proven.
- Explicit null/empty placement rules for typed sort fields.
- Browser UX labels that communicate typed semantics without changing URL contracts.

## Recommended Next Step

Start with date/time semantics boundary planning before implementation. Date/time comparisons should define accepted formats, timezone assumptions, null/empty behavior, and compatibility with existing display-string behavior before changing the endpoint.

## Push

Push was not performed.
