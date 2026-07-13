# sample33 SQLite-to-MySQL promotion

`sample33` is the first representative tutorial artifact for the SQLite-first to MySQL promotion lane.

This is not a runtime pack yet. It intentionally has no `compose.yaml`, `run.sh`, or `seed/`.

## Goal

Show the supported offline promotion path as a reviewable artifact chain:

1. freeze a SQLite source;
2. prepare a fresh MySQL target schema;
3. export SQLite rows deterministically;
4. import chunks into MySQL with checkpoint evidence;
5. verify source and target;
6. build an explicit cutover contract;
7. build an operator package;
8. produce a side-effect-free rehearsal package.

## Fixture

The fixture lives at:

- `reference/promotion-rehearsal-contract.json`

It describes a small parent/record domain with:

- parent/child relation;
- primary keys;
- a composite unique key;
- a foreign key;
- JSON value evidence;
- BLOB value evidence;
- timestamp value evidence;
- cutover and rollback references.

## Validate

Run the side-effect-free validator from the repository root:

```bash
php mtool/scripts/validate_sample33_promotion.php
php mtool/scripts/validate_sample33_promotion.php --json
```

The validator builds the rehearsal package from the fixture and reports whether the artifact chain is ready. It does not connect to a live MySQL database, change deployment config, delete the SQLite source, or perform cutover.

## Scope

This sample proves the v1 artifact contract for a bounded tutorial scenario. It does not perform automatic cutover, zero-downtime CDC, bidirectional sync, or MySQL-to-SQLite reverse migration.

The next slice may add a runtime-style wrapper or operator guide, but the first supported boundary is the artifact chain itself.
