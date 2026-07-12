# SSO App User Runtime Proof

Status: `DONE_LOCAL_READY`

Plans #857 and #858 close the first representative implementation proof for the SSO application-user standard.

## Implemented runtime boundary

`mtool/app/sso_app_user_runtime.php` adds a deterministic SQLite proof for a server-side caller that has already validated the SSO principal.

The runtime provides:

- proof schema for `app_user`, `app_user_external_identity`, and `app_user_profile`;
- unique `(issuer, subject)` external identity mapping;
- opaque application-owned `app_user_id`;
- `jit` and `invitation-only` provisioning modes;
- atomic JIT creation of user, identity mapping, and safe profile;
- repeat-login restoration and allowlisted profile refresh;
- enabled-account check;
- explicit exclusion of forbidden credential/raw-claim fields from policy;
- reuse of a caller-owned transaction when one is already active.

The entry point is deliberately named `app_sso_app_user_resolve_verified_principal()`. It does not validate a token or trust client actor JSON. The server auth boundary must first establish the verified principal.

## Representative evidence

`tests/Integration/SsoAppUserRuntimeTest.php` proves:

- first login creates an app user and identity mapping;
- repeat login returns the same `app_user_id`;
- changed email refreshes the profile without changing identity;
- access token and raw claims are not persisted;
- the same email with a different subject creates a separate user instead of auto-linking;
- invitation-only mode denies an unknown valid identity;
- required identity insert failure rolls back the app user insert;
- application-owned data can reference `app_user_id` through a foreign key.

## Verification

- PHP lint passed for the runtime and test.
- `git diff --check` passed.
- `make test`: `524 tests`, `14647 assertions`, `1 skipped`; exit code 0.
- Focused test after transaction-branch review: `4 tests`, `26 assertions`; exit code 0.

## Supported boundary

This is the first SQLite server-runtime proof, not a claim of full generated deployment support.

Supported now:

- stable standard and deterministic design validation;
- deterministic SQLite schema/resolver proof;
- transaction and failure semantics;
- safe profile allowlist behavior;
- application-domain ownership through `app_user_id`.

Still demand-driven:

- dialect-specific generated DDL/repository output for MySQL and PostgreSQL;
- real IdP/browser UI wiring;
- invitation and identity-link UI;
- tenant/organization policy implementation;
- SCIM and account lifecycle automation;
- migration of existing Mtool operator membership/audit identity columns.

## Integration state

The branch contains one coherent SSO application-user standard slice: planning history, stable documentation, side-effect-free guidance/validation, runtime proof, and tests. It is ready to be committed and then proposed to `develop` without squash being required inside this single semantic commit.
