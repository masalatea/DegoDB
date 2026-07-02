# Post-runtime preview accessibility polish no-code product goal replan

Status: `DONE`

Date: 2026-07-01

## Decision

Choose React bridge/schema-form artifact parity notes as the next small no-code product-facing implementation.

## Context

Runtime preview accessibility polish added generated landmarks, labelled screen regions, action nav labels, and list table captions. The generated preview surface is now easier to inspect visually and semantically.

The remaining small product-facing gap is consumer handoff clarity between the custom React bridge artifact and the schema-form comparison probe. Both artifacts now have consumer notes, but readers still benefit from explicit guidance on which artifact to inspect for which question.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| React bridge/schema-form artifact parity notes | 0.5 - 1 day | Selected. It tightens handoff clarity without changing runtime behavior. |
| Runtime preview keyboard/action affordance polish | 0.5 - 2 days | Deferred. Useful, but less immediate after the first accessibility pass. |
| Retry audit trail | 0.5 - 2 days | Deferred. Accountability is useful, but it is less connected to the just-completed runtime preview accessibility work. |

## Scope

In scope:

- generated parity notes in React bridge `consumer_notes`;
- generated parity notes in schema-form probe `consumer_notes`;
- `CONSUMER-NOTES.md` sections for both artifacts;
- focused sample28 and shared foundation assertions.

Out of scope:

- new artifact kind;
- replacing the custom React bridge;
- adopting JSON Forms or rjsf as product runtime;
- runtime behavior changes;
- action execution behavior changes.

## Notes

This is deliberately a documentation/contract slice inside generated artifacts. It should answer the practical question: inspect React bridge for custom adapter behavior and action-intent emission; inspect schema-form probe for JSON Forms/rjsf comparison and schema metadata.
