# Post-adapter checklist notes no-code product goal replan

Status: `DONE`

Date: 2026-07-01

## Decision

Choose Adapter artifact troubleshooting notes as the next small no-code product-facing implementation.

## Context

Adapter artifact checklist notes made required files, stable markers, and smoke commands visible in the generated React bridge and schema-form probe artifacts. The next small handoff gap is what to inspect when those handoff checks fail.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Runtime preview keyboard/action affordance polish | 0.5 - 2 days | Deferred. Useful, but adapter handoff clarity can be rounded off with one more small slice. |
| Adapter artifact troubleshooting notes | 0.5 - 1 day | Selected. It directly follows the checklist notes and helps consumers debug generated adapter artifacts. |
| Retry audit trail | 0.5 - 2 days | Deferred. Accountability is useful, but less connected to the adapter handoff lane. |

## Scope

In scope:

- React bridge troubleshooting notes;
- schema-form probe troubleshooting notes;
- structured contract metadata;
- generated `CONSUMER-NOTES.md`;
- sample28 and shared foundation assertions.

Out of scope:

- new artifact kind;
- new smoke commands;
- React bridge replacement;
- JSON Forms/rjsf product runtime adoption;
- runtime behavior changes.

## Notes

This is a generated documentation/contract slice. It keeps the troubleshooting advice near the generated artifacts that consumers inspect.
