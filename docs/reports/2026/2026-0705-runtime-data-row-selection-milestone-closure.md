# Runtime Data Row Selection Milestone Closure

Status: DONE
Date: 2026-07-05

## Summary

This report closes the first runtime-data row-selection milestone.

The milestone began with read-only current/alias `runtime-data.json` delivery and now reaches browser-visible row selection across the three product-facing no-code profiles: sample28, sample29, and sample31. Artifact-key previews remain immutable and static; current/alias previews are the only pages that fetch live runtime data.

## Accepted Capability

- Current/alias public runtime previews can fetch authenticated, read-only, no-store live runtime data.
- `runtime-data.json` is versioned as `no-code-runtime-data-v0`.
- Generated DBAccess rows render list/detail/form screen data.
- The first-row default remains stable when no selected key is requested.
- `selected_key` can select a non-first row for detail/form screens.
- Missing selected keys fail closed with JSON 422.
- Browser row selection adds `Select` controls for live current/alias list rows.
- Selecting a row fetches `runtime-data.json?selected_key=...`, highlights the selected row, and refreshes detail/form data.
- Hidden action key values are preserved from the selected row so the local action-intent draft remains ready when the selected row supplies required key/input values.
- Submit/outbox processing remains a separate mutation path; the read endpoint stays read-only.
- The behavior is verified across sample28, sample29, and sample31.

## Verification Baseline

- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11093 assertions, skipped 1)

The latest full verification was run in #224 after promoting sample29/sample31 row-selection fixtures.

## Remaining Candidates

- Query-driven pagination and page-size controls.
- Filter parameters derived from generated screen/operation metadata.
- Form default behavior for create/update screens.
- Operator/admin wording for live runtime data selection boundaries.
- A later product closure after pagination/filter semantics are chosen or explicitly parked.

## Recommended Next Step

Choose a small boundary plan for query-driven pagination/page-size before implementation. Pagination changes the response contract more than row selection did because list slices, total counts, selected-row availability, and browser controls need one shared rule.

Push was not performed.
