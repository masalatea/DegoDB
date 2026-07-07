# Runtime Data Product Docs Refresh

Date: 2026-07-07

## Summary

#409 refreshes permanent product documentation for the live runtime-data no-code boundary.

The earlier operator/admin wording made the boundary visible on the Source Output detail page. This slice carries the same product shape into the repository entry docs so new readers do not need to reconstruct it from dated runtime-data reports.

## Updated Docs

- `README.md` now lists current/alias read-only live `runtime-data.json` as part of the current no-code capability boundary while preserving static artifact-key previews.
- `docs/no-code-tryout.md` now explains that Refresh on current/alias previews fetches read-only live runtime data, while artifact-key previews keep static inspection behavior.
- `docs/overview.md` now includes the current/alias runtime-data read path in the no-code layer position.
- `docs/README.md` now describes the tryout as sitting on database-first metadata, Source Output, approval workflow, managed-operation outbox, and read-only live runtime data.

## Preserved Boundaries

- No runtime code changed.
- No route behavior changed.
- No generated artifact behavior changed.
- No submit/outbox mutation behavior changed.
- No push or history rewrite was performed.

## Verification

- `git diff --check`
