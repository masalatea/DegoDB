# Database-First Plus No-Code Product Narrative Docs / database-first + no-code product narrative docs

Status: `FIRST_SLICE_DONE`

Date: 2026-07-05

## Summary

#181 updates the permanent product-facing docs so DegoDB keeps its database-first identity while explaining the no-code layer as a real upper layer on that foundation.

The narrative is deliberately two-layered:

- foundation: database schema -> canonical metadata -> Data Class / DB Access -> Source Output;
- no-code layer: canonical metadata -> managed operation -> no-code runtime -> publish candidate -> public preview.

The point is not that no-code replaces the database tooling. The point is that no-code is more solid because it inherits reviewed database metadata, generated artifacts, managed-operation intent boundaries, Source Output review, and approval records.

## Updated Docs

- `README.md`
  - Strengthened the two-layer model and added the current no-code capability boundary.
- `docs/no-code-tryout.md`
  - Added why the foundation matters, sample29 as the second-domain reference, runtime submit / outbox / refresh behavior, and the demo-only synchronous processing boundary.
- `docs/overview.md`
  - Added the conceptual placement of the no-code layer above Source Output and managed operations.
- `docs/use-cases.md`
  - Added no-code on database metadata as a practical use case with current boundaries.
- `docs/README.md`
  - Updated index wording so readers understand no-code tryout as a database-first / Source Output / approval / outbox path.

## Boundary

- This is a documentation slice only.
- It does not claim no-code is detached from the database-first model.
- It does not claim synchronous processing is the production default.
- It keeps unsupported deployment and domain claims out of permanent docs.

## Verification

- `git diff --check`

Push was not performed.
