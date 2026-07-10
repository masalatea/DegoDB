# Manual Result Refresh Boundary Inventory

Status: `DONE`.

Date: 2026-07-05.

## Current Mechanics

Generated runtime preview embeds the runtime model in `runtime-preview.html` as `#no-code-runtime-preview-data`.

The public runtime preview route serves the approved candidate artifact's `runtime-preview.html` from the artifact bundle runtime root. Current and alias routes resolve to an approved/current candidate, then serve the same generated artifact with an injected execution binding.

`Refresh preview` currently:

- enables after successful submit;
- stores current screen form values in `sessionStorage`;
- calls `window.location.reload()`;
- restores the form values after reload.

It does not:

- fetch fresh DB rows;
- re-run the outbox processor;
- regenerate `runtime-preview.json` / `runtime-preview.html`;
- publish or select a new current runtime revision.

## Existing Proofs

The public runtime browser smokes prove:

- current/alias submit enqueues pending sync outbox work;
- live status JSON can report pending/timeout, `done`, and `failed` / `needs_review`;
- outbox processing smoke can process queued direct endpoint payloads through generated server DBAccess and update an isolated SQLite row.

These are intentionally separate proofs. They do not prove that a browser reload of the existing artifact shows processed DB changes.

## Product Boundary

The current `Refresh preview` is a page/artifact reload affordance, not a fresh-data query.

That is acceptable for the first slice, but wording should avoid implying that processed data will always appear after clicking refresh. The no-code runtime still rests on generated artifacts, explicit publish/current selection, and the operation/outbox processor boundary.

## Recommended Next Implementation

Add wording/affordance clarification:

- Make the refresh status say it reloads the current preview artifact.
- Keep the outbox detail path as the authoritative place to inspect processing status.
- Mention that fresh business data may require a regenerated/published runtime preview or a future live data endpoint.

Do not add a fresh-data endpoint until the data-source/auth/cache boundary is explicit.

## Future Options

- Fresh runtime data endpoint backed by generated DBAccess/read model.
- Regenerate/publish/current-revision workflow after processing.
- Demo-only refresh behavior behind the existing synchronous demo gate.

## Push Boundary

No push was performed.
