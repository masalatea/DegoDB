# Runtime Data Date/Time Semantics Boundary Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#358 chooses date/time semantics as the next behavior lane after numeric filter and numeric sort closure.

The current endpoint already normalizes read-model field types including `date`, `datetime`, and `time`, but the active no-code smoke samples do not yet provide a clear date/time fixture field. The next implementation should not add parser behavior until it also has a representative fixture and direct endpoint smoke coverage.

## Recommended First Slice

- Add or select a no-code sample field with generated read-model type metadata:
  - `date`,
  - `datetime`, or
  - `time`.
- Keep existing URL contracts:
  - reuse `filter_op[field]=gt|gte|lt|lte` for typed date/time filters;
  - reuse `sort[field]=asc|desc` for typed date/time sort.
- Accept strict ISO-like local values first:
  - `date`: `YYYY-MM-DD`;
  - `datetime`: `YYYY-MM-DDTHH:MM:SS` or `YYYY-MM-DD HH:MM:SS`;
  - `time`: `HH:MM:SS`.
- Compare only values of the same typed field category.
- Fail closed with HTTP 422 when a date/time operator targets a non-date/time field or a compared value cannot be parsed.
- Keep string `contains` and `eq` behavior display-string based for compatibility.
- Keep non-date/time sort fields on their existing numeric or display-string semantics.

## Non-Goals

- Do not infer date/time types from row values.
- Do not add timezone conversion in the first slice.
- Do not accept broad natural-language dates.
- Do not change endpoint version.
- Do not change generated browser controls yet.
- Do not change artifact-key preview behavior.
- Do not change mutation, retry, outbox processing, or status polling.

## Open Boundary Decisions

- Which no-code sample should carry the first explicit date/time fixture field.
- Whether `datetime` should accept timezone offsets in a later slice.
- Whether null/empty date/time row values should fail closed, sort last, or require an explicit null-placement policy.
- Whether browser labels should expose typed operators only after direct endpoint semantics are stable.

## Verification Plan

- Add direct endpoint smoke coverage for date/time filter comparison.
- Add direct endpoint smoke coverage for date/time asc/desc sort.
- Add fail-closed coverage for invalid date/time values.
- Preserve sample28/sample29/sample31 numeric behavior smokes.
- Run public runtime browser smoke for the chosen sample.
- Run full `make test` after implementation.

## Push

Push was not performed.
