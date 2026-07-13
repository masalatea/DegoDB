# SQLite-to-MySQL Promotion Manifest First Slice

Status: `DONE_FIRST_SLICE`

## Result

#868 added a provider-neutral, side-effect-free `sqlite-mysql-promotion-manifest-v1` contract in `mtool/app/sqlite_mysql_promotion_manifest.php`.

The builder accepts canonical metadata, an already-read SQLite inspection snapshot, and redacted options as arrays. It opens no database, writes no file, generates no DDL, copies no row, and always declares `stage=preflight` and `mutation_performed=false`.

## Supported first-slice evidence

- deterministic normalized input digests and manifest ordering;
- canonical/source table and column mismatch blockers;
- stable primary-key requirement;
- primary/composite-unique/foreign-key evidence comparison;
- FK-derived parent-before-child load order and cycle rejection;
- canonical-to-MySQL type decisions for integer, boolean, decimal, datetime, JSON, BLOB, character, and text families;
- value-profile blockers for SQLite mixed storage classes, invalid JSON, integer range, decimal precision, text encoding/length, and ambiguous timestamps;
- trailing-space semantic warning;
- unsupported default-expression blocker;
- credential-bearing keys and DSN-shaped target identities rejected without copying secret values into the result;
- explicit approvals, verification checklist, and non-goals.

## Boundary

This is a planning contract, not a complete SQLite inspector. The representative test supplies the source inspection snapshot directly. Reading SQLite unique indexes, foreign keys, storage classes, and value profiles from a real file remains necessary before a real promotion can use the builder.

#869 is next. It must define deterministic MySQL DDL and an empty-target verification boundary, but it must not import data. A real SQLite inspection adapter may be added before or alongside #869 only when needed to feed the already-fixed manifest contract; it must remain read-only.

## Verification

- `php -l mtool/app/sqlite_mysql_promotion_manifest.php`
- `php -l tests/Integration/SqliteMysqlPromotionManifestTest.php`
- focused PHPUnit: `OK (8 tests, 21 assertions)`
- full `make test`: `Tests: 554, Assertions: 14919, Skipped: 1`
- `git diff --check`
