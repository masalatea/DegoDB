# Post-fresh-runtime-data Endpoint Replan

Status: DONE
Date: 2026-07-05

## Decision

Choose current/alias UI consumption of `runtime-data.json` as the next product-facing slice.

## Why This Next

The endpoint is now proven across sample28, sample29, and sample31, but the visible runtime preview still behaves like an artifact reload surface. The next useful step is to let the current/alias public page explicitly know where its read-only live data endpoint is, then use that endpoint from the existing Refresh affordance.

## Scope

- Inject a `runtime_data_url` binding only on authenticated current/alias public runtime pages.
- Keep immutable artifact-key preview unchanged.
- Keep `runtime-preview.html` and `runtime-preview.json` as generated artifacts, not live-data stores.
- Start with explicit user Refresh, not automatic polling or post-submit reload.
- Fail closed with visible status text if `runtime-data.json` is unavailable or has an unexpected contract.

## Out Of Scope

- Automatic post-submit data reload.
- Artifact regeneration.
- Current revision switching.
- Outbox processing inline from the data refresh.
- Pagination/filter read-model expansion.

## Next Slice

Implement the first UI consumption slice: public current/alias runtime pages should inject the data URL and make Refresh fetch `runtime-data.json` before falling back to the older artifact reload behavior.

Push was not performed.
