# SQLite-to-MySQL Verification Gate Preflight

Status: `PREFLIGHT_DONE_IMPLEMENTATION_NEXT`

## Decision

#872 will produce one versioned, mutation-free verification artifact. Cutover readiness is true only when every check declared required by the promotion manifest is present and has `status=passed`. Missing, skipped, unsupported, warning-only, or failed required checks all keep cutover blocked.

The verifier reads both databases after import. It never repairs data, changes constraints, advances sequences, or switches configuration.

## Artifact contract

Tentative version: `sqlite-mysql-promotion-verification-v1`.

Required top-level fields:

- promotion manifest SHA-256;
- target schema plan SHA-256;
- final import checkpoint SHA-256;
- source snapshot identity and target identity without credentials;
- `mutation_performed=false`;
- deterministic per-table results;
- required-check registry;
- blockers and warnings;
- `cutover_ready` derived by Mtool, never supplied by a caller.

Each check records `check_key`, `required`, `status`, source evidence, target evidence, and a stable failure code. Evidence contains counts, hashes, or bounded identifiers rather than full production rows.

## Required checks

| Check | V1 evidence | Failure code |
| --- | --- | --- |
| Row count | Exact source/target count per managed table | `row_count_mismatch` |
| PK set | PK values streamed in canonical tuple order and hashed; count plus digest must match | `primary_key_set_mismatch` |
| Row values | Canonically encoded rows in PK order | `row_digest_mismatch` |
| Nullability | Target query finds no null in required columns | `nullability_violation` |
| Unique keys | Target duplicate-group query returns zero for every canonical unique key | `unique_key_violation` |
| Foreign keys | Target orphan query returns zero and `information_schema` contains the declared FK | `foreign_key_violation` |
| JSON | Decode target JSON and compare canonical JSON value, not textual key order | `json_value_mismatch` |
| BLOB | Compare byte length and SHA-256 | `blob_value_mismatch` |
| Timestamp | Compare the declared normalized UTC value and precision | `timestamp_value_mismatch` |
| Next ID | For auto-increment tables, next value is greater than the maximum imported ID | `next_id_invalid` |
| DBAccess smoke | Declared generated read/write-safe smoke callback succeeds against target | `dbaccess_smoke_failed` |

## Digest policy

- V1 always verifies complete row counts and complete PK sets.
- Small and medium tables use full canonical row digest.
- A future configurable large-table threshold may use deterministic PK-bucket digests, but a sample-only digest cannot by itself mark a required row-value check passed.
- Therefore first implementation supports full streaming digests only. Scale optimization remains later and must preserve complete coverage.
- Canonical row encoding uses manifest column order, exact integer/decimal strings, canonical JSON values, BLOB length/hash envelopes, and normalized timestamp strings.

## Consistency boundary

Verification assumes the SQLite source remains write-frozen and the MySQL target has not accepted application writes. The verifier records start/end source and target count/digest sentinels; a changing database returns `verification_input_changed` rather than a result assembled from different moments.

There is no cross-database transaction. Each side gets its strongest read snapshot, and stable sentinels prove that the compared interval did not change.

## First implementation boundary

Add `mtool/app/sqlite_mysql_verification.php` with two layers:

1. provider-neutral result normalizer/gate that derives `cutover_ready` from a fixed required-check registry;
2. SQLite/MySQL read-only collectors for row count, PK digest, and full canonical row digest.

The first tests must prove:

- all required passed means ready;
- any failed, missing, skipped, unsupported, or warning-only required check blocks;
- input ordering cannot change artifact JSON/digest;
- a one-value difference with equal row count fails row digest;
- a PK replacement with equal row count fails PK digest;
- no credential or raw connection DSN enters evidence;
- both PDO connections remain unmodified.

Constraint, next-ID, and generated DBAccess collectors may follow as separate #872 slices, but #873 cannot start until every required check above has live evidence.

## Non-goals

- Automatically fixing target rows or constraints.
- Treating sampled rows as complete verification.
- Allowing operator acknowledgement to turn a failed required check into passed.
- Running cutover from the verifier.
- Comparing MySQL-specific physical representation when canonical application values are equal.
