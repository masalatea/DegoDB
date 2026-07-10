# Runtime Data Query Controls Milestone Closure

Date: 2026-07-06

Status: `DONE`

## Summary

#240 chooses a closure report before starting field-specific filters, sort controls, or another read-model lane. #241 closes the runtime-data query controls milestone.

This milestone builds on the generated DBAccess foundation rather than replacing it: current/alias previews read live business rows through the generated runtime data endpoint, while immutable artifact-key previews and submit/outbox mutation behavior remain separate.

## Accepted Capability

- Current/alias `runtime-data.json` exposes read-only, no-store live data rendered from generated DBAccess rows.
- Current/alias public runtime previews can Refresh no-query live data.
- List rows can be selected by generated action key, refreshing detail/form screens and preserving hidden action keys.
- List rows can be paginated with `page` and `page_size`.
- Runtime controls expose page-size Apply, Previous/Next, direct page Go, total-row count, and Search.
- Search uses bounded `q` against rendered display values and keeps normal Refresh as the no-query full-list reload.
- sample28, sample29, and sample31 prove the behavior across different no-code profiles.

## Preserved Boundaries

- Artifact-key previews remain immutable/static.
- Submit/outbox mutation remains a separate path from read-only runtime-data queries.
- `selected_key` remains the detail/form selection mechanism.
- Pagination slices list rows only.
- Search is explicit and does not change default Refresh semantics.

## Remaining Candidates

- Field-specific filters with explicit contract shape.
- Sort controls and endpoint sort contract.
- Search state persistence or clearer reset affordance.
- Form default semantics for creating or editing rows beyond selected-row rendering.
- Operator/admin wording for live runtime data query boundaries.
- Broader accessibility/UI polish once the control set stabilizes.

## Verification Baseline

Latest full verification before closure:

- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11105 assertions`, `1 skipped`)

Push was not performed for this closure.
