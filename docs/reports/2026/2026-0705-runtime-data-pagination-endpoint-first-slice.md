# Runtime Data Pagination Endpoint First Slice

Status: DONE
Date: 2026-07-05

## Summary

This slice implements the first pagination/page-size behavior for current/alias `runtime-data.json`.

The endpoint now accepts optional `page` and `page_size` query parameters. Pagination is intentionally list-only: list screen rows are sliced, while detail/form screens continue to use the existing current item selection rule. That means no-query behavior is unchanged, and `selected_key` remains the mechanism for choosing a specific detail/form row.

## Implemented

- Added fail-closed pagination query parsing for `page` and `page_size`.
- Added a conservative default page size when only `page` is supplied.
- Added page `1` default when only `page_size` is supplied.
- Added a maximum `page_size` of `100`.
- Applied pagination only to list screen rows.
- Preserved full generated DBAccess row reads for detail/form selection.
- Added list-screen `metadata.pagination` when pagination is active.
- Added direct endpoint smoke coverage for `page=2&page_size=1`.
- Added invalid pagination smoke coverage for `page=0`.

## Metadata

When pagination is active, list screens include:

- `pagination.page`
- `pagination.page_size`
- `pagination.total_rows`
- `pagination.page_count`
- `pagination.has_previous_page`
- `pagination.has_next_page`

Existing `row_count` continues to mean the number of rows rendered in that screen payload.

## Verified Behavior

- sample28 paginated current runtime data returns one list row for `page=2&page_size=1`.
- sample28 paginated list row key is `1002`, while detail/form default selection remains `1001`.
- sample29 paginated list row key is `2002`, while detail/form default selection remains `2001`.
- sample31 paginated list row key is `3102`, while detail/form default selection remains `3101`.
- `page=0` fails closed with JSON 422.
- Existing selected-key behavior still works.
- Existing submit/outbox processing proof still works.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11093 assertions, skipped 1)

## Remaining Candidates

- Browser pagination controls.
- Multi-profile UI smoke for browser controls after controls exist.
- Filter parameters.
- Server-side generated DBAccess limit/offset optimization.
- Selection behavior when future filters hide the selected row.

Push was not performed.
