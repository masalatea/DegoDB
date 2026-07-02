# Post-artifact parity notes no-code product goal replan

Status: `DONE`

Date: 2026-07-01

## Decision

Choose Adapter artifact checklist note as the next small no-code product-facing implementation.

## Context

React bridge/schema-form artifact parity notes now explain when to inspect `NO-CODE-REACT-BRIDGE` versus `NO-CODE-JSON-FORMS-PROBE`. The next small handoff gap is making that guidance actionable: required files, stable markers, and smoke commands should be visible in the generated notes themselves.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Runtime preview keyboard/action affordance polish | 0.5 - 2 days | Deferred. Useful, but consumer handoff clarity is already active and can be completed in one small slice. |
| Adapter artifact checklist note | 0.5 - 1 day | Selected. It directly follows parity notes and makes generated adapter handoff more actionable. |
| Retry audit trail | 0.5 - 2 days | Deferred. Accountability is useful, but less connected to the adapter handoff lane. |

## Scope

In scope:

- React bridge generated handoff checklist;
- schema-form probe generated handoff checklist;
- required files, stable markers, and smoke commands;
- sample28 and shared foundation assertions.

Out of scope:

- new artifact kind;
- new smoke command implementation;
- replacing React bridge;
- adopting JSON Forms or rjsf as product runtime;
- runtime behavior changes.

## Notes

This is a small generated documentation/contract slice. It does not add new runtime behavior; it makes the existing generated artifacts easier to consume and verify.
