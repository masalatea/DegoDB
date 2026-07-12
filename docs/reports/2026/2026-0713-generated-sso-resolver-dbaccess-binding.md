# Generated SSO Resolver DBAccess Binding

## Outcome

Plan #863C turns the exact operation contract into an executable composite resolver and binds it to one generated-DBAccess-compatible SQLite fixture sharing a single PDO transaction context.

The resolver:

- accepts only a server-verified principal shape;
- normalizes issuer and subject;
- persists only allowlisted SSO profile fields;
- resolves existing identity and application-user status;
- creates app user, external identity, and profile for JIT;
- refreshes profile and last-authenticated time on repeat login;
- returns a canonical actor containing server-owned `app_user_id`;
- owns transaction begin/commit/rollback only when no caller transaction is active.

## Evidence

- First login creates one application user, identity, and profile and commits all rows.
- Repeat login restores the same `app_user_id` and updates the profile.
- An `access_token` present in the principal is not included in persisted profile JSON.
- A forced profile-write failure after user and identity inserts rolls both earlier writes back; neither partial row remains.
- Missing runtime transaction methods or operation bindings fail before execution.

The full integration suite passed with 542 tests and 14868 assertions; one existing test is skipped.

## Remaining #863 scope

This slice proves execution against a generated-DBAccess-compatible fixture but does not yet place the resolver artifact into a real Source Output generated file set. Plan #863D integrates gated artifact emission into the output runtime file plan and proves that disabled, absent, or blocked SSO projects emit no resolver file.
