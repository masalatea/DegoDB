# Runtime Data Field Typing Boundary Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#350 chooses a runtime-data field typing boundary before adding numeric/date-aware filter or sort semantics.

The generated runtime render fields already carry a `type` value. Current runtime-data query semantics still treat filter and sort comparison values as display strings. That behavior is stable and predictable, but numeric/date-aware comparison should not be added until the endpoint has an explicit read-model field typing map and a conservative coercion rule.

## Current Capability

- Generated runtime screen fields include `field_key`, `label`, `type`, `required`, `readonly`, and `visibility`.
- Current/alias `runtime-data.json` returns screen `data`, screen `metadata`, and query echoes.
- Field filters support default `contains` and explicit `eq`.
- Sort supports up to 3 ordered fields.
- Existing filter/sort semantics compare rendered display strings.
- Browser controls, URL replay, and browser history replay already preserve filter/sort state.

## Recommended First Slice

The recommended first implementation is additive field-type metadata in `runtime-data.json`, not semantic behavior changes yet.

- Build a per-contract `fields` metadata map from generated screen definition fields.
- Expose stable field metadata in the runtime-data response, probably under a top-level read-model metadata section rather than duplicating it inside every screen.
- Include at minimum `field_key`, `label`, and normalized `type`.
- Normalize unknown or empty types to `string`.
- Keep existing display-string filtering and sorting unchanged in this slice.
- Add endpoint and browser-smoke assertions that field type metadata is present for sample28, then promote to sample29/sample31 if the shape is shared.

## Deferred Semantics

Numeric/date-aware behavior should be a later slice after metadata is visible and tested:

- Numeric `eq`, `gt`, `gte`, `lt`, `lte` operators.
- Date/time parsing and comparison.
- Explicit null/empty placement for sort.
- UI operator choices based on field type.
- Validation messages for invalid typed values.

## Non-Goals

- Do not change current `contains` / `eq` behavior in this boundary slice.
- Do not infer types from row values.
- Do not introduce database-specific type handling.
- Do not change endpoint version, auth, route, or cache behavior.
- Do not change artifact-key static preview behavior.
- Do not add mutation, retry, or outbox processing behavior.

## Estimate

- Boundary plan: 0.25 day.
- Additive field metadata first implementation: 0.25 - 0.5 day.
- Multi-profile promotion and closure: 0.25 - 0.5 day.

## Push

Push was not performed.
