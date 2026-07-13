# SSO App User Canonical Schema Validation

Status: `DONE_EXPRESSIBLE_SCOPE_GAP_IDENTIFIED`

This report records plan #862 and the constraint discovered while implementing its generation gate.

## Implemented validator

`app_sso_app_user_validate_canonical_schema()` validates an enabled project policy against ordinary canonical metadata.

It checks the currently expressible requirements:

- application-user, external-identity, and profile table roles;
- required fields;
- primary `app_user_id` on the application-user table;
- corresponding DataClass records;
- external-identity/profile `app_user_id` DataClass references;
- required generated DBAccess action categories for create, lookup, and profile update;
- disabled policy remains non-applicable and requires no schema.

The project policy now includes optional `schema_roles` with safe canonical defaults and rejects unknown or invalid role mappings.

## Blocking metadata gap

Current canonical table metadata stores a column-level `IsKey` value and DataClass reference hints. It does not preserve full database key/constraint definitions.

Therefore it cannot prove:

- composite `UNIQUE (issuer, subject)`;
- database foreign keys from identity/profile/domain records to `app_user_id`.

The validator reports `metadata_valid_constraint_gap` when every expressible invariant passes, but keeps `ready_for_generation=false` and returns both missing constraint-evidence items as blocking gaps.

This is intentional. Inferring a composite unique constraint from individual column flags or accepting documentation text as database evidence would create a false safety claim.

## Evidence

- Disabled policy is non-applicable.
- Missing tables/DataClasses/DBAccess fail closed.
- A complete expressible fixture passes metadata validation.
- The complete fixture remains blocked from generation by the unique/FK evidence gap.
- Focused schema validator: `3 tests`, `13 assertions`.
- Focused project policy compatibility: `3 tests`, `27 assertions`.

## Plan correction

The first generated server runtime slice must not start until Mtool has a canonical constraint evidence boundary. The next step is a preflight deciding whether to:

1. add normalized project table key/constraint metadata;
2. reuse a reliable existing live-schema snapshot artifact if it can round-trip composite keys and FKs;
3. use both, with imported constraints promoted into canonical metadata.

The selected approach must remain DB-first, exportable, reviewable, and cross-driver-aware.
