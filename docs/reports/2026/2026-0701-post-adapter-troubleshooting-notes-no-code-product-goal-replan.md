# Post-adapter troubleshooting notes no-code product goal replan

Status: `DONE`

Date: 2026-07-01

## Decision

Choose Adapter consumer doc index note as the next small no-code product-facing implementation.

## Context

Adapter artifact troubleshooting notes now cover common React bridge and schema-form probe handoff failures. The generated notes have accumulated several useful sections: parity, checklist, troubleshooting, stable markers, action intent, and generated files. The next small gap is a compact index that tells consumers how to read those sections.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Runtime preview keyboard/action affordance polish | 0.5 - 2 days | Deferred. Useful, but adapter handoff docs can be finalized as one readable package first. |
| Adapter consumer doc index note | 0.5 - 1 day | Selected. It ties the recent parity/checklist/troubleshooting notes together. |
| Retry audit trail | 0.5 - 2 days | Deferred. Accountability is useful, but less connected to the adapter handoff lane. |

## Scope

In scope:

- generated documentation index notes;
- React bridge consumer notes/contract;
- schema-form probe consumer notes/contract;
- sample28 and shared foundation assertions.

Out of scope:

- new artifact kind;
- new smoke commands;
- React bridge replacement;
- JSON Forms/rjsf product runtime adoption;
- runtime behavior changes.

## Notes

This is a small generated documentation/contract slice. It makes the adapter handoff notes easier to consume as a package.
