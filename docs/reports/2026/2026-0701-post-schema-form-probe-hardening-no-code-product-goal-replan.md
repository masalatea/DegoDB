# Post-schema-form probe hardening no-code product goal replan

## Status

`DONE`

## Decision

Select `Schema-form runtime smoke first slice` as the next small no-code product-facing implementation.

## Context

The JSON Forms / rjsf comparison artifact now has Mtool extension metadata, action-field role hints, client-write hints, UI Schema options, and checker coverage.

The next useful question is whether the emitted JSON Schema and UI Schema can be consumed by a schema-form style renderer without making Mtool own that renderer as product code.

## Candidates

| Candidate | First slice estimate | Decision | Reason |
| --- | --- | --- | --- |
| Schema-form runtime smoke | 1 - 3 days | Selected | Strongest continuation after static schema-form artifacts. It proves consumer viability while keeping the custom React bridge as the default product adapter. |
| Generated runtime visual polish follow-up | 0.5 - 2 days | Deferred | Useful, but less directly connected to the just-hardened schema-form probe. |
| Retry audit trail | 0.5 - 2 days | Deferred | Still useful for operator accountability, but not the next no-code adapter confidence gap. |

## Boundary

In scope:

- a tiny non-product runtime smoke for the emitted schema-form probe artifacts;
- sample28-focused verification;
- generated artifact contract/readability checks;
- documentation and plan updates.

Out of scope:

- replacing the custom React bridge;
- adding JSON Forms / rjsf as product runtime code;
- visual builder work;
- server execution;
- transport or sync behavior.

## Estimate Note

Rough estimate remains 1 - 3 days because dependency/install friction, renderer assumptions, and smoke stability can vary. If existing generated artifacts already satisfy the renderer path, the actual implementation may be much shorter, similar to the recent React bridge smoke slices.

## Verification

Planning/report update only. The selected implementation should add focused smoke verification.
