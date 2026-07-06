# Next No-Code Direction After Push / push 後の次 no-code 方向性

Status: `DONE`

Date: 2026-07-05

## Context

The grouped no-code runtime submit / outbox / second-domain confidence stack has been pushed to `origin/develop`. sample28 and sample29 now both prove public runtime browser submit, direct current/alias endpoint enqueue, operator outbox handoff affordances, and generated server DBAccess outbox processing against isolated SQLite rows.

## Decision

The next major candidates are all needed. They should not be treated as mutually exclusive options. The plan is to sequence them as a single mainline so each step improves the ground for the next one.

## Mainline Sequence

1. No-code practical runtime flow polish
   - Make the current submit / outbox / processing / refresh path feel usable from the runtime surface.
   - Reduce the current developer-oriented handoff.
   - First-slice estimate: 0.5 - 1.5 days.

2. Synchronous demo processing first slice
   - Add a safe demo / tryout path where submit can process and refresh immediately.
   - Preserve async outbox as the production foundation.
   - First-slice estimate: 1 - 2 days.

3. Database-first plus no-code product narrative docs
   - Update README/docs so the two-layer message is explicit: database-first tooling is the foundation, and no-code runs on top of canonical metadata, generated artifacts, approval flow, and managed operations.
   - First-slice estimate: 0.5 - 1 day.

4. Next domain/sample expansion
   - Prove the runtime flow against another product-facing domain after the practical flow and docs are clearer.
   - First-slice estimate: 2 - 5 days.

## Rationale

The current implementation is already beyond a static preview: it can submit, enqueue, expose handoff affordances, and prove processing for two domains. The weakest part is now the practical user flow after submit. Polishing that first makes the existing capability feel coherent. Synchronous demo processing then gives the project a stronger tryout story without weakening the async foundation. Docs and the next sample should follow once the product behavior is easier to explain and demonstrate.

## Boundary

This is a planning update only. No runtime code change is included in this report.
