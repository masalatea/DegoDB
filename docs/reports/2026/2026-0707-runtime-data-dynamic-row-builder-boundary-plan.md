# Runtime Data Dynamic Row Builder Boundary Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#344 chooses a dynamic filter/sort row-builder boundary before changing the generated runtime-data controls again.

The current generated current/alias runtime-data exploration surface already exposes three fixed filter rows and three fixed ordered sort rows. That is functionally complete for the visible first slice, but it is dense. The next implementation should improve progressive disclosure without weakening the existing read-only endpoint contracts, URL replay, browser history replay, or sortable-header behavior.

## Current Capability

- Current/alias `runtime-data.json` accepts bounded additive filters with `filter[field]` and `filter_op[field]`.
- The endpoint keeps the filter upper bound at 8 fields.
- Current/alias `runtime-data.json` accepts up to 3 ordered `sort[field]=asc|desc` entries.
- Generated browser controls currently expose 3 visible filter rows and 3 visible sort rows.
- URL mirror, initial URL replay, and browser back/forward replay already preserve the visible filter/sort rows.
- Sortable table headers set the primary sort, clear secondary/tertiary sort controls, and synchronize primary header state.

## Recommended First Slice

The recommended first implementation slice is progressive disclosure of the existing fixed rows, not arbitrary row generation.

- Show the primary filter row and primary sort row by default.
- Reveal secondary/tertiary rows when they already have URL/query values or when the user clicks an explicit add control.
- Let remove controls clear and hide secondary/tertiary rows, while preserving at least one primary row for each control family.
- Keep existing parameter names, request construction, URL mirror/replay, and browser history replay.
- Keep endpoint limits unchanged: max 8 filters, max 3 ordered sort fields.
- Keep sortable-header behavior simple: header click remains a primary-sort shortcut that clears secondary/tertiary sort controls.

## Non-Goals

- Do not add unbounded user-created filter or sort rows in this slice.
- Do not expand the generated browser filter UI to the endpoint max-8 in this slice.
- Do not change `runtime-data.json` response version or route/auth/cache behavior.
- Do not add numeric/date-aware comparison or null placement semantics here.
- Do not change artifact-key preview behavior.
- Do not add mutation, retry, or inline outbox processing behavior.

## Verification Plan

First implementation should verify:

- sample28 current/alias runtime controls still fetch read-only runtime data.
- Existing query capture, URL mirror, initial URL replay, and browser back/forward replay still preserve three rows when values exist.
- Add controls reveal hidden secondary/tertiary rows without changing endpoint requests until the user applies a query.
- Remove controls clear hidden row values and prevent stale `filter`, `filter_op`, or `sort` parameters.
- Sortable table headers still clear secondary/tertiary sort rows and update primary header state.
- sample29 and sample31 public runtime browser smokes should be promoted after sample28 behavior is stable because the controls are generated/shared.

## Estimate

- Boundary plan: 0.25 day.
- Progressive disclosure first implementation: 0.5 - 1 day.
- Multi-profile promotion: 0.25 - 0.5 day.
- Closure / stack review before push: 0.25 day.

## Push

Push was not performed.
