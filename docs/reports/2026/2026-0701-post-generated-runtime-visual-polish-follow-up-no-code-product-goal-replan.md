# Post-generated runtime visual polish follow-up no-code product goal replan

Status: `DONE`

Date: 2026-07-01

## Decision

Choose Runtime preview accessibility polish as the next small no-code product-facing implementation.

## Context

Generated runtime visual polish follow-up added compact field/action/screen-key summaries to `runtime-preview.html`. The visible runtime surface is now easier to scan, but the semantic structure still had a small gap: generated screens, list tables, and action navigation could expose clearer labels for assistive tools and DOM-level inspection.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| React bridge/schema-form artifact parity notes | 0.5 - 1 day | Deferred. Useful for handoff clarity, but less directly connected to the just-polished runtime preview surface. |
| Runtime preview accessibility polish | 0.5 - 2 days | Selected. It continues the visible runtime quality lane while keeping behavior stable. |
| Retry audit trail | 0.5 - 2 days | Deferred. Accountability is useful, but it is less immediate than tightening generated preview semantics. |

## Scope

In scope:

- generated runtime preview landmarks;
- labelled screen regions;
- list table captions;
- action navigation labels;
- focused PHPUnit, sample checker, and browser smoke coverage.

Out of scope:

- full WCAG audit;
- keyboard interaction redesign;
- visual builder;
- action execution behavior changes;
- React bridge or schema-form runtime behavior changes.

## Notes

This is intentionally a small quality slice. It should make the generated preview more inspectable and more accessible without introducing new metadata tables or changing runtime intent behavior.
