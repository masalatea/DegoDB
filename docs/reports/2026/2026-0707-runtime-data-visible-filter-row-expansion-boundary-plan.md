# Runtime Data Visible Filter Row Expansion Boundary Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#304 chooses a visible filter-row expansion boundary plan after typed filter operator multi-profile coverage. #305 records the recommended first implementation slice.

## Recommended First Slice

Expose one additional generated filter row in current/alias runtime-data controls:

- keep the endpoint contract unchanged because `runtime-data.json` already accepts up to 8 field filters;
- add a third visible generated filter row that reuses `filter[field]=value` and `filter_op[field]=contains|eq`;
- preserve existing primary/secondary filter controls and URL behavior;
- include third filter state in generated URL construction, URL mirror/replay, payload control sync, and browser history replay;
- extend one browser smoke path to prove three visible filters can be submitted and retained.

## Why One Row First

The runtime-data controls are already dense: pagination, search, filters, sort, page size, and Clear share one generated control group. A single extra row proves the shape without committing to an arbitrary dynamic row builder or making mobile wrapping hard to scan.

## Deferred

- Arbitrary add/remove filter rows.
- Exposing all 8 endpoint-supported filters at once.
- Grouped filter layout redesign.
- AND/OR condition grouping.
- Multi-value operators.
- Numeric/date operator families.

## Boundary

- In scope: boundary plan, one extra visible filter row recommendation, existing endpoint contract reuse, URL/history expectations, and smoke expectation.
- Out of scope: code changes, endpoint max-filter changes, dynamic filter-row builder, logical grouping, multi-column sort, broader read-model shape, mutation behavior, artifact-key preview changes, and push.
