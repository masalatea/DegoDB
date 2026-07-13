# SQLite-to-MySQL Promotion Preflight

Status: `DONE`

## Decision

There is no feasibility blocker for an offline, one-way SQLite-to-MySQL/MariaDB promotion path. The repository already has useful dialect, metadata, constraint, generated DBAccess, and transaction foundations. It does not yet have a data-migration engine, and the existing live-schema import must not be mistaken for one.

The first implementation unit is therefore a side-effect-free promotion manifest builder, not target DDL or row copying.

## Exact V1 contract

| Topic | V1 decision |
| --- | --- |
| Direction | SQLite application user DB to a fresh MySQL/MariaDB application user DB only |
| Availability | Offline promotion with an explicit write freeze |
| Design authority | Canonical Mtool table and constraint metadata |
| Source reality | Read-only SQLite schema and value inspection; disagreements with canonical metadata block planning |
| Target | New or explicitly empty schema; existing unrelated or conflicting objects block by default |
| Identity | Preserve every declared primary-key value; never regenerate application IDs |
| Transactions | MySQL transaction per deterministic chunk; checkpoint only after commit |
| Acceptance | Required schema, row, key, value, digest, and runtime checks must all pass |
| Cutover | Separate explicit deployment approval after verification; never part of import transaction |
| Rollback | Retain the frozen SQLite source; automatic rollback ends after MySQL accepts non-reversible production writes |
| Secrets | Connection credentials are runtime inputs and never written to manifests or checkpoints |

## Reusable foundations

| Existing capability | Reuse decision | Limitation |
| --- | --- | --- |
| SQL dialect/config helpers in `mtool/app/database.php` | Reuse for validated driver identity and PDO construction | They do not define promotion policy or redact a manifest by themselves |
| SQLite table/column inspection in `app_project_table_import_source_tables_from_sqlite()` | Extract or reuse its read-only table/column normalization | It currently omits SQLite unique indexes and foreign keys |
| MySQL/MariaDB `information_schema` inspection | Reuse later for target emptiness and verification | Current reader is designed for live-schema import, not target acceptance |
| Canonical key/FK model in `table_constraint_metadata.php` and its repository | Reuse as intended constraint authority | Canonical column metadata is still dialect-shaped text and needs explicit promotion mapping |
| Project metadata bundle | Reuse stable project/table/constraint snapshots and digests | Bundle import/export is metadata transport, not row migration |
| Generated DBAccess runtime and three-driver contract tests | Reuse for post-load smoke and dialect behavior evidence | Cross-driver contract equality does not prove copied data equality |
| Generated transaction support | Reuse for target smoke and later composite behavior | It does not provide cross-database atomicity |
| SSO SQLite proof and generated resolver | Reuse only in #875 qualification | Current direct proof schema/runtime is SQLite-specific |

## Missing capabilities

1. A provider-neutral promotion manifest schema and strict normalizer.
2. SQLite unique-index and foreign-key inspection (`PRAGMA index_list/index_info/foreign_key_list`).
3. Value-level SQLite type profiling rather than trust in declared affinity.
4. Explicit canonical-to-MySQL type/default/collation mapping.
5. Fresh-target DDL generation and target-emptiness verification.
6. Stable row ordering, chunk encoding, digests, and resume checkpoints.
7. MySQL insert-only import with idempotent interruption recovery.
8. Cross-database verification and an acceptance gate.
9. Cutover/write-freeze/rollback artifacts and operator commands.

## Blocker matrix

The planner must return stable blocker codes rather than attempting a lossy conversion.

| Code | Condition | Why it blocks V1 | Resolution |
| --- | --- | --- | --- |
| `canonical_metadata_incomplete` | Managed table/column/key metadata is missing or ambiguous | Target schema and ordering cannot be authoritative | Complete/review canonical metadata |
| `source_schema_mismatch` | SQLite schema differs materially from canonical metadata | The planned target would not represent the actual source | Reconcile and approve metadata first |
| `stable_primary_key_missing` | A copied table has no declared stable PK | Deterministic ordering, retry, and identity verification are unsafe | Add a stable key or exclude the table explicitly |
| `sqlite_constraint_evidence_missing` | Required unique/FK evidence cannot be reconciled | Relationship order and target integrity are unproven | Inspect or declare/review canonical constraints |
| `foreign_key_cycle_unsupported` | Required FK graph has a cycle | V1 does not silently disable integrity or guess deferred loading | Redesign, explicitly qualify a later cycle strategy, or exclude |
| `sqlite_dynamic_type_violation` | Observed storage class/value contradicts the selected mapping | MySQL coercion could silently change data | Clean data or declare a reviewed lossless conversion |
| `integer_range_violation` | Observed integer exceeds selected MySQL range/sign | Exact identity/value preservation would fail | Select a wider type or repair data |
| `decimal_precision_violation` | Exact numeric text/value exceeds precision or scale | Float/best-effort casts are forbidden | Select adequate DECIMAL or repair data |
| `text_encoding_or_length_violation` | Text is invalid UTF-8 or exceeds target capacity | Target insert or comparison would be lossy | Clean data or choose reviewed binary/larger text mapping |
| `trailing_space_semantics_risk` | Keyed text can compare differently under MySQL collation | Unique/PK semantics may change | Select binary/appropriate collation and approve |
| `invalid_json_value` | A column selected as JSON contains invalid non-null JSON | MySQL JSON rejects or normalizes it unexpectedly | Clean data or keep it as text explicitly |
| `ambiguous_timestamp_value` | Timestamp lacks the declared timezone/format semantics | Normalization could change meaning | Declare convention and repair/approve values |
| `unsupported_default_expression` | SQLite default has no qualified MySQL equivalent | Generated rows may behave differently after cutover | Replace with a declared target default |
| `unsupported_sqlite_object` | Required trigger/view/generated/virtual/FTS object exists | V1 handles base tables and declared constraints only | Recreate manually under a separate reviewed plan |
| `target_not_empty` | Target contains data or conflicting objects | Retry and acceptance could overwrite/merge silently | Use a new/empty target |
| `secret_in_artifact` | Password/token appears in a planned artifact | Violates artifact safety boundary | Reject and regenerate with redacted connection identity |

Warnings may describe non-blocking observations, but no warning may downgrade one of these conditions automatically.

## Dedicated representative contract fixture

#868 should introduce a small in-test fixture rather than prematurely adding the final tutorial sample. The fixture has two parent/child tables and deliberately covers the promotion hazards:

- stable integer PKs with preserved, non-contiguous IDs;
- a composite unique key and one parent FK;
- nullable and defaulted fields;
- boolean values restricted to `0`, `1`, and `NULL`;
- exact decimal text at declared precision/scale boundaries;
- UTF-8 text, empty text, and trailing-space risk evidence;
- valid JSON text including key-order/whitespace variation;
- UTC timestamp text with fractional precision plus `NULL`;
- binary BLOB containing zero bytes;
- an auto-increment table whose next value must exceed the migrated maximum.

Negative variants should each trigger one stable blocker: missing PK, invalid JSON, mixed SQLite storage class, out-of-range integer, ambiguous timestamp, unsupported default, FK cycle, and canonical/source mismatch.

The later #874 tutorial may promote this fixture into a user-facing sample after the planner, target, import, and verification boundaries exist.

## First implementation boundary: #868

Add a pure contract module, tentatively `mtool/app/sqlite_mysql_promotion_manifest.php`, with no filesystem writes and no target connection:

```php
app_sqlite_mysql_promotion_manifest_build(
    array $canonicalSnapshot,
    array $sqliteInspection,
    array $options = [],
): array;

app_sqlite_mysql_promotion_manifest_contract_errors(array $manifest): array;
```

The result uses a versioned schema such as `sqlite-mysql-promotion-manifest-v1` and contains:

- `ok`, `stage=preflight`, and `mutation_performed=false`;
- redacted source/target identity and immutable input digests;
- deterministic table order and per-column mapping decisions;
- observed row counts and value-profile summaries, never secrets or full production rows;
- stable blockers and warnings;
- required later approvals and verification checklist;
- explicit non-goals and supported-boundary labels.

A separate read-only SQLite inspection adapter may be introduced in the same semantic unit only when needed to prove the fixture. The pure builder must remain independently testable with arrays. #868 must not create MySQL DDL, connect to a target, export rows, write checkpoints, or switch configuration.

## #868 test boundary

- deterministic identical input produces byte-equivalent normalized manifest JSON;
- input order does not change table/column/blocker ordering;
- source/canonical mismatch fails closed;
- credentials and raw connection DSNs cannot enter output;
- each first-slice blocker has a stable code and evidence path;
- supported fixture returns a reviewable plan with `mutation_performed=false`;
- no MySQL/Ollama/browser dependency is required for normal tests.

## Sequence confirmation

#868 remains the next main work unit. Target DDL begins only at #869 after the manifest contract can explain exactly what would be created. The optional Ollama fallback lane #877-#883 remains independent and is not required for database promotion.
