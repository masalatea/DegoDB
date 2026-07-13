# SSO App User Project Policy Contract

Status: `DONE`

This report completes plan #860 after the generated integration preflight.

## Implemented contract

`sso-app-user-project-policy-v1` is now a versioned, non-secret, project-level policy.

The policy records:

- enabled/disabled opt-in state;
- auth mode;
- JIT or invitation-only provisioning;
- non-secret provider key;
- SSO-managed and application-managed profile fields;
- application user-owned data names;
- tenant boundary description;
- explicit custom lifecycle boundary.

The side-effect-free normalizer rejects:

- unsupported contract versions;
- credential, token, secret, or raw-claim profile fields;
- overlapping SSO/application profile ownership;
- email-based automatic identity linking;
- enabled policy without supported auth/provisioning/provider decisions.

Disabled policy may retain configuration for review but emits a warning and does not opt the project into generation.

## Repository and bootstrap

- `project_app_user_policies` stores one optional policy per config-DB project.
- The record contains normalized policy JSON and no IdP secret.
- Fetching a project with no row returns `item=null`, preserving existing-project behavior.
- Upsert validates before opening a database write path.
- The repository does not access or mutate the generated user DB.
- Config DB bootstrap now requires the table and validates its core columns.
- MariaDB DDL is converted by the existing SQLite bootstrap path for deterministic tests.

## Evidence

`SsoAppUserProjectPolicyTest` covers:

- absent-policy compatibility;
- enabled policy normalization and round-trip;
- explicit disabled-policy behavior;
- invalid/unsafe policy rejection without persistence.

Verification:

- PHP lint passed for policy, repository, and test.
- `git diff --check` passed.
- focused test: `3 tests`, `26 assertions`.
- `make test`: `527 tests`, `14673 assertions`, `1 skipped`; exit code 0.

## Next boundary

Plan #861 should add the optional policy to project metadata bundle export/import. Older or absent-policy projects must remain unchanged, and import preview must make preserve/replace/disable behavior visible before apply.
