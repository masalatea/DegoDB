# Runtime Data Visible Filter Row Expansion Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#310 chooses closure after the third visible runtime-data filter row was implemented and promoted across sample29 and sample31. #311 closes the visible filter-row expansion lane.

## Accepted Capability

Generated current/alias runtime-data exploration now exposes three visible filter rows. Each visible row has:

- field selection;
- operator selection with `contains` / `eq`;
- value input.

Those rows are carried through generated query capture, control sync, URL construction, initial URL replay, URL mirror, and browser history replay. The read-only `runtime-data.json` endpoint remains unchanged at the contract level and still supports up to 8 additive filters.

## Verification Baseline

- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`

Latest full test result from this lane: 337 tests, 11134 assertions, 1 skipped.

## Remaining Candidates

- Dynamic add/remove filter rows.
- Exposing more of the endpoint's max-8 filter capacity.
- Grouped or mobile-specific query-control layout.
- Numeric/date operator families once field typing is explicit.
- Multi-column sort.
- Broader read-model metadata shape.
- Push cleanup / local commit stack review.

## Boundary

- In scope: lane closure, accepted capability, verification baseline, and remaining candidates.
- Out of scope: code changes, endpoint contract changes, new operators, layout redesign, mutation behavior, history rewrite, and push.
