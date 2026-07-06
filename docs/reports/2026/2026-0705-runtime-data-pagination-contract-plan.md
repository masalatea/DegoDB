# Runtime Data Pagination Contract Plan

Status: DONE
Date: 2026-07-05

## Summary

This plan fixes the first pagination/page-size boundary for current/alias `runtime-data.json` before implementation.

The previous milestone made row selection work across sample28, sample29, and sample31. Pagination should build on that without changing the row-selection meaning: list pagination controls which rows are shown in the list, while `selected_key` continues to choose the detail/form row.

## Planned First Slice

- Add optional `page` and `page_size` query parameters to current/alias `runtime-data.json`.
- Keep no-query behavior unchanged: all rows are returned and first-row selection remains the default.
- Apply pagination only to list screen rows.
- Keep detail/form screens selected by `selected_key` when provided.
- When `selected_key` is absent, detail/form keep current first-row behavior from the unsliced generated DBAccess result.
- Return additive pagination metadata on list screens.
- Reject invalid `page` / `page_size` values with fail-closed JSON 422.
- Keep artifact-key previews immutable and static.
- Keep submit/outbox processing as a separate mutation path.

## Proposed Query Semantics

- `page`: positive integer, default absent.
- `page_size`: positive integer, default absent.
- Pagination is active only when at least one pagination parameter is present.
- If only `page` is present, use a conservative default page size.
- If only `page_size` is present, use page `1`.
- Enforce a maximum page size to keep public runtime reads bounded.

## Proposed Metadata

Add list-screen metadata without removing existing fields:

- `pagination.page`
- `pagination.page_size`
- `pagination.total_rows`
- `pagination.page_count`
- `pagination.has_previous_page`
- `pagination.has_next_page`

Keep existing metadata:

- `row_count`: number of rows rendered in this screen payload
- `selected_key`: current selected key metadata, when applicable
- `freshness`: `live-read`

## Selection Boundary

`selected_key` should not be limited to the current list page in the first slice. A selected row may be outside the current page and still render detail/form when generated DBAccess can resolve it from the full read result.

This keeps browser row selection simple today and avoids making pagination imply a new detail route or filter contract.

## Verification Plan

- Focused PHP coverage for invalid pagination input.
- Direct runtime-data smoke coverage for a paginated sample28 current request.
- Browser smoke coverage after UI controls are added in a later slice.
- Full `make test` before committing implementation.

## Deferred

- Browser pagination controls.
- Filter parameters.
- Server-side DBAccess limit/offset optimization.
- Cursor pagination.
- Sorting controls.
- Selection behavior when filters hide the selected row.

Push was not performed.
