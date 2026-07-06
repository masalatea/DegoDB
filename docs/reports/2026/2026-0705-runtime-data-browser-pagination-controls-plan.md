# Runtime Data Browser Pagination Controls Plan

Status: DONE
Date: 2026-07-05

## Summary

This plan fixes the first browser pagination controls boundary for current/alias live runtime data.

The endpoint now supports `page` and `page_size`, but the generated runtime preview still starts with the existing no-query full-list behavior. The first browser UI slice should keep that behavior and make pagination opt-in from the list screen.

## Planned First UI Slice

- Keep normal Refresh unchanged: it fetches `runtime-data.json` without pagination query and renders the full list.
- Add an explicit page-size entry control to live current/alias list screens.
- The first page-size action fetches `runtime-data.json?page=1&page_size=<chosen>`.
- After a paginated response, show Previous / Next controls using `metadata.pagination`.
- Previous / Next preserve the active page size and request the next page through `runtime-data.json?page=...&page_size=...`.
- Disable Previous on the first page and Next on the last page.
- Reuse the existing runtime-data refresh status surface.
- Keep row `Select` controls working on the currently displayed page.

## Selection Boundary

Pagination controls only change list rows.

Detail/form selection remains governed by `selected_key`. A row selected from a paginated page should request `runtime-data.json?selected_key=<key>` unless a later slice deliberately combines selected key and pagination query.

This keeps the first UI slice aligned with the endpoint boundary and avoids implying filter/detail route behavior.

## Non-Goals

- Do not change no-query Refresh into a default paginated request.
- Do not add browser sorting or filtering.
- Do not optimize generated DBAccess with server-side limit/offset yet.
- Do not add pagination controls to immutable artifact-key previews.
- Do not make submit/outbox processing depend on pagination state.

## Verification Plan

- Browser smoke for sample28 current/alias:
  - initial Refresh remains full-list
  - page-size entry fetches `page=1&page_size=1`
  - Next fetches `page=2&page_size=1`
  - rendered list row changes while detail/form default behavior remains stable
- Direct endpoint smoke remains the source of multi-profile endpoint confidence for sample29/sample31 until browser controls are stable.
- Full `make test` before committing implementation.

## Remaining Candidates

- Implement the browser controls.
- Promote browser pagination controls smoke across sample29/sample31.
- Decide whether row selection should preserve active pagination query.
- Add filter controls after pagination controls are stable.

Push was not performed.
