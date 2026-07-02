# Post-schema-form runtime smoke no-code product goal replan

## Status

`DONE`

## Decision

Select `Schema-form consumer notes first slice` as the next small no-code product-facing implementation.

## Context

The schema-form comparison path now has static artifact coverage, Mtool-aware metadata, and a focused rjsf runtime smoke. The remaining handoff gap is human-readable guidance in the generated artifact itself.

## Candidates

| Candidate | First slice estimate | Decision | Reason |
| --- | --- | --- | --- |
| Schema-form consumer notes | 0.5 - 1 day | Selected | Best continuation after the runtime smoke. It documents that the probe is comparison-only, explains ownership boundaries, and helps consumers understand how to inspect JSON Schema / UI Schema without replacing the custom React bridge. |
| Generated runtime visual polish follow-up | 0.5 - 2 days | Deferred | Still useful for visible product quality, but less directly connected to the schema-form consumer handoff. |
| Retry audit trail | 0.5 - 2 days | Deferred | Useful for operator accountability, but not the next adapter confidence gap. |

## Boundary

In scope:

- generated `CONSUMER-NOTES.md` for the schema-form probe;
- structured `consumer_notes` in `schema-form-contract.json`;
- invariant/checker/foundation coverage;
- docs and focused verification.

Out of scope:

- product adoption of JSON Forms or rjsf;
- replacing the custom React bridge;
- visual builder work;
- server execution;
- transport or sync behavior.

## Verification

Planning/report update only. The selected implementation should use focused sample28 and smoke verification.
