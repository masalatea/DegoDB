# Runtime Data Typed Filter Operator Boundary Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#296 chooses a typed filter operator boundary plan after the browser history replay lane. #297 records the recommended first implementation slice before changing endpoint or generated browser behavior.

## Recommended First Slice

Preserve the existing current/alias runtime-data filter contract and add a small optional operator companion:

- keep `filter[field]=value` as the stable filter value shape;
- treat an omitted operator as the current display-string `contains` behavior so existing URLs, smokes, and browser controls keep working;
- add `filter_op[field]=contains|eq` as the first optional operator shape;
- echo the active operator map in the runtime-data response query metadata;
- update generated URL mirror/replay so operator state survives browser URL, back/forward, and screen re-render;
- add one operator select for each visible generated filter row after the endpoint contract is proven.

## Operator Scope

First slice operators:

- `contains`: current bounded display-string containment behavior.
- `eq`: exact display-value match behavior.

Deferred operators:

- numeric comparisons such as `gt`, `gte`, `lt`, and `lte`;
- date/time comparisons;
- `in` / multi-value filters;
- null / blank predicates;
- per-field operator lists based on schema metadata.

Those should wait until runtime-data responses carry explicit field typing / operator eligibility metadata. Without that metadata, numeric and date comparisons would either guess from display strings or silently become inconsistent across sample domains.

## Contract Notes

- Contract version can remain `no-code-runtime-data-v0` for the first slice because the change is backward compatible and additive.
- Unknown operators should fail closed with a clear runtime-data error response.
- Operators without a matching `filter[field]` value should be ignored or rejected consistently; the first implementation should prefer fail-closed validation when an operator names an unknown field.
- Artifact-key previews remain immutable static artifacts. Typed operators apply only to authenticated current/alias read-only `runtime-data.json` routes.
- Submit/outbox mutation remains separate from read-only runtime-data query behavior.

## Suggested Verification

- PHP lint for endpoint and generated runtime files.
- JS syntax check for browser smoke script.
- Direct runtime-data endpoint smoke proving:
  - omitted operator keeps current contains behavior;
  - `filter_op[field]=contains` matches the existing result;
  - `filter_op[field]=eq` returns an exact display-value result;
  - unknown operator fails closed.
- Browser smoke proving operator state is mirrored, replayed from URL, and restored through back/forward.
- Full `make test` before committing code.

## Boundary

- In scope: boundary plan, recommended additive URL/query contract, first operator set, response metadata expectation, validation expectation, and verification shape.
- Out of scope: code changes, numeric/date comparison semantics, multi-value filters, additional visible filter rows beyond existing primary/secondary controls, multi-column sort, broader read-model shape, mutation behavior, and push.
