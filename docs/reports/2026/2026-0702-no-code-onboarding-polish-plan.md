# 2026-0702 No-Code Onboarding Polish Plan

Status: `DONE`

## Decision

After the sample28 Docker tryout and first user-facing guide, the next onboarding polish lane should start with seeded preview data.

## Plan

| Order | Work unit | Priority | Rough effort | Rationale |
| --- | --- | --- | --- | --- |
| 62 | sample28 seeded preview data | MUST | 0.5 - 1 day | The current preview works, but the list screen starts empty. Seeded records make the generated app feel alive immediately. |
| 63 | preview data smoke | MUST | 0.5 day | The seeded-data experience should be locked by runtime/browser smoke coverage, not just manual observation. |
| 64 | operator wording polish | SHOULD | 0.5 - 1 day | Current UI terms are correct but developer-heavy for a first-time operator. |
| 65 | one-click tryout action design | SHOULD | 0.5 day | A demo shortcut could help, but it touches approval semantics and should be designed before implementation. |
| 66 | one-click tryout implementation | LATER | 1 - 2 days | Implement only after the demo-only boundary, audit trail, and production guard are explicit. |

## Why Seeded Data First

The public preview already reaches a meaningful page, but `No records to show yet.` weakens the first impression. A few realistic ticket rows should improve the experience faster than changing approval mechanics or adding a shortcut.

## Boundaries

- Keep this first lane focused on sample28 and the generated no-code runtime preview.
- Do not bypass the publish candidate approval workflow.
- Treat one-click tryout as a later, explicitly demo-scoped feature.
- Keep app-local packaging as a separate scenario.

## Verification Expectation

The seeded data implementation should include a focused smoke that proves the runtime preview displays the seeded ticket rows.
