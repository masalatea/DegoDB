# Runtime Data Multi-Column Sort Boundary Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#314 chooses a runtime-data multi-column sort endpoint boundary after the three visible filter-row lane and local stack review. #315 records the recommended first slice before changing generated browser sort controls.

## Recommended First Slice

Add bounded multi-column sort support to current/alias read-only `runtime-data.json`:

- accept an additive `sort[field]=asc|desc` list;
- keep the existing one-field query as valid and backward-compatible;
- cap the first endpoint slice at 3 sort fields;
- validate each field key and each direction fail-closed;
- apply sort keys in request order, then fall back to original row order for stable ties;
- echo the full sort map in `query.sort`;
- keep generated browser controls on one visible sort row for this first endpoint slice.

## Deferred

- Generated browser multi-sort controls.
- URL replay/control sync for more than the first visible sort row.
- Dynamic add/remove sort rows.
- Numeric/date-aware comparisons.
- Null placement configuration.
- Field typing metadata.

## Boundary

- In scope: endpoint contract expansion, stable multi-key sorting, direct endpoint smoke coverage, and backward compatibility for one-field sort.
- Out of scope: generated browser UI changes, browser history changes, filter behavior changes, mutation behavior, artifact-key preview changes, history rewrite, and push.
