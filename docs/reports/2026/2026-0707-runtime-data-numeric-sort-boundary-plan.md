# Runtime Data Numeric Sort Boundary Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#355 chooses numeric sort semantics as the next behavior lane after numeric filter semantics.

Current runtime-data sort behavior compares rendered display strings with `strnatcasecmp()`. That is stable for strings, but numeric fields should sort by numeric value once read-model field typing is explicit. This plan defines the first implementation boundary before changing endpoint behavior.

## Recommended First Slice

- Keep the existing query shape: `sort[field]=asc|desc`.
- Use generated read-model field metadata to decide sort semantics.
- Treat normalized `integer` and `number` fields as numeric.
- Compare numeric sort fields with the same strict numeric parser used by numeric filters.
- Keep non-numeric fields on the existing display-string `strnatcasecmp()` behavior.
- Preserve existing stable sort behavior:
  - multi-sort fields remain ordered by query order;
  - later sort fields break ties from earlier sort fields;
  - original row order breaks ties after all requested sort fields compare equal.
- Fail closed with HTTP 422 when a requested numeric sort field contains a non-numeric row value.
- Keep `query.sort` response metadata unchanged.

## Non-Goals

- Do not change the `sort[field]=asc|desc` URL contract.
- Do not add `sort_type`, `sort_op`, or a new endpoint version.
- Do not change generated browser sort controls yet.
- Do not add date/time sorting.
- Do not infer field types from row values.
- Do not change filter behavior.
- Do not change artifact-key preview behavior.
- Do not change mutation, retry, outbox processing, or status polling.

## Verification Plan

- Add endpoint smoke coverage for numeric ascending/descending sort on:
  - sample28 `priority`,
  - sample29 `id`,
  - sample31 `quantity_needed`.
- Preserve existing string sort coverage on `status`.
- Preserve existing multi-sort coverage and stable tie-breaking expectations.
- Preserve invalid direction and too-many-sort fail-closed coverage.
- Run sample28/sample29/sample31 public runtime browser smokes after implementation.
- Run full `make test` after implementation.

## Push

Push was not performed.
