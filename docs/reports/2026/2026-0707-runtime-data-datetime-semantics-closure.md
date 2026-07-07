# Runtime Data Date/Time Semantics Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#360 closes the runtime-data date/time semantics lane after #359.

The accepted first-slice capability is strict ordered comparison for explicit date/time read-model fields on current/alias `runtime-data.json`.

## Accepted Capability

- Explicit read-model field types now distinguish:
  - `date`;
  - `datetime`;
  - `time`.
- sample31 provides the representative date fixture through `inventory_request.needed_by`.
- Current/alias `runtime-data.json` allows ordered filter operators for explicit date/time fields:
  - `gt`;
  - `gte`;
  - `lt`;
  - `lte`.
- Current/alias `runtime-data.json` sorts explicit date/time fields with strict normalized date/time values.
- Invalid date/time filter or sort values fail closed with JSON 422.
- Numeric filter/sort behavior remains intact.
- Display-string `contains`, `eq`, and non-date/time string sort compatibility remain intact.

## Preserved Boundaries

- No endpoint version change.
- No generated browser-control change.
- No artifact-key preview behavior change.
- No mutation, retry, outbox processing, or status polling change.
- No timezone offset acceptance.
- No natural-language date parsing.
- No null/empty date/time ordering policy yet.

## Verification Baseline

- PHP lint on touched PHP files passed.
- `git diff --check` passed.
- `make sample31-no-code-public-runtime-browser-smoke` passed.
- `make sample28-no-code-public-runtime-browser-smoke` passed.
- `make sample29-no-code-public-runtime-browser-smoke` passed.
- `make test` passed: `337 tests`, `11152 assertions`, `1 skipped`.

## Remaining Candidates

- Type-driven browser operator choices so ordered operators are offered only for numeric/date/time fields.
- Timezone-offset policy for `datetime`.
- Null/empty date/time sort/filter semantics.
- Local stack review and commit cleanup before push.

## Push

Push was not performed.
