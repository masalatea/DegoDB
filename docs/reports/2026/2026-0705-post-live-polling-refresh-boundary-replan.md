# Post-Live-Polling Refresh Boundary Replan

Status: `DONE`.

Date: 2026-07-05.

## Context

The live outbox status polling lane is complete through multi-profile verification. Runtime submit now shows accepted, pending/timeout, terminal `done`, and terminal `failed` / `needs_review` states.

The next tempting improvement is stronger "Refresh preview" behavior after an outbox item is processed. That needs a boundary check first, because the current public runtime preview is a generated artifact. A browser reload may reload the same artifact rather than a fresh post-processing data snapshot.

## Decision

Choose a manual result refresh boundary inventory before adding new refresh behavior.

## Questions To Settle

- Should `Refresh preview` mean simple browser reload of the current generated artifact?
- Should public runtime preview request a fresh runtime JSON/data snapshot without regenerating the artifact?
- Should processed results become visible only after an explicit regenerate/publish/current-revision step?
- Should demo-processing mode get a special visual refresh path that production async mode does not get?
- What should the UI say when a processed outbox item is done but the current artifact is still stale?

## Candidate Next Slice

#199 should record the current refresh mechanics and classify the safe first implementation path.

## Push Boundary

No push was performed.
