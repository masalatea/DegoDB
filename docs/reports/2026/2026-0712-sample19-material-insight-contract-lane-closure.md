# Sample19 material insight contract lane closure

Date: 2026-07-12

## Summary

#809 closes the Sample19 `material_insight_v0` contract foundation.

#808 added a fixture-backed builder and validator that:

- reuses the existing Sample19 source/proposal/canonical inputs;
- derives normalized entity summaries;
- derives bounded Q&A cards;
- derives a read-only UI outline;
- binds source and canonical hashes;
- rejects broken Q&A/UI references;
- rejects unsafe UI actions and missing prohibitions;
- keeps `mutation_performed=false`;
- passed full `make test`.

## Decision

Promote a read-only fixture preview preflight next.

Additional validator hardening can happen later if a concrete preview or browser route exposes a missing reference/failure shape. At this point the better product evidence is whether the same normalized material insight artifact can be rendered for a user as Q&A cards plus a UI outline while preserving the no-mutation boundary.

## Next lane

#810: Sample19 material insight read-only preview preflight.

It should define:

- default-off feature flag;
- authenticated Sample19 route or fixture preview boundary;
- exact loader inputs and hash checks;
- HTML markers for source, basis, Q&A cards, UI outline, and prohibited actions;
- no POST, no apply/import/build/publish, no metadata mutation, no AI call;
- fast tests first, browser smoke only after route implementation.
