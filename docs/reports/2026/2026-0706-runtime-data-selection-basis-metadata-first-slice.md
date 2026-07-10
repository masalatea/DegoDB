# Runtime Data Selection Basis Metadata First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#262 replans after the runtime-data query controls closure and chooses form/detail default selection semantics before query reset, URL/history persistence, visual density polish, richer filter/sort models, or broader read-model shape. #263 implements the first small slice.

The goal is to make the read-only runtime-data payload explain why detail/form values were chosen after a live data fetch, without changing which row is selected today.

## Planned / Implemented

- Add additive `metadata.selection_basis` to generated runtime-data screens.
- Report `explicit-selected-key` when `selected_key` is provided.
- Report `query-result-first-row` when search, filter, or sort query results choose the current detail/form row.
- Report `default-first-row` when no selected key and no search/filter/sort query is active.
- Report `empty-result` when the queried row set is empty.
- Preserve existing list rows, detail/form values, pagination behavior, query echo, immutable artifact-key previews, and submit/outbox mutation behavior.
- Extend public runtime endpoint smoke coverage for the selection basis metadata.

## Boundary

- In scope: additive runtime-data metadata and smoke assertions.
- Out of scope: changing selected-row behavior, form defaults, reset controls, URL/history persistence, visual redesign, endpoint query contract changes, and mutation behavior.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11116 assertions`, `1 skipped`)
