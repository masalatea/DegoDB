# 2026-0702 Docs Information Architecture Split Plan

Status: `DONE`

## Decision

Add a documentation information-architecture step after the no-code tryout polish lane.

DegoDB should keep the original database-first/toolkit identity clear, while presenting no-code as a strong layer on top of that foundation. The README and docs should not collapse these into one vague story.

The two-layer structure is part of the value proposition, not something to hide. The message should be that DegoDB no-code does not float above the database as a separate lightweight builder. It runs on top of existing DB schemas, canonical metadata, generated artifacts, shared contracts, Source Output review, and publish approval workflow.

In short: the no-code layer is credible because the database-first foundation remains visible.

## Planned Work

| Order | Work unit | Priority | Rough effort | Goal |
| --- | --- | --- | --- | --- |
| 67 | README/docs information architecture split | MUST | 0.5 - 1 day | Restructure entry docs so database-first usage remains explicit and no-code is presented as a separate upper layer. |

## Positioning Message

Use this theme when rewriting README/docs:

> DegoDB is a database-first development toolkit. Its no-code layer is built on the same canonical metadata, generated artifacts, Source Output workflow, and approval path, so generated apps remain inspectable, reviewable, and connected to the database foundation.

Japanese message:

> DegoDB は database-first の開発ツールです。その no-code layer は、同じ正本メタデータ、生成成果物、Source Output workflow、承認 path の上に載るため、生成アプリも inspect / review / regenerate でき、DB 基盤から切り離されません。

## Intended Documentation Shape

- Keep `database-first / existing database -> metadata -> generated artifacts` as the core DegoDB path.
- Add a clearly labeled `no-code on top of database metadata` path.
- Explicitly explain that the split is intentional: database-first foundation below, no-code experience above.
- Keep quickstart/use-case pages from implying that no-code replaces the core database tooling.
- Give no-code its own entry links and sample path, while cross-linking back to Source Output, managed operation, shared contract, and app-local packaging foundations.

## Boundaries

- This is documentation structure and messaging, not a product rename.
- Do not remove database-first positioning from the README.
- Do not overstate no-code maturity beyond the current sample28 Web preview / public runtime / app-local packaging boundaries.
- Keep implementation docs and historical reports intact; improve the entry/navigation layer first.
