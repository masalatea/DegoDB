# 2026-06-21 OSS / Consulting Readiness Inventory

## Status

- status: `CURRENT INVENTORY`
- created: `2026-06-21 JST`
- first docs package: `DONE`
- purpose: US / overseas task intake, open source adoption, consulting, and implementation support readiness を棚卸しする

## Summary

DegoDB は database-first / existing-database-first toolkit として、現行の生成基盤、sample、compatibility 境界、legacy reference 整理が揃い始めている。

次の論点は、単に機能を増やすことではなく、外部の利用者、OSS 採用者、または導入支援の相談相手が「何に使えるか」「どこまで current support か」「導入前に何を確認すべきか」「どの成果物を納品・引き継ぎに使えるか」を短時間で判断できる資料と機能を揃えること。

この inventory は、生成 output として未実装のものを完成済みには見せない。AI context / audit は、generator ができるまでは plan として扱う。

## Target Situations

| Situation | Need |
| --- | --- |
| Overseas / US task intake | English-first problem statement, supported scope, reproducible demo, and clear paid-support boundary. |
| OSS adoption | Quick local start, architecture overview, support matrix, contribution guide, security posture, and issue templates. |
| Consulting | Discovery checklist, client DB intake sheet, deliverable catalog, before / after artifact story, and risk classification. |
| Implementation support | Installation runbook, existing DB import runbook, generated output verification, handoff bundle, and operational backup / restore notes. |

## Current Assets

| Asset | Status | Notes |
| --- | --- | --- |
| README positioning | `DONE` | database-first / existing-database-first positioning exists. |
| Quickstart / start-here / choose-your-path | `DONE` | usable for developer onboarding, but not yet packaged as a consulting intake path. |
| Use cases | `DONE` | includes consulting and implementation support topics. |
| Compatibility and output support | `DONE` | has safe public wording and current / legacy / future boundaries. |
| Existing DB to output guide | `DONE` | current primary journey for import to generated output. |
| Examples index | `DONE` | scenario-oriented examples are separated from tutorial samples. |
| Tutorial sample lane | `DONE / MAINTAIN` | strong verification evidence, not yet summarized as an external proof matrix. |
| Project metadata bundle docs | `DONE` | useful handoff foundation. |
| Config DB externalization / storage docs | `DONE` | useful for team / enterprise operational setup. |
| AI context source output | `TODO` | planned as `AI-CONTEXT-MD`; do not claim current support. |
| Modernization audit generator | `TODO` | planned as `AUDIT-MD`; do not claim current support. |
| Goal-based wrapper CLI / help | `TODO` | currently listed as a roadmap item. |

## Missing Documentation Package

### 1. External Adoption Overview

Purpose: A short English-first document for people who are not already inside this repo.

Needed contents:

- what DegoDB is and is not;
- current supported DB / output language boundaries;
- strongest use cases: existing DB import, generated PHP DataClass / DBAccess, OpenAPI / proxy / bundle output, modernization preparation;
- expected user profile: developer, AI-assisted engineer, consultant, migration lead;
- safe public message copied from compatibility docs.

Candidate location:

- `docs/oss-adoption.md` or `docs/adoption-guide.md`

### 2. Consulting Intake Checklist

Purpose: Prepare a paid or collaborative engagement without overpromising support.

Needed contents:

- client / project context;
- source DB type and access method;
- schema size and sensitive-data handling;
- existing application framework and generated-output target;
- expected deliverables;
- support boundary for non-PHP output, SQL Server, Oracle, compliance, and billing/tax domain work;
- minimum artifact package for handoff.

Candidate location:

- `docs/consulting-intake.md`

### 3. Deliverable Catalog

Purpose: Make consulting / implementation support concrete.

Candidate deliverables:

- schema inventory and relationship notes;
- canonical metadata import result;
- generated DataClass / DBAccess output;
- OpenAPI / proxy / HTML / metadata bundle output where applicable;
- before / after modernization explanation;
- project metadata bundle and secret template;
- verification report from sample / contract gates;
- AI context or audit report only after generators exist.

Candidate location:

- `docs/deliverables.md`

### 4. Proof Matrix

Purpose: Convert existing samples and tests into external trust evidence.

Needed contents:

- sample tutorial coverage table;
- DB layer matrix: MySQL / MariaDB, SQLite config store, SQLite user DB contract, PostgreSQL user DB contract;
- generated artifact matrix: DataClass, DBAccess, OpenAPI, HTML, authenticated proxy, metadata bundle;
- current / legacy / future labels.

Candidate location:

- extend `docs/compatibility-and-output-support.md` or add `docs/proof-matrix.md`

### 5. Security / Data Handling Brief

Purpose: Make early enterprise / consulting conversations possible without pretending full enterprise certification.

Needed contents:

- config DB vs user DB boundary;
- secret separation and metadata bundle secret template;
- auth / generated runtime security baseline summary;
- sensitive-data handling rule for client schemas and seed data;
- synthetic examples policy;
- what is not currently certified or guaranteed.

Candidate location:

- `docs/security-and-data-handling.md`

### 6. Contribution / Support Boundary

Purpose: OSS users need to know how to ask for help and what support means.

Needed contents:

- issue categories;
- bug report reproduction expectations;
- sample / contract gate expectations;
- supported environment;
- consulting / paid support boundary if publicized;
- roadmap items that are plans, not support claims.

Candidate location:

- `CONTRIBUTING.md`, `.github/ISSUE_TEMPLATE/*`, or `docs/support.md`

## Missing Feature Package

### 1. Goal-Based Wrapper CLI / Help

Status: already in current plans.

Why it matters:

- external users should not need to memorize Make targets;
- consultants need repeatable commands for intake, inspect, generate, audit, doctor, and bundle handoff;
- AI assistants need a stable command surface.

Candidate commands:

- `mtool init`
- `mtool inspect`
- `mtool import`
- `mtool generate`
- `mtool verify`
- `mtool bundle`
- `mtool doctor`
- `mtool audit` after `AUDIT-MD` exists

### 2. AI Context Source Output

Status: already in current plans.

Why it matters:

- useful for AI-assisted implementation support;
- lets a client or OSS adopter hand a stable project context to another engineer or assistant;
- should be generated from canonical metadata, not hand-written as if generated.

### 3. Modernization Audit Generator

Status: already in current plans.

Why it matters:

- converts consulting discovery into a repeatable artifact;
- separates schema-backed facts from assumptions;
- can become the first paid-support / implementation-support deliverable.

### 4. Scenario-Specific Actual Output Examples

Status: needed after generator wiring.

Why it matters:

- `examples/laravel-mysql-legacy/`, `examples/postgresql-existing-schema/`, and `examples/sqlite-api-generation/` are useful intake stories;
- scenario-specific generated output would make demos stronger;
- `reference/` must contain only actual Mtool output.

### 5. One-Command Demo / Doctor

Status: planned as part of wrapper CLI or Make convenience.

Why it matters:

- external evaluation should have a short green path;
- consulting setup can quickly distinguish Docker / PHP / DB / port / config DB problems;
- useful before paid discovery sessions.

### 6. Sanitized Client-Schema Intake Flow

Status: needed for consulting readiness.

Why it matters:

- real client schemas may contain table / column names that reveal business details;
- current examples are synthetic, which is good, but consulting needs a documented anonymization / redaction path;
- the output should preserve relationship and type facts while removing sensitive identifiers when required.

### 7. Security / Authorization Hardening Replan

Status: parked until concrete deployment need.

Why it matters:

- not the next default implementation unit;
- if a US / enterprise deployment conversation becomes real, admin / lab route authorization, deployment profile, and operational controls should be replanned with tests.

## Recommended Work Units

These are commit-sized or near commit-sized units.

| Order | Work unit | Status | Commit unit |
| --- | --- | --- | --- |
| 1 | OSS / consulting readiness docs package | `DONE` | `Document OSS consulting readiness package` |
| 2 | External proof matrix | `DONE_WITH_1` | Included in [Proof Matrix](../../proof-matrix.md) |
| 3 | AI context source output | `ACTIVE_NEXT` | `Generate AI context source output` |
| 4 | Modernization audit MVP generator | `TODO` | `Generate modernization audit output` |
| 5 | Goal-based wrapper CLI roadmap | `TODO` | `Document goal-based help and wrapper CLI roadmap` |
| 6 | Scenario-specific generated example output | `LATER` | Generate actual example outputs after source-output support exists |
| 7 | Deployment security hardening replan | `CONDITIONAL` | Replan admin / lab route authorization when deployment need is concrete |

## Suggested First Slice

The first slice is documentation-only and does not change support claims.

Completed contents:

1. [Adoption Guide](../../adoption-guide.md)
2. [Consulting Intake](../../consulting-intake.md)
3. [Deliverables](../../deliverables.md)
4. [Proof Matrix](../../proof-matrix.md)
5. [Security And Data Handling](../../security-and-data-handling.md)
6. `docs/current-plans.md` updated to move the completed docs package into history and return the next active lane to AI context source output.

## Guardrails

- Do not claim US legal, tax, billing, HIPAA, SOC 2, or procurement readiness without domain review.
- Do not present Japanese invoice / billing / compliance examples as expertise until reviewed.
- Do not claim non-PHP generated output as current support.
- Do not claim SQL Server / Oracle support as current support.
- Do not treat AI context or modernization audit as current generated output until generators exist.
- Do not place hand-written drafts under generated-looking `reference/` or `generated/` directories.
- Keep consulting examples synthetic unless a client explicitly approves sanitized publication.
