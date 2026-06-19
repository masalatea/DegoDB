# 2026-06-19 Plan Inventory

## Status

- status: `CURRENT INVENTORY`
- created: `2026-06-19 18:15 JST`
- purpose: 現時点の plan / roadmap / resume prompt を棚卸しし、実装済み・待機中・次に触るものを分ける

## Executive Summary

現時点の active mainline は、5 月の broad rewrite parent plan ではなく、6 月後半に固まった以下の 4 本として読む。

1. `sample01-26` tutorial / demo lane の維持と、今後の sample 追加ポリシー。
2. Mtool config store の MySQL / MariaDB default + SQLite lightweight profile の維持。
3. user DB / generated output 側の multi-dialect framework 拡張。
4. security / audit / permission / auth v2 を、SQLite/config-store 安定後の次期 feature foundation として進める。

5 月の broad rewrite / self-host 計画は、current emitted runtime contract の整理と verification freeze まで完了済みの履歴として扱う。再開時の参照価値は残るが、今日の実装順を決める active parent ではない。

## Current Plan Sources

| Document | Current Reading | Status |
| --- | --- | --- |
| `docs/sample-tutorial-roadmap.md` | tutorial lane の正本。`sample01-26` は current。今後 sample を増やす場合はここへ追加する。 | `ACTIVE / CURRENT` |
| `docs/reports/2026/2026-0619-instruction-driven-demo-sample-plan.md` | `sample18` の instruction-driven demo lane。first candidate は完了済み。 | `DONE` |
| `docs/reports/2026/2026-0619-ebook-headless-cms-sample-plan.md` | `sample19-26` の ebook / headless CMS lane。runtime pack、reference output、checker、study guide、比較表、tutorial 導線まで含めて完了済み。今後は production CMS ではなく tutorial scope として維持する。 | `SAMPLE19_26_DONE_AS_TEACHING_SAMPLE` |
| `docs/reports/2026/2026-0617-lightweight-sqlite-persistence-plan.md` | Mtool 自身の SQLite config store 対応は current scope で完了扱い。user DB 側 SQLite は dialect framework 側で継続。 | `MTOOL-SIDE DONE / USER-DB ONGOING` |
| `docs/reports/2026/2026-0617-user-db-multidb-dialect-roadmap.md` | user DB 側の MySQL / MariaDB mainline + SQLite first expansion の正本。PostgreSQL / SQL Server は parked。first stop-line は sample06/08/09/10 contract で完了済み。 | `FIRST STOP-LINE DONE` |
| `docs/reports/2026/2026-0617-http-runtime-smoke-plan.md` | sample13 / sample16 の HTTP / browser smoke は完了。次は auth-required OpenAPI operation と future bearer auth 表現の整理。 | `FIRST SLICES DONE` |
| `docs/reports/2026/2026-0617-enterprise-personal-feature-plan.md` | security / audit / permission / backup / safe local persistence の優先順位整理。次の first slice は security regression gate + audit log foundation。 | `NEXT FOUNDATION DECIDED` |
| `docs/reports/2026/2026-0617-api-auth-inventory-plan.md` | API auth v2 の設計正本。`Phase 1. Policy Contract` は storage / validation 方針まで準備済みで、runtime 実装は security foundation と合わせて進める。 | `POLICY CONTRACT READY / IMPLEMENTATION WAITING` |
| `docs/reports/2026/2026-0617-json-to-db-optional-entrance-roadmap.md` | JSON-first optional entrance の思想と AI contract。`sample19` で tutorial bridge を実装済み。 | `DONE / CONCEPT AVAILABLE` |
| `docs/mtool-positioning.md` | ORM 類似ツールとの比較の living doc。Mtool は ORM 置換より metadata-driven output generator として読む。 | `ACTIVE REFERENCE` |

## Superseded Or Historical Sources

| Document | Current Reading |
| --- | --- |
| `docs/reports/2026/2026-0507-rebuild-plan.md` | 初期再構築 scope の履歴。現在の active plan ではない。 |
| `docs/internal/mtool-admin-roadmap.md` | 旧 Mtool 移植と self-host の基準文書。進捗値は古い見立てを含むため、最新実装順は 6 月 report と合わせて読む。 |
| `docs/reports/2026/2026-0527-broad-rewrite-temporary-closure-plan.md` | broad rewrite current wave は `DONE`。supporting reference。 |
| `docs/reports/2026/2026-0528-resume-prompt.md` | 5/28 stop point の再開 prompt。runtime contract freeze の確認には有用だが、今日の active task list ではない。 |
| `docs/reports/2026/2026-0525-openapi-auth-persistence-plan.md` | Phase 0/1/3/4 は done。auth 系の未完は 6/17 API auth inventory へ引き継ぐ。 |

## Done Inventory

- `sample01-26` tutorial / demo packs are current in `docs/sample-tutorial-roadmap.md`.
- `sample18` instruction-driven task board demo is implemented with runtime test, SQLite runtime test, and HTTP smoke.
- `sample19-26` ebook / headless CMS lane is complete as teaching-sample scope, including runtime packs, checks, references, study guide, comparison table, and tutorial links.
- Mtool config store SQLite lightweight profile is complete for current Mtool-side scope:
  - folder-backed `APP_CONFIG_STORE_DIR`
  - bootstrap / migrate / preflight
  - backup / restore / rotation
  - `mtool-lite-smoke`
  - sample01-17 dual-profile gate and artifact parity
- representative generated runtime HTTP smoke is implemented:
  - sample13 OpenAPI viewer / proxy route / browser Try It Out
  - sample16 authenticated proxy fail-closed route smoke
- 5 月 broad rewrite / runtime contract truth normalization / wrapper-base migration / verification freeze is done.
- `docs/internal/mtool-admin-roadmap.md` has a 2026-06-19 reading note, so its old 2026-05-15 progress wording is no longer an active-plan trap.
- user DB dialect first stop-line is complete across sample10 CRUD, sample06 filter/sort/page, sample08 join read model, and sample09 aggregate report contracts.

## Active Remaining Work

None of the items below are remaining work for the completed `sample19-26` ebook CMS teaching-sample lane. That lane is done. user DB dialect first stop-line is also done; future dialect work resumes only when a new user DB contract slice is explicitly chosen.

### Standing Policies

- When adding `sample27+`, update `docs/sample-tutorial-roadmap.md`, `sample/tutorials/README.md`, make targets, checker, and actual generated reference together.
- For `sample20+`, continue treating MySQL / MariaDB runtime profile as canonical unless a new plan explicitly requires SQLite dual-profile.
- For `sample19-26`, do not propose product-like next work as the normal continuation. If this lane is revisited, prefer readability, README/study guide clarity, scope-cut wording, and sample granularity alignment.

### Immediate

1. Security foundation first slice is complete as of the current 2026-06-19 security/auth commit-in-progress.
   - Added security regression checks for public raw OpenAPI/artifact exposure.
   - Added audit log schema and repository usable from MySQL / MariaDB and SQLite config store.
   - Added audit metadata secret/token/password redaction regression.
   - Minimal project permissions follow after the audit event shape is clear.
   - SSO / OIDC work has moved to first-slice I/F after this local role / audit boundary was put in place.

### Next

1. API auth v2 `Phase 1. Policy Contract` is complete as of `6765f79 Add security foundation and bearer auth contract`.
   - First storage shape: `auth_policy_version`, `auth_policy_json` beside existing proxy/function metadata.
   - Defer `project_auth_policies` table until reuse / inheritance is needed.
   - Validation is fixed for unknown policy invalid, blank new auth invalid, missing secret reference fail-closed, and populated secret/token/password fields invalid.
   - Legacy `ProjectToken` compatibility remains, but body `TOKEN` is not the new default.

2. `static-bearer` first implementation is complete as of `6765f79 Add security foundation and bearer auth contract`.
   - Generated proxy runtime verifies `Authorization: Bearer`.
   - OpenAPI emits an `http` bearer security scheme.
   - Swagger helper can send bearer auth as an HTTP header instead of a request-body token field.
   - Missing / malformed / wrong / env-missing / success cases are covered by contract tests.

3. Auth-required OpenAPI browser smoke is complete as of `6ddb61d Add auth Swagger browser smoke`.
   - sample25 now has a headless Chrome Swagger Try It Out smoke for an auth-required proxy path.
   - Auth proxy reference outputs for sample16 / sample25 / sample26 are refreshed for the API auth v2 runtime shape.
   - Minimal project permission model / user control is intentionally externalized to the SSO / membership redesign topic, because current membership is legacy and should not be expanded in this security/browser-smoke commit.

4. Import preview / apply review hardening is complete as of `03c5ec4 Add import review details`.
   - Table import plans include `review_required`, destructive / metadata update counts, per-table risk level, reasons, and column-level before/after changes.
   - Preview remains non-mutating; apply still uses the existing preview plan and then mutates canonical metadata.

### Parked

- Approval workflow.
- rollback / revision history.
- local app packaging.
- PostgreSQL / SQL Server user DB support.
- production-grade ebook CMS features such as editor UI, full role management, upload, search, EPUB generation, payment, DRM.
- Full Mtool namespace migration. Composer usage by itself does not require this because third-party classes are already namespaced; a repo-wide Mtool namespace migration should be a separate post-security plan if it is still valuable.

### SSO / Membership Follow-up Status

- Design source of truth: `docs/internal/auth-architecture.md`.
- OIDC first slice uses env / compose / config / route / callback interfaces and stores the same session principal shape as stub auth.
- Mock OIDC smoke is contract-level: verified claims can produce a session principal without depending on a real IdP.
- OIDC login HTTP smoke is first-slice implemented with a mock IdP and verifies redirect / callback / session principal / project role claim storage.
- SSO first completion boundary is documented in `docs/internal/sso-oidc-connection.md`; Keycloak setup, Mtool env mapping, mock smoke, and remaining authorization hardening boundary are explicit.
- OIDC login and audited project permission decisions write audit events with actor source, capability, role source, and result.
- Member / group lifecycle is externalized to an OIDC-compatible IdP or surrounding OSS, not built inside Mtool.
- External IdP project roles are first-slice implemented through group claims such as `dego:project:<PROJECT_KEY>:publisher`, with configurable group claim and project-role prefix.
- New project permission work may use `project_identity_memberships` for `principal_source + principal_subject + role_code`, but this table is local override / break-glass / test support rather than primary membership management.
- Legacy `project_memberships` remains compatibility fallback only and should not receive new SSO behavior.
- Project permission roles are `viewer`, `editor`, `publisher`, and `admin`; source output publish/download requires `publisher` or stronger.

## Proposal Guardrails

For the completed `sample19-26` ebook CMS lane, future recommendations should use a sample-first standard:

- improve understandability before adding behavior;
- keep each sample centered on one teaching point;
- document scope cuts as intentional sample boundaries;
- avoid turning tutorial follow-up into production CMS implementation work.

Production-like items such as editor UI, role management, revision history, upload, EPUB build, search, payment, DRM, monitoring, and backup are parked unless the user explicitly asks for a separate product / foundation plan.

## Recommended Next Order

1. Start `security foundation` with security regression checks and audit log schema, not OIDC.
   - This matches `2026-0617-enterprise-personal-feature-plan.md`.
   - It also prepares API auth v2 and bundle/import review without coupling the first slice to an IdP.

2. Then implement API auth v2 policy contract.
   - Once storage and validation are fixed, `static-bearer` is a contained follow-up.

3. Keep dialect work scoped to generated output/user DB side.
   - Avoid reopening Mtool config store SQLite as if it were unfinished.

## Notes

- `original-codes/` remains host-side reference only and must not become runtime input.
- Reports under `docs/reports/2026/` are historical records. This inventory does not rewrite their original status unless a living doc points to them as current.
- Permanent docs should receive updates only when a plan becomes a durable contract, not for every exploratory report.
