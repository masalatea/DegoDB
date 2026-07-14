# sample34 SQLite-to-Firebird promotion

`sample34` is the first representative tutorial artifact for the SQLite-first to Firebird local durable profile lane.

This sample is intentionally an artifact-chain fixture plus an opt-in live smoke, not a normal runtime pack. It validates the side-effect-free promotion contract that prepares a SQLite local/lightweight source for a Firebird local durable file target, rehearses the export/import artifact boundary, and can then prove the live Firebird import path in Docker.

## Scope

- Source: SQLite inspection snapshot plus a small in-memory SQLite row fixture.
- Target: Firebird local durable file profile.
- Artifact: deterministic SQLite-to-Firebird promotion contract, Firebird DDL plan, SQLite export chunks, and Firebird import rehearsal checkpoint.
- Mutation: none for the validator. The optional Docker smoke mutates only disposable `sample34` proof tables in the configured Firebird database.

## Verified boundary

- canonical table metadata and SQLite inspection are compared;
- load order honors foreign keys;
- Firebird target types are selected for integer, decimal, JSON/text, blob, timestamp, and boolean-like values;
- Firebird DDL is planned from the contract;
- SQLite fixture rows are exported in deterministic primary-key order;
- JSON text, binary BLOB, timestamp, and nullable values are encoded in the export artifact;
- an import rehearsal package checks row/chunk counts and keeps the target switch explicit;
- the opt-in Docker smoke creates Firebird tables, inserts the fixture rows, and verifies row counts plus JSON/text BLOB, binary BLOB, timestamp, PK, FK, and next-id candidate evidence;
- the backup/restore Docker smoke verifies the imported proof DB survives Firebird backup/restore with expected row counts;
- source retention is required;
- target must be new or empty;
- backup/restore and explicit local profile switch remain required approvals/verifications;
- automatic source deletion and automatic cutover are non-goals.

## Run

```bash
php mtool/scripts/validate_sample34_firebird_promotion.php
php mtool/scripts/validate_sample34_firebird_promotion.php --json
make sample34-firebird-promotion-smoke-docker
make sample34-firebird-backup-restore-smoke-docker
```

Canonical PHPUnit coverage:

```bash
phpunit tests/Integration/Sample34SqliteFirebirdPromotionTest.php
```

## Not covered yet

- Automatic local profile switch.
- Firebird-to-MySQL/MariaDB promotion.
