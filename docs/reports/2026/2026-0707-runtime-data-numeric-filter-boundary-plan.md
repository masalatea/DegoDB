# Runtime Data Numeric Filter Boundary Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#353 chooses numeric filter semantics as the next behavior lane after runtime-data field typing.

The goal is to use explicit read-model field metadata for numeric comparison instead of inferring types from row values. The first implementation should extend current/alias `runtime-data.json` query behavior for numeric fields only, while keeping generated browser controls, endpoint version, and non-numeric behavior stable.

## Recommended First Slice

- Allow `filter_op[field]` values `gt`, `gte`, `lt`, and `lte` in addition to existing `contains` and `eq`.
- Apply `gt` / `gte` / `lt` / `lte` only when the field type is numeric, using the read-model metadata built from generated screen definition fields.
- Treat `integer` and `number` as numeric for this slice.
- Coerce compared row values and query values using a strict numeric parser.
- Fail closed with HTTP 422 when:
  - a numeric comparison operator is used on a non-numeric field,
  - the query value is not numeric,
  - a compared row value for the requested field is not numeric.
- Keep `contains` behavior display-string based.
- Keep `eq` behavior display-string based for this first slice, even on numeric fields, to avoid changing existing URLs.
- Echo the requested operator in `query.filter_op` unchanged when accepted.

## Non-Goals

- Do not change browser filter controls yet.
- Do not add date/time comparison.
- Do not change sort semantics.
- Do not change endpoint version.
- Do not infer field types from row values.
- Do not change artifact-key preview behavior.
- Do not change mutation, retry, outbox processing, or status polling.

## Verification Plan

- Add endpoint smoke coverage for numeric comparison on sample28 `priority` and sample31 `quantity_needed`.
- Add fail-closed endpoint smoke coverage for a numeric operator on a string field.
- Preserve existing sample28/sample29/sample31 public runtime browser smokes.
- Run full `make test` after implementation.

## Push

Push was not performed.
