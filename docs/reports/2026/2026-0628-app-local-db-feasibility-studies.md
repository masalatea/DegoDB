# 2026-06-28 App Local DB Feasibility Studies

## Purpose

App local DB / sync / no-code app roadmap の本計画とは別に、独立して実施できる feasibility study を整理する。

この文書は実装計画の本線ではない。大きな roadmap に入る前に、リスクの高い要素を小さく検証するための spike catalog である。

Parent roadmap:

- [2026-0628 App Local DB And Sync Roadmap](2026-0628-app-local-db-and-sync-roadmap.md)

## Status

- catalog status: `DONE`
- core App-local study status: `COMPLETED`
- remaining optional study status: `PENDING`
- auth-related study status: `PROMOTED_TO_AUTH_FOUNDATION_PLAN`
- completed App-local studies: `Shared Contract Manifest Spike`, `App Local SQLite Schema Spike`, `DTO Save/Read Spike`
- next recommended App-local implementation work: `Shared DataClass contract foundation`
- current-plan link: this study catalog is not the active implementation itself; auth foundation has been promoted to a normal first-slice plan in `docs/current-plans.md`, and the remaining App-local / no-code studies stay as Gate 0 FS.

Status reading:

- This document being done means the FS catalog / placement policy / study definitions are drafted.
- The catalog itself is done, and the core App-local feasibility chain has now been executed as dated result reports.
- Auth-related studies remain documented here for traceability, but the current decision is to implement the Mtool auth foundation as normal feature/foundation work before returning to App-local / no-code FS.
- Each executed study produced its own dated result report before promotion to implementation.

Executed core FS reports:

- [2026-0629 Shared Contract Manifest Spike](2026-0629-shared-contract-manifest-spike.md)
- [2026-0629 App Local SQLite Schema Spike](2026-0629-app-local-sqlite-schema-spike.md)
- [2026-0629 DTO Save/Read Spike](2026-0629-dto-save-read-spike.md)

Core FS conclusion:

- DataClass metadata can describe generated DTO shape and match generated PHP fields.
- DataClass metadata alone is not enough as the shared persistence / sync contract.
- A separate shared contract metadata layer should carry nullable / default / key / persistence / sync semantics.
- Explicit shared contract semantics can generate App-local SQLite schema.
- DTO-shaped rows can round trip through that schema without shape loss in the minimal spike.
- Shared Contract Core Vocabulary is complete; the larger Shared DataClass contract foundation can now build on the manifest v0 vocabulary and validator.

## Positioning

This is feasibility study, not the main implementation plan.

読み方:

- 本計画ではない。
- `docs/current-plans.md` に昇格した active plan ではない。
- 既存 DB schema や generator の大きな変更を前提にしない。
- 失敗しても roadmap 全体を壊さない小さな実験として扱う。
- 成功した study だけを、後で roadmap の Work Unit へ昇格する。

## Study Selection Rule

feasibility study は次の条件を満たすものを優先する。

- 既存 sample を使える。
- 新しい config DB schema を追加しなくても試せる。
- generated reference を少数に限定できる。
- 1-3 日以内で結果が読める。
- 成功 / 失敗 / 要再設計を明確に判断できる。
- 本体機能として採用しなくても学びが残る。

## Placement Policy

Feasibility study は、正式 sample や本体 generator と混同しない場所に置く。

Default placement:

| Artifact | Placement | Rule |
| --- | --- | --- |
| study plan / result | `docs/reports/2026/` | 日付付き report として残す。 |
| small spike script | `mtool/scripts/experimental/` | 本体 CLI / generator に接続しない standalone script として置く。 |
| disposable output | `work/feasibility/<study-key>/` | repo に commit しない scratch output。 |
| stable result snapshot, if needed | `docs/reports/2026/assets/<study-key>/` or a dated appendix | 小さく、説明用に必要な場合だけ残す。 |
| promoted tutorial | `sample/tutorials/sampleNN-*` | FS 成功後、正式 sample に昇格する時だけ作る。 |

Do not start a feasibility study by adding a new tutorial sample. A tutorial sample means a supported learning path and regression responsibility. FS はその前段として扱う。

Do not start by changing core generator behavior unless the study specifically requires a tiny read-only helper. Prefer standalone scripts that read existing metadata / generated output and write to `work/feasibility/...`.

## Code Change Policy

FS の初期段階では、本体コード変更を最小化する。

Allowed first:

- standalone scripts under `mtool/scripts/experimental/`.
- read-only helpers that parse existing generated output or metadata.
- temporary output under `work/feasibility/...`.
- report updates under `docs/reports/2026/`.

Avoid in first FS:

- config DB schema migration.
- new permanent Source Output type.
- changes to current generated PHP output.
- changes to tutorial sample references.
- changes to `docs/current-plans.md`.

Promotion threshold:

- If a spike passes and should become product behavior, create a separate implementation plan.
- Only then add schema migration, Source Output catalog rows, official sample, reference outputs, and tests.
- Promotion should happen as a new work unit, not as silent continuation of the FS.

## Recommended Order

| Order | Study | Rough effort | Why first |
| --- | --- | ---: | --- |
| 1 | Auth Foundation First Slice | 1-3 days | FS ではなく正式計画へ昇格。Legacy Permission Unit Inventory / Operation Policy Evaluator / Principal Mapping の内容を通常機能として扱う。 |
| 2 | Shared Contract Manifest Spike | 0.5-1.5 days | 後続すべての土台。既存 DataClass metadata だけで試せる。 |
| 3 | App Local SQLite Schema Spike | 1-2 days | App-local DB の型変換と schema 差分を小さく確認できる。 |
| 4 | DTO Save/Read Spike | 1-3 days | `server read -> DTO -> app save -> app read` の最小核を確認する。 |
| 5 | Principal Mapping Spike | 0.5-1 day | Auth foundation の一部として扱う。OIDC / simple-local / upstream claims を normalized principal に寄せられるか確認する。 |

## Study 1. Shared Contract Manifest Spike

### Question

既存の `dataclass` / `dataclassfields` metadata から、server / App / API / sync / no-code が共有できる language-neutral contract manifest を出せるか。

### Scope

- 新しい config DB table は追加しない。
- 既存 sample を使う。
- first candidate は `sample02-dataclass-nullable-default-status` または `sample10-dbaccess-mini-crud-flow`。
- generated PHP DataClass output と manifest の field shape を比較する。

### Candidate Output

- `DATACLASS-CONTRACT-JSON`

Example shape:

```json
{
  "contract_key": "task",
  "dataclass": {
    "logical_name": "Task",
    "physical_name": "task",
    "generated_name": "Task"
  },
  "fields": [
    {
      "logical_name": "TaskId",
      "physical_name": "task_id",
      "generated_name": "TaskId",
      "type": "integer",
      "nullable": false,
      "is_key": true
    }
  ]
}
```

### Success Criteria

- manifest can be generated from existing DataClass metadata.
- generated PHP DataClass fields match manifest fields.
- nullable / default / key / enum-like status information is either present or explicitly reported as missing.
- physical / logical / generated names are represented without ambiguity.

### Failure Signals

- existing metadata lacks enough field semantics for a useful contract.
- physical / generated naming cannot be reconstructed safely.
- PHP DataClass output and metadata disagree in a way that cannot be explained.

### Follow-up If Successful

- Promote to roadmap Work Unit 2.
- Decide whether `DATACLASS-CONTRACT-JSON` becomes a real Source Output.
- Add contract comparison to sample gate.

## Study 2. App Local SQLite Schema Spike

### Question

shared contract manifest から、App-local SQLite schema を安全に生成できるか。

### Scope

- Mtool config DB schema は触らない。
- Study 1 の manifest、または手書き minimal manifest を入力にする。
- SQLite DDL を生成し、SQLite に apply する。
- sync engine は作らない。

### Candidate Output

- `APP-LOCAL-SQLITE-SCHEMA`

Example generated elements:

- local table DDL.
- local metadata columns.
- simple index.
- optional schema version table.

### Candidate Local Metadata Columns

- `local_updated_at`
- `last_synced_at`
- `sync_status`
- `dirty`
- `tombstone`

### Success Criteria

- SQLite schema applies cleanly.
- core fields match shared contract.
- local metadata columns can be added without confusing core contract fields.
- generated docs can explain server-copy vs app-local metadata.

### Failure Signals

- type mapping is too lossy.
- local metadata columns collide with business fields.
- table naming / key policy is unclear.

### Follow-up If Successful

- Promote to roadmap Work Unit 3.
- Decide local metadata column policy.
- Add SQLite apply check.

## Study 3. Operation Policy Evaluator Spike

### Question

SSO を入れずに、normalized principal と operation policy の照合モデルが単純に成立するか。

### Scope

- No IdP.
- No login.
- No user table.
- JSON principal and JSON operation policy only.
- Evaluate roles / claims / scopes / permission key.
- Include storage role as an additional check if useful.

### Example Input

```json
{
  "principal": {
    "subject": "user-123",
    "roles": ["editor"],
    "claims": {
      "department": "sales"
    },
    "scopes": ["note:write"]
  },
  "operation": {
    "operation_key": "update_note",
    "required_roles": ["editor"],
    "required_claims": {
      "department": "sales"
    },
    "required_scopes": ["note:write"]
  }
}
```

### Success Criteria

- all-pass authorization check is understandable.
- missing role / claim / scope fails closed.
- denial reason is explainable.
- storage role can reject operation independently from SSO permission.

### Failure Signals

- simple all-pass model cannot express common cases.
- role / claim / scope mapping becomes ambiguous immediately.
- storage role and permission become hard to separate.

### Follow-up If Successful

- Promote to roadmap Work Unit 6 / 7.
- Define `operation_auth_policies` first schema shape.
- Add generated operation documentation candidate.

## Study 4. Legacy Permission Unit Inventory Spike

### Question

旧 `ProjectUser` / page security の細かい権限単位を、現代的な Mtool permission key の雛形として使えるか。

Design direction:

- 旧実装の user-specific read/write bit 群は、そのまま復活させない。
- 新しい auth foundation は role-based access control を基本にする。
- legacy permission units are inventory input for designing role permission sets.
- principal / user / SSO group maps to role.
- role grants permission keys.
- operation / route / output policy requires role or permission key.

Conceptual shift:

```text
legacy:
  project -> user -> many tool/category read/write bits

current direction:
  project -> principal -> role -> permission keys
```

### Background

旧実装には `ProjectUser` の read/write bit 群があった。

Observed legacy units:

- `CHATREAD` / `CHATWRITE`
- `REQREAD` / `REQWRITE`
- `SPECTOOLREAD` / `SPECTOOLWRITE`
- `DBTOOLREAD` / `DBTOOLWRITE`
- `HTMLREAD` / `HTMLWRITE`
- `TESTTOOLREAD` / `TESTTOOLWRITE`
- `MINUTESREAD` / `MINUTESWRITE`
- `UPLOADREAD` / `UPLOADWRITE`

Current implementation already simplified part of this:

- `project_memberships`: owner / admin / member first slice.
- `project_identity_memberships`: principal-source / subject based project roles.
- `project_page_security_policies` and `project_page_security_policy_capabilities`: normalized capability list for legacy page security.
- `APP_PROJECT_PERMISSION_REQUIREMENTS`: current project-level capability mapping such as `project.read`, `project.edit`, `source_output.publish`, `source_output.download`, `db_source.manage`, `secret.manage`, `project.admin`.

### Scope

- No new auth runtime.
- No SSO.
- No schema migration.
- Create an inventory table that maps legacy units to candidate modern permission keys.
- Identify which old units should be collapsed, renamed, parked, or preserved.
- Draft default roles and their permission sets.

Candidate default roles:

| Role | Meaning |
| --- | --- |
| `owner` | project owner; all permissions and ownership transfer guard. |
| `admin` | project administration and security-sensitive settings. |
| `editor` | design / build / publish work. |
| `viewer` | read-only project access and artifact viewing. |
| `publisher` | optional role for publish / download / artifact operations if separate from editor. |

### Candidate Output

- dated inventory report or JSON under `work/feasibility/legacy-permission-unit-inventory/`.
- optional generated Markdown matrix.

Example mapping:

| Legacy unit | Candidate permission key | Direction |
| --- | --- | --- |
| `DBTOOLREAD` | `db_design.read` or `project.read` | collapse or rename |
| `DBTOOLWRITE` | `db_design.edit` | preserve as domain permission |
| `HTMLREAD` | `html_template.read` | preserve if HTML authoring remains separate |
| `HTMLWRITE` | `html_template.edit` | preserve if HTML authoring remains separate |
| `UPLOADREAD` | `artifact.read` or `source_output.download` | collapse |
| `UPLOADWRITE` | `artifact.publish` or `source_output.publish` | collapse |

### Success Criteria

- legacy permission units are fully listed.
- each unit has a candidate modern permission key or an explicit parked decision.
- current `APP_PROJECT_PERMISSION_REQUIREMENTS` can be compared against the legacy coverage.
- the result clarifies a minimum permission key set for Mtool auth foundation.
- default role -> permission key mapping is proposed.
- no user-specific read/write bit revival is required for the first slice.

### Failure Signals

- legacy units are too tied to old pages and cannot map to current route / operation concepts.
- mapping produces too many permission keys for first slice.
- read/write category model conflicts with current project role model.
- role model cannot express current route / output authorization without many per-user exceptions.

### Follow-up If Successful

- Promote into Auth Foundation FS.
- Define first `permission_key` catalog.
- Use it as input for Operation Policy Evaluator Spike.
- Decide what remains role-derived and what becomes explicit operation permission.
- Define role -> permission key defaults before SSO mapping.

## Study 5. DTO Save/Read Spike

### Question

shared DTO shape を App-local SQLite DB に保存し、同じ shape で読み戻せるか。

### Scope

- Generated output でなくてもよい。
- Node-based harness first.
- Browser worker / SQLite WASM / OPFS は後回し。
- Use Study 1 manifest and Study 2 schema if available.

### Flow

```text
server fixture row
-> DTO object
-> local SQLite save
-> local SQLite read
-> DTO compare
```

### Success Criteria

- DTO can be persisted and read without shape loss.
- null / default / key fields survive round trip.
- harness stays small enough to become a generated runtime candidate later.

### Failure Signals

- TypeScript runtime or SQLite library choice dominates the experiment.
- DTO shape needs application-specific transformation too early.
- field defaults behave differently from contract expectations.

### Follow-up If Successful

- Promote to roadmap Work Unit 4.
- Decide TypeScript runtime/library candidate.
- Add sample27 runtime harness candidate.

## Study 6. Principal Mapping Spike

### Question

different auth sources can be mapped to the same Mtool normalized principal shape.

### Scope

- No live IdP required.
- Use static JSON examples.
- Include Keycloak-like claims, authentik-like claims, simple-local principal, and upstream header-derived principal.

### Normalized Principal Shape

```json
{
  "subject": "user-123",
  "display_name": "Alice",
  "email": "alice@example.com",
  "roles": ["editor"],
  "groups": ["sales"],
  "scopes": ["note:write"],
  "claims": {
    "department": "sales"
  },
  "auth_source": "oidc"
}
```

### Success Criteria

- at least two external claim shapes can map to the same principal model.
- missing required subject fails closed.
- role / group / scope mapping is explicit.
- mapping is configuration-driven, not provider-hardcoded.

### Failure Signals

- provider-specific behavior leaks into operation policy.
- role / group / scope distinction is too vague.
- upstream headers require unsafe trust assumptions.

### Follow-up If Successful

- Promote to roadmap Work Unit 7.
- Define principal mapping config shape.
- Connect to operation policy evaluator.

## Minimal Recommended Study Set

If time is limited, run only:

1. Shared Contract Manifest Spike.
2. App Local SQLite Schema Spike.
3. Operation Policy Evaluator Spike.
4. Legacy Permission Unit Inventory Spike, if the next step is auth foundation rather than App-local DB.

Reason:

- They are independent.
- They require little or no new persistent schema.
- They test the core data-first idea, App-local output feasibility, and permission model separately.

DTO Save/Read and Principal Mapping can follow once the first three show useful results.

## Reporting Format

Each study should end with a short result report:

- status: `PASSED`, `FAILED`, or `NEEDS_REDESIGN`.
- files touched.
- command / test run.
- observed blockers.
- recommendation: promote, repeat, park, or discard.

Do not promote a feasibility study into `docs/current-plans.md` until the result is reviewed and a concrete implementation unit is chosen.
