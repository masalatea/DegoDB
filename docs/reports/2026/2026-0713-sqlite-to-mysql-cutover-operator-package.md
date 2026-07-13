# SQLite to MySQL cutover operator package report / SQLiteからMySQL cutover operator package記録

Date: 2026-07-13

## Summary / 概要

The SQLite-to-MySQL promotion lane now has a side-effect-free operator package layer above the cutover contract.

SQLiteからMySQLへのpromotion laneに、cutover contractの上位層として副作用なしのoperator package層を追加した。

## Decision / 判断

The operator package is not an execution engine. It is a reviewable artifact that points to approved switch, smoke, backup, rollback, and rehearsal references.

operator packageは実行engineではない。承認済みのswitch、smoke、backup、rollback、rehearsalへの参照を束ねるreview可能artifactである。

It intentionally rejects inline shell commands, inline SQL, automatic apply, source deletion, secrets, unsafe references, missing rehearsal evidence, and missing approvals.

inline shell command、inline SQL、automatic apply、source delete、secret、unsafe reference、rehearsal evidence不足、approval不足は意図的に拒否する。

## Implemented boundary / 実装境界

- Added `APP_SQLITE_MYSQL_CUTOVER_OPERATOR_PACKAGE_VERSION`.
- Added `APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS`.
- Added `app_sqlite_mysql_cutover_operator_package(...)`.
- The package requires a ready cutover plan and a valid cutover contract digest.
- The package remains side-effect-free and reports `mutation_performed=false`.
- Switch and rollback execution details are references only, not executable payloads.
- Rehearsal evidence must include switch dry-run, rollback rehearsal, and post-switch smoke rehearsal.

## Test evidence / テスト根拠

The integration test suite now covers:

- deterministic ready operator package creation;
- fail-closed behavior when the cutover plan, rehearsal, or approvals are incomplete;
- rejection of inline execution payloads, secrets, source deletion, and unsafe references.

## Remaining work / 残作業

The next #873 slice is representative sample rehearsal packaging: connect the cutover contract and operator package to one bounded SQLite-to-MySQL sample flow without broadening into zero-downtime CDC or bidirectional sync.

次の#873 sliceは代表sample rehearsal packaging。cutover contractとoperator packageを、1つの限定されたSQLite-to-MySQL sample flowに接続する。zero-downtime CDCや双方向syncには広げない。
