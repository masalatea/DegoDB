# SQLite-to-MySQL Promotion / SQLiteからMySQLへの昇格

English companion:
This document is the stable operator-facing guide for the supported v1 path from a SQLite-first application database to MySQL/MariaDB. It describes an offline, one-way promotion with explicit review, verification, cutover, and rollback evidence. It is not a zero-downtime sync system.

この文書は、SQLite-first の application database を MySQL / MariaDB へ昇格する v1 supported path の恒久ガイドです。
ここでいう promotion は、明示的な review、検証、cutover、rollback 根拠を伴う offline / one-way の移行です。zero-downtime sync system ではありません。

## Supported boundary / 対応範囲

Supported:

- SQLite source is frozen before final copy.
- MySQL/MariaDB target is fresh and empty before schema creation.
- Canonical metadata decides target columns, primary keys, unique keys, foreign keys, nullability, and value mappings.
- SQLite rows are exported deterministically in primary-key order.
- MySQL/MariaDB import is transactional per chunk and records checkpoint evidence.
- Verification compares row counts, primary key sets, row values, nullability, unique keys, foreign keys, selected value classes, sequence safety, and DBAccess smoke evidence.
- Cutover requires explicit freeze, final verification, config switch reference, smoke reference, source retention, rollback window, and operator approvals.
- SQLite source is retained as rollback evidence.
- SSO app-user data can be promoted while preserving stable `app_user_id`, `(issuer, subject)` identity mapping, safe profile data, and app-owned FK data.

Out of scope:

- MySQL-to-SQLite reverse migration.
- Bidirectional sync.
- Zero-downtime CDC.
- Automatic production cutover.
- Automatic deletion of the SQLite source.
- Joining database migration with unrelated file, network, or external-store side effects.

## Artifact chain / artifact chain

The supported path is an artifact chain. Operators should review each artifact before proceeding.

1. Promotion manifest: source/target identity, canonical table contract, blockers, warnings, required approvals, non-goals.
2. Target schema plan: deterministic MySQL/MariaDB DDL and schema digest; requires an empty target and explicit approval.
3. SQLite export: deterministic chunks, value envelopes, chunk digests, and resume key.
4. MySQL import checkpoint: committed chunk digests and resume state.
5. Verification artifact: required checks and cutover readiness.
6. Cutover contract: freeze/smoke/rollback/source-retention requirements.
7. Operator package: manual switch package plus rehearsal evidence.
8. Rehearsal package: digest-chained summary that ties the above artifacts together.

The representative tutorial fixture is:

- [sample33 SQLite-to-MySQL promotion](../sample/tutorials/sample33-sqlite-to-mysql-promotion/README.md)

Validate the tutorial artifact chain:

```bash
php mtool/scripts/validate_sample33_promotion.php
php mtool/scripts/validate_sample33_promotion.php --json
```

This validator is side-effect-free. It does not connect to a live MySQL database, mutate deployment config, delete SQLite, or cut over traffic.

## SSO app-user promotion / SSO app-user昇格

The SSO app-user promotion lane proves the app-user case separately because user identity is a high-risk semantic boundary.

The qualified behavior is:

- SQLite runtime resolver creates the initial `app_user` through JIT.
- Repeat login restores the same stable `app_user_id`.
- Safe SSO profile fields are refreshed; token-like fields are not persisted.
- Application-owned data references `app_user_id`.
- Promotion exports/imports `app_user`, `app_user_external_identity`, `app_user_profile`, and app-owned FK data.
- On the promoted MySQL/MariaDB target, resolver restore still finds the same user.
- New JIT SSO login on the promoted target also succeeds.

For promoted target identity, the supported canonical key is the semantic SSO identity pair:

```text
app_user_external_identity primary key = (issuer, subject)
```

That avoids depending on copied SQLite `AUTOINCREMENT` sequence state for future SSO identities.

## Operator checklist / operator checklist

Before promotion:

- Confirm the source is SQLite and the target is MySQL/MariaDB.
- Confirm the target database is dedicated, empty, and safe to create into.
- Confirm canonical metadata has explicit PK/unique/FK/nullability/type mapping.
- Resolve manifest blockers; do not override them by hand.
- Plan a write freeze and rollback window.

During promotion:

- Freeze writes.
- Generate/review target schema plan.
- Apply target schema only after explicit approval.
- Export SQLite chunks.
- Import chunks into MySQL/MariaDB and retain checkpoints.
- Run verification and require every required check to pass.
- Build cutover and operator package.
- Rehearse switch, rollback, and smoke references.

After cutover:

- Switch application config only after acceptance.
- Run post-cutover smoke.
- Retain SQLite source until the rollback window ends.
- Do not automatically delete source data.

## Test evidence / test evidence

Current evidence anchors:

- `make test`
- `SsoAppUserPromotionTest`
- `SqliteMysqlTargetSchemaTest`
- `SqliteMysqlImportTest`
- `SqliteMysqlVerificationTest`
- `php mtool/scripts/validate_sample33_promotion.php`

Live MySQL/MariaDB checks use the opt-in `PROMOTION_MYSQL_TEST_DB` environment variable and require a dedicated schema named like:

```text
mtool_promotion_test_<suffix>
```

The normal full suite can run without that environment; live promotion tests are skipped when the dedicated schema is not configured.

## Related docs / 関連文書

- [Current Plans / 現在の計画](current-plans.md)
- [Proof Matrix / 根拠 matrix](proof-matrix.md)
- [Current Supported Workflow / 現在サポートするワークフロー](current-supported-workflow.md)
- [Storage And State Model / 保存先と状態モデル](storage-and-state-model.md)
- [SSO Application User Standard / SSOアプリケーションユーザ標準](sso-application-user-standard.md)
