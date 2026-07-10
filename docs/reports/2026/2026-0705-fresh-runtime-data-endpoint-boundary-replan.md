# Fresh Runtime Data Endpoint Boundary Replan

Status: `DONE`.

Date: 2026-07-05.

## Context

The runtime now has submit, outbox tracking, terminal status feedback, and clarified refresh wording. The remaining product gap is fresh business-data visibility after processing. The current generated preview artifact does not solve that by itself.

## Decision

Choose a fresh runtime data endpoint boundary inventory before implementing live data reload behavior.

## Why Not Implement Immediately

A fresh data endpoint would cross several boundaries:

- Which data source should it read: generated DBAccess, runtime preview JSON, current public revision, or a read-model-specific endpoint?
- Which auth model should apply: the same current/alias public runtime execution boundary, operator session, project-scoped public token, or a read-only policy?
- What cache behavior should it use when current/alias previews are `no-store` but artifact-key previews are immutable?
- What response shape should preserve the generated runtime contract without becoming a hand-built API per sample?
- How should the UI explain stale generated artifact data versus fresh endpoint data?

## Candidate Next Slice

#204 should inventory the current generated runtime data model, current/alias route behavior, DBAccess/read-model bindings, and safe first endpoint shape.

## Push Boundary

No push was performed.
