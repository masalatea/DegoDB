# Sample33 SQLite-to-MySQL promotion report / sample33 SQLiteからMySQL promotion記録

Date: 2026-07-13

## Summary / 概要

#874 has a first representative tutorial artifact: `sample33-sqlite-to-mysql-promotion`.

#874のfirst representative tutorial artifactとして、`sample33-sqlite-to-mysql-promotion`を追加した。

## Boundary / 境界

This first slice is not a Docker runtime pack. It is a promotion tutorial sample with a `reference/` fixture and a PHPUnit contract.

このfirst sliceはDocker runtime packではない。`reference/` fixtureとPHPUnit contractを持つpromotion tutorial sampleである。

## Evidence / 根拠

The sample fixture builds a ready `sqlite-mysql-promotion-rehearsal-package-v1` from:

- parent/record SQLite source shape;
- primary, unique, and foreign-key metadata;
- JSON, BLOB, and timestamp values;
- MySQL target schema plan;
- deterministic export chunks;
- import checkpoint evidence;
- verification artifact;
- cutover contract;
- operator package.

## Operator validator / operator validator

The sample now has a side-effect-free validator:

```bash
php mtool/scripts/validate_sample33_promotion.php
php mtool/scripts/validate_sample33_promotion.php --json
```

The validator reads the fixture, rebuilds the artifact chain, and reports readiness and digests. It does not connect to a live MySQL database, change deployment config, delete the SQLite source, or perform cutover.

## Remaining work / 残作業

Runtime wrapper work is intentionally deferred. The supported #874 boundary is complete with the artifact-chain tutorial and side-effect-free validator CLI. A runtime-style wrapper should be added only if a concrete hands-on operation need appears.

次のruntime wrapperは意図的に保留する。#874のsupported boundaryは、artifact-chain tutorialと副作用なしvalidator CLIで完了とする。runtime-style wrapperは具体的なhands-on運用需要が出た場合だけ追加する。

The next main lane is #875: qualify the SSO app-user fixture through the same SQLite-to-MySQL promotion contract.

次の主線は#875。同じSQLite-to-MySQL promotion contractでSSO app-user fixtureを認定する。
