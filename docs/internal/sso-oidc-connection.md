# SSO OIDC Connection

Status: `DONE`

This document records the first complete SSO connection contract for Mtool. Mtool does not manage users, members, groups, invitations, passwords, MFA, or SCIM. Those remain in the external IdP. Mtool only consumes OIDC claims and maps them to site roles and project roles.

## Completion Boundary

Status: `DONE`

SSO is considered complete for the current first production-usable slice when all of the following are true.

| Area | Status | Contract |
| --- | --- | --- |
| OIDC protocol I/F | `DONE` | discovery, authorization redirect, callback, token exchange, JWKS verification, issuer/audience/nonce validation |
| Session principal | `DONE` | OIDC principal uses the same session shape as stub auth, plus `project_roles` |
| Project role claims | `DONE` | group strings such as `dego:project:<PROJECT_KEY>:publisher` map to project roles |
| Permission source priority | `DONE` | site admin, external claims, local override, legacy fallback |
| Smoke test | `DONE` | `make mtool-oidc-login-smoke` verifies redirect, callback, session principal, and project role storage with a mock IdP |
| Audit first slice | `DONE` | OIDC login and audited permission decisions record actor source, project key, capability, role source, and result |
| Member management | `PARKED_EXTERNAL` | handled by Keycloak, Authentik, Zitadel, Dex, Authelia, or another OIDC-compatible IdP |

Route-wide enforcement for every existing project edit screen is intentionally not part of this SSO completion boundary. The current first slice fixes the policy contract and enforces the sensitive source output publish/download lane. Broad route-by-route replacement should be a separate authorization hardening phase so that existing 404 / validation behavior is not accidentally converted into 403 responses.

## Mtool Environment

Set these values for the admin site. The lab site uses the same keys with the `LAB_` prefix in `.env.example`.

```env
ADMIN_AUTH_MODE=oidc
ADMIN_AUTH_OIDC_ISSUER=https://idp.example.test/realms/dego
ADMIN_AUTH_OIDC_CLIENT_ID=mtool-admin
ADMIN_AUTH_OIDC_CLIENT_SECRET=change-this-client-secret
ADMIN_AUTH_OIDC_REDIRECT_URI=http://localhost:8081/auth/oidc/callback
ADMIN_AUTH_OIDC_SCOPES=openid,profile,email
ADMIN_AUTH_OIDC_GROUPS_CLAIM=groups
ADMIN_AUTH_OIDC_ADMIN_GROUPS=dego-admin
ADMIN_AUTH_OIDC_CONFIG_GROUPS=dego-config
ADMIN_AUTH_OIDC_LAB_GROUPS=dego-lab
ADMIN_AUTH_OIDC_PROJECT_ROLE_GROUP_PREFIX=dego:project:
ADMIN_AUTH_OIDC_DEFAULT_ROLES=
```

## Keycloak Minimal Setup

Status: `DONE`

Use this as the reference setup. Names can differ as long as the emitted claims match the Mtool contract.

1. Create a realm, for example `dego`.
2. Create a confidential OIDC client, for example `mtool-admin`.
3. Set valid redirect URI to `http://localhost:8081/auth/oidc/callback` for local admin testing.
4. Enable standard authorization code flow.
5. Add a groups mapper to the ID token.
   - claim name: `groups`
   - full group path: off for the simplest contract
6. Create groups:
   - `dego-config`
   - `dego-admin`
   - `dego:project:CLAIM-FIRST:publisher`
7. Assign those groups to the test user.
8. Copy the client secret to `ADMIN_AUTH_OIDC_CLIENT_SECRET`.

The important part is not Keycloak itself. It is that the ID token contains a `groups` claim with site-role groups and project-role groups.

## Authentik / Other IdP Notes

Status: `DONE`

For Authentik, Zitadel, Dex, Authelia, or another OIDC provider, keep the same Mtool-side contract.

- issuer must match the discovery document issuer.
- client ID must appear in the ID token audience.
- redirect URI must be `/auth/oidc/callback`.
- group or role claim must be readable as a string list.
- project role entries must use the configured prefix, defaulting to `dego:project:`.

If an IdP emits a different claim name, set `APP_AUTH_OIDC_GROUPS_CLAIM`. If it emits a different prefix, set `APP_AUTH_OIDC_PROJECT_ROLE_GROUP_PREFIX`.

## Verification

Status: `DONE`

Run the built-in mock IdP smoke:

```bash
make mtool-oidc-login-smoke
```

Expected checks:

- mock discovery is reachable.
- Mtool redirects from `/login` to the IdP authorization endpoint.
- callback exchanges the code for an ID token.
- ID token is verified by JWKS.
- session principal contains the OIDC subject and display name.
- session principal contains project roles derived from group claims.

Run the narrow contract tests when touching the mapper or permission logic:

```bash
bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample16-authenticated-proxy/compose.yaml \
  --run-script=./sample/tutorials/sample16-authenticated-proxy/run.sh \
  --phpunit-target=/var/www/tests/Integration/OidcAuthContractTest.php

bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample16-authenticated-proxy/compose.yaml \
  --run-script=./sample/tutorials/sample16-authenticated-proxy/run.sh \
  --phpunit-target=/var/www/tests/Integration/ProjectIdentityMembershipPermissionTest.php
```
