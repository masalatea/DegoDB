# SQLite-first to MySQL/MariaDB Promotion Plan

Status: `PREFLIGHT_DONE_IMPLEMENTATION_PLANNED`

Preflight evidence and the exact first implementation boundary are recorded in [SQLite-to-MySQL Promotion Preflight](2026-0713-sqlite-to-mysql-promotion-preflight.md).

## Goal

Provide one safe, understandable path for an application that starts on SQLite and later outgrows it to move to MySQL/MariaDB without redesigning the Mtool project or changing application identity semantics.

This is a one-way promotion workflow, not database synchronization. SQLite is the source, a fresh MySQL/MariaDB database is the target, and canonical Mtool metadata remains the schema/design authority.

## V1 operating model

V1 deliberately uses an offline cutover:

1. preflight canonical metadata and actual SQLite values;
2. prepare a fresh target schema;
3. obtain explicit approval and freeze application writes;
4. export SQLite rows in deterministic order;
5. import into MySQL with resumable checkpoints;
6. run required verification gates;
7. switch deployment connection configuration;
8. run smoke tests;
9. retain the SQLite source unchanged for a defined rollback window.

There is no cross-database atomic transaction. Safety comes from an immutable source snapshot, an isolated target, deterministic/resumable import, verification before cutover, and delayed source retirement.

## Supported starting assumptions

- The project already has canonical Table/DataClass/DBAccess metadata.
- SQLite is the active application user DB, not merely Mtool's config store.
- The target is a new or explicitly empty MySQL/MariaDB database.
- Primary keys are preserved exactly; application IDs are not regenerated.
- The first version accepts planned write downtime.
- Secrets remain deployment inputs and never enter promotion artifacts.

Projects with no stable primary key, unresolved SQLite dynamic-type violations, unsupported collations, ambiguous timestamps, or undeclared custom SQL are blocked until reviewed.

## Authority and artifacts

The promotion planner reads:

- canonical project metadata for intended tables, columns, keys, FKs, and generated DBAccess;
- SQLite schema/value inspection for source reality;
- explicit target connection capabilities.

It emits a versioned promotion manifest under the Mtool work area. The manifest records source/target identity without passwords, schema digest, table order, column mappings, type/value conversions, row counts, chunk/checkpoint policy, blockers, warnings, approvals, and verification results.

The manifest is review evidence, not permission to mutate. Target preparation, import, and cutover each require separate explicit approval.

## Type and value policy

The preflight must treat SQLite's dynamic typing as a value-level problem rather than trusting declared column types alone.

Required first mappings include:

| SQLite intent/value | MySQL/MariaDB target | Required checks |
| --- | --- | --- |
| integer primary key | signed/unsigned integer chosen by canonical metadata | range, sign, duplicate, sequence next value |
| text identifier | `VARCHAR`/`CHAR`/`TEXT` | length, charset, collation, trailing-space semantics |
| boolean integer | `TINYINT(1)` or declared boolean convention | values limited to 0/1/null |
| numeric/decimal | exact `DECIMAL` where declared | precision/scale; no float coercion |
| timestamp text | declared UTC datetime/timestamp convention | parseability, timezone normalization, precision |
| JSON text | `JSON` when selected, otherwise text | every non-null value parses; canonical encoding policy |
| blob | `BLOB` family sized from observed and declared limits | byte length, streaming, digest |
| null/default | target nullability/default | source violations and dialect-specific default expressions |

Unknown or lossy conversions are blockers, not best-effort casts.

## Ordering, constraints, and transactions

- Compute table load order from canonical foreign keys.
- Reject unresolved cycles in V1 unless an explicit deferred-constraint strategy is qualified.
- Preserve primary keys and natural unique keys.
- Import each chunk in one MySQL transaction.
- Record a checkpoint only after commit.
- A retry must be idempotent and must not silently overwrite target rows.
- Set auto-increment/sequence state after verified data load.
- Run target FK/unique validation before cutover even when constraints were delayed during loading.

## Verification gate

Cutover is blocked unless all required checks pass:

- source and target row counts per table;
- primary-key set equality;
- deterministic selected-row or full-table digests according to size policy;
- unique and foreign-key integrity;
- nullability and value-range checks;
- JSON parse/canonical-value checks;
- BLOB byte length/digest checks;
- timestamp normalization checks;
- next-ID/auto-increment correctness;
- generated DBAccess smoke tests on the target;
- SSO first/repeat-login behavior when the project uses the app-user standard.

## Cutover and rollback

Cutover is a deployment/configuration action outside the data-copy transaction. It requires the final verification manifest, operator approval, and a smoke-test plan.

Rollback means restoring the previous SQLite connection while the retained SQLite source is still authoritative and no accepted MySQL-only writes have occurred. Once MySQL accepts production writes, rollback requires an explicit data reconciliation plan; V1 does not pretend this is automatic.

Therefore the initial smoke window should either keep writes disabled or allow only a deliberately reversible test account/operation set.

## Explicit non-goals

- MySQL/MariaDB to SQLite migration as a standard path.
- Bidirectional replication or ongoing sync.
- Zero-downtime CDC.
- Automatic conflict resolution after both databases have accepted writes.
- Automatic conversion of arbitrary triggers, views, stored procedures, generated columns, FTS, or SQLite-specific custom SQL.
- Automatic deletion of the SQLite source.
- Automatic production connection switch without approval.

## Implementation sequence

| Plan | Work unit | Exit condition |
| --- | --- | --- |
| 867 | Inventory and preflight | V1 contract, blocker matrix, representative fixture, and first code boundary are fixed. |
| 868 | Manifest/planner contract | Side-effect-free plan reports mappings, counts, risks, approvals, and deterministic artifact shape. |
| 869 | Target schema preparation | Fresh MySQL schema is generated and verified before data writes. |
| 870 | SQLite export/conversion | Stable, resumable chunks preserve source identity and values. |
| 871 | MySQL import/resume | Chunk transactions and checkpoints survive interruption without duplicate writes. |
| 872 | Verification gate | Required equality/integrity/value checks block unsafe cutover. |
| 873 | Cutover/rollback | Explicit write freeze, switch, smoke, rollback window, and source retention are defined. |
| 874 | Representative sample | One bounded application proves interruption, retry, verification, cutover, and rollback rehearsal. |
| 875 | SSO qualification | Stable app-user IDs and identity/profile invariants survive promotion. |
| 876 | Checkpoint/integration | Supported boundary and operational guide are ready for integration. |

## Estimate

This is a multi-stage product capability, not a one- or two-day task. A credible V1 offline path is roughly three to six weeks of implementation and qualification, depending on how much of target schema generation and chunked import can reuse current user-DB contract tooling. Large-dataset performance, CDC, and broader dialect objects are separate later projects.
