# SSO App User Generated Integration Preflight

Status: `DONE`

This report completes plan #859. It defines how the proven SSO application-user standard should enter Mtool project metadata and generated output without bypassing the DB-first architecture.

## Decision summary

The integration has two layers:

1. ordinary canonical DB metadata owns the application-user schema;
2. one explicit project-level SSO app-user policy describes how that schema is used by authentication and generated runtime.

Mtool must not create a hidden second schema lane for SSO users. `app_user`, external identity mapping, profile, membership, and domain foreign keys remain normal Table/DataClass/DBAccess definitions that can be inspected, edited, exported, imported, and generated through the existing DB-first workflow.

## Metadata ownership

### Project-level policy

The SSO app-user policy belongs to the project, not an individual Source Output or DBAccess function.

Reasons:

- server API, browser/client output, App-local output, and sync output must share one identity model;
- provisioning and profile ownership are application decisions rather than endpoint decisions;
- Source Output-specific copies would drift;
- the policy must be present in project metadata bundle export/import;
- secrets and IdP client credentials remain deployment configuration, outside this policy.

Recommended logical contract:

```json
{
  "contract_version": "sso-app-user-project-policy-v1",
  "enabled": true,
  "auth_mode": "oidc",
  "provisioning_mode": "jit",
  "provider_key": "primary-oidc",
  "sso_profile_fields": ["display_name", "email"],
  "application_profile_fields": ["nickname"],
  "user_owned_data": ["saved_item"],
  "tenant_boundary": "",
  "lifecycle_custom_boundary": ["identity-link", "retention"]
}
```

This object is non-secret. It must reject token, password, secret, raw-claim, and email-auto-link configuration.

### Persistence

Add a dedicated one-row-per-project config record rather than embedding JSON in `projects.description` or an unrelated auth-policy field.

Logical storage:

- `project_app_user_policies`
  - `project_id` unique foreign key;
  - `contract_version`;
  - `enabled`;
  - normalized policy JSON;
  - timestamps.

The normalized policy JSON is acceptable for the bounded field set because it is versioned, validated as a whole, non-secret, and not queried as business data. Project identity membership and generated endpoint auth policy remain separate concerns.

## Existing metadata bundle integration

Add an optional `app_user_policy` section and `app-user-policy.json` file to the project-core bundle.

Compatibility rules:

- an absent section means SSO app-user generation is disabled; existing projects are unchanged;
- importing an older bundle without the section preserves the safe disabled/default state for a new target project;
- replacing an existing project from an older bundle must not silently erase an existing app-user policy without an explicit replace option or version-aware migration decision;
- secrets remain excluded;
- import preview validates the policy and reports whether it will create, replace, preserve, or disable it;
- bundle schema evolution must remain explicit and tested rather than accepting unknown fields silently.

## Schema ownership and validation

The SSO policy references canonical table roles rather than generating private tables outside metadata.

First standard role mapping:

| Role | Default physical name | Required invariant |
| --- | --- | --- |
| application user | `app_user` | stable `app_user_id`, status |
| external identity | `app_user_external_identity` | unique issuer + subject, FK to app user |
| profile | `app_user_profile` | one profile per app user; safe field ownership |
| domain owner references | project-defined | FK/reference to `app_user_id` |

Projects may use different physical names only through explicit role mapping. The validator resolves roles to canonical metadata and checks fields, unique keys, relationships, provisioning requirements, and forbidden persisted fields.

Mtool may offer a proposal that creates the standard tables through normal metadata operations, but generation must not mutate project schema implicitly. The user or AI reviews and applies the proposal first.

## Generator and runtime responsibilities

### Mtool design/validation layer

- recognize an enabled SSO app-user policy;
- run the existing standard guidance and validation contract;
- validate the canonical schema role mapping;
- emit blocking diagnostics before generation if required identity/schema invariants are missing;
- create a reviewable schema/DBAccess proposal when requested, without auto-applying it.

### Generated user DB layer

- schema continues to come from canonical project table metadata and normal migration/deployment artifacts;
- generated DBAccess owns lookup/create/profile operations;
- the composite resolver owns transaction begin/commit/rollback for JIT;
- domain records refer to `app_user_id` through ordinary generated DBAccess.

### Generated server runtime

- receives only a server-verified principal from the selected auth adapter;
- maps `(issuer, subject)` to `app_user_id` using generated DBAccess;
- applies JIT or invitation-only policy;
- refreshes only allowlisted SSO-managed fields;
- returns canonical actor context containing `app_user_id`;
- does not validate credentials through client-supplied actor JSON.

### Generated client/App-local layer

- may cache the safe App-local identity and `local_user_id`;
- stores returned `app_user_id` only after server resolution;
- sends actor context for sync correlation, while server re-establishes authority;
- never stores access/refresh/ID tokens in application identity records.

## Dialect scope

The current runtime proof contains SQLite-specific DDL and upsert behavior. Generated integration must separate:

- policy and resolver semantics, which are dialect-independent;
- schema and DBAccess SQL, which use the existing MySQL/PostgreSQL/SQLite generation contracts;
- deterministic contract tests, which may remain SQLite-first;
- cross-driver qualification, which should cover at least MySQL and SQLite before claiming the standard generated path, with PostgreSQL added through the existing user-DB contract lane when the generated queries are stable.

Do not copy the proof helper unchanged into every generated output and call it cross-driver support.

## Migration and existing-project rules

- Policy is opt-in. Existing projects generate exactly as before when it is absent or disabled.
- Enabling policy does not create or alter user DB tables automatically.
- Validator reports missing role mappings and offers a reviewable proposal.
- Existing user tables can be mapped if they satisfy the invariants; forced renaming is not required.
- Existing email-keyed user models require an explicit migration plan and cannot be auto-upgraded merely by enabling the policy.
- Runtime output generation fails closed when policy is enabled but schema/DBAccess prerequisites are incomplete.
- Disabling policy stops SSO app-user runtime emission but does not drop user data or tables.

## First generated slice

The next implementation slice should be the project policy contract, not the full generator.

1. Add a side-effect-free normalizer/validator for `sso-app-user-project-policy-v1`.
2. Add repository storage for one optional policy per project.
3. Add config DB bootstrap/migration support for SQLite and MySQL-compatible config stores.
4. Add project metadata bundle export/import preview/apply behavior.
5. Prove absent-policy compatibility and forbidden-field rejection.

Only after that contract is stable should the next slice add schema role validation and generated runtime artifacts.

## Planned continuation

| Order | Work unit | Exit condition |
| --- | --- | --- |
| 859 | Generated integration preflight | Metadata ownership, schema/generator boundary, dialect scope, migration rule, and first slice are explicit. |
| 860 | Project policy contract | Versioned normalizer/validator and repository persistence exist with focused tests and no implicit user DB mutation. |
| 861 | Metadata bundle integration | Optional policy round-trips through export/import preview/apply with backward-compatible absence behavior. |
| 862 | Canonical schema role validation | Enabled policy can validate required tables, keys, relationships, safe fields, and DBAccess prerequisites. |
| 863 | First generated server runtime slice | One output uses generated DBAccess plus composite transaction resolver and returns canonical `app_user_id`. |
| 864 | Client/App-local handoff | Server-resolved `app_user_id` can join the existing safe App-local/sync actor contract without becoming client authority. |
| 865 | Cross-driver qualification and checkpoint | Declared driver evidence, docs, supported boundary, and integration state are recorded. |

## Non-goals for the first slice

- No real IdP UI or vendor-specific setup.
- No hidden user table creation during output generation.
- No automatic migration from email-keyed users.
- No identity-link, merge, SCIM, retention, or tenant UI.
- No claim that the SQLite proof helper is already a portable generated runtime.
