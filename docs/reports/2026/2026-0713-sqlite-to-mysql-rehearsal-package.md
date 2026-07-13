# SQLite to MySQL rehearsal package report / SQLiteからMySQL rehearsal package記録

Date: 2026-07-13

## Summary / 概要

#873 now has a digest-chained rehearsal package that ties together the already completed promotion artifacts before a representative tutorial/sample is built.

#873に、代表tutorial・sample化の前段として、既存promotion artifact群をdigestで接続するrehearsal packageを追加した。

## Implemented boundary / 実装境界

- Added `mtool/app/sqlite_mysql_promotion_rehearsal.php`.
- Added `sqlite-mysql-promotion-rehearsal-package-v1`.
- The package verifies the chain across:
  - promotion manifest;
  - target schema plan;
  - deterministic SQLite export chunks;
  - import checkpoint;
  - verification artifact;
  - cutover contract;
  - operator package.
- The package itself is side-effect-free and reports `mutation_performed=false`.
- Import evidence may describe an isolated rehearsal mutation, but the package does not perform that mutation.

## Safety choices / 安全方針

- Secrets in any included artifact block the rehearsal package.
- Export chunk row count and digest mismatches block the package.
- Verification context digests must match the manifest, target schema, and import checkpoint.
- Cutover and operator package digests must match the upstream artifacts.
- The package explicitly keeps these non-goals: MySQL-to-SQLite, bidirectional sync, zero-downtime CDC, and automatic cutover.

## Test evidence / テスト根拠

`SqliteMysqlPromotionRehearsalTest` covers:

- a deterministic ready package for a bounded parent/record fixture with unique/FK, JSON, BLOB, timestamp, and chunked export evidence;
- fail-closed behavior for tampered export chunks, broken digest chain, not-ready operator package, and secrets.

## Remaining work / 残作業

The next step is #874: turn this bounded rehearsal chain into a representative SQLite-to-MySQL promotion sample/tutorial package.

次は#874。この限定されたrehearsal chainを代表SQLite-to-MySQL promotion sample/tutorial packageへ展開する。
