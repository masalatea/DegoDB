# 2026-06-21 Database-first Sales Assets Plan

## Status

- status: `REVISED`
- created: `2026-06-21 JST`
- revised: `2026-06-21 JST`
- purpose: database-first / existing-database-first positioning を、実装済み機能と将来候補を混ぜずに整理する

## Summary

DegoDB の説明は、database-first / existing-database-first toolkit として整理する。

ただし、実際に Mtool が生成していない output を `generated/` や `reference/` に置かない。AI が作った固定の期待出力を、生成済み artifact のように見せない。

## Output Placement Rule

| Directory | Allowed contents |
| --- | --- |
| `sample/tutorials/*/reference/` | Actual Mtool generated output only. |
| `examples/*/reference/` | Actual Mtool generated output only, after the example is wired to Mtool. |
| `examples/*/schema/`, `examples/*/legacy/`, `examples/*/checks/` | Input schema, seed data, and runnable baseline or smoke checks. |
| `examples/*/mtool/` | Import notes, generation plan, and handoff notes before actual output exists. |
| `docs/reports/` | Design notes, future source output ideas, roadmap, and status. |

## Current Asset Scope

| Item | Status | Artifact |
| --- | --- | --- |
| README positioning | `DONE` | `README.md` |
| Use case guide | `DONE` | `docs/use-cases.md` |
| Compatibility and output support | `DONE` | `docs/compatibility-and-output-support.md` |
| Current plan index | `DONE` | `docs/current-plans.md` |
| Examples index | `DONE` | `examples/README.md` |
| Laravel + MySQL legacy input baseline | `DONE` | `examples/laravel-mysql-legacy/` |
| PostgreSQL existing schema input draft | `DONE` | `examples/postgresql-existing-schema/` |
| PostgreSQL actual-output backing | `DONE_WITH_INPUT` | `examples/postgresql-existing-schema/mtool/actual-output.md` |
| SQLite API generation input draft | `DONE` | `examples/sqlite-api-generation/` |
| SQLite actual-output backing | `DONE_WITH_INPUT` | `examples/sqlite-api-generation/mtool/actual-output.md` |
| Curated legacy mtool references | `DONE` | `mtool/reference/legacy-mtool-build/`, `mtool/reference/legacy-mtool-templates/` |

## Active Next Work

1. AI context source output
   - Implement `AI-CONTEXT-MD` before adding generated AI context examples.
   - Do not store hand-written AI context under generated-looking directories.

2. Modernization audit MVP generator
   - Implement an audit generator before adding generated audit examples.
   - Keep audit ideas in this report until actual output exists.

## Planned AI Context Source Output

Status: `TODO_SOURCE_OUTPUT`

This is a plan, not current product documentation. / これは計画であり、現在の正式機能文書ではない。

Candidate output key: `AI-CONTEXT-MD`

Candidate shape:

```text
AI-CONTEXT-MD/
  README.md
  schema-summary.md
  tables/
    customers.md
    invoices.md
  relationships.md
  risky-areas.md
  generation-map.md
  agent-instructions.md
  schema-context.json
```

Candidate rules:

- Treat the database schema as the source of truth.
- Do not rename, drop, or rewrite production tables without an explicit migration plan.
- Prefer additive changes when current behavior is unclear.
- Check generated DataClass and DBAccess artifacts before writing custom SQL.
- Document assumptions before changing code that depends on implicit relationships.

Do not add examples for this output until `AI-CONTEXT-MD` is implemented through the existing source output and verification model.

## Planned Modernization Audit Generator

Status: `TODO_GENERATOR`

This is a plan, not current product documentation. / これは計画であり、現在の正式機能文書ではない。

Candidate output key: `AUDIT-MD`

Candidate shape:

```text
AUDIT-MD/
  summary.md
  table-inventory.md
  risks.md
  relationship-candidates.md
  normalization-notes.md
  ai-readiness.md
  modernization-next-steps.md
```

Candidate principles:

- Separate facts from assumptions.
- Prefer schema-backed statements.
- Mark inferred relationships as candidates until reviewed.
- Do not recommend destructive migration without explicit review.
- Connect every recommendation to an artifact, check, or human decision.

Candidate risk categories:

| Category | Examples |
| --- | --- |
| Privacy | email, customer notes, uploaded content, payment references |
| Public API exposure | internal-only fields, admin-only records, raw IDs |
| Migration risk | table rename, type narrowing, nullable-to-required changes |
| Relationship uncertainty | implicit foreign keys, duplicated names, missing constraints |
| Generated output risk | unsupported SQL pattern, unsafe physical name, missing source metadata |
| Domain review needed | billing, tax, compliance, permissions, approval workflows |

Do not add generated audit examples until an audit generator exists and uses canonical metadata plus explicit review labels.

## Parked

- Japanese invoice / billing / compliance sample until domain review is available.
- SQL Server / Oracle user DB examples until support scope is explicit.
- Generated AI context and audit examples until their generators exist.

## Guardrails

- Do not use `generated/` in `examples/` for hand-written drafts.
- Do not call a scenario `DONE` as generated output unless Mtool produced the output.
- Do not copy unrelated sample output into a scenario just to fill an example.
- Prefer an input draft plus clear next step over a convincing placeholder.
