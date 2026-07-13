# Generated SSO DBAccess Invocation Preflight

## Finding

Plan #862E proves table, DataClass, action-type, unique-key, and foreign-key readiness. It does not yet identify callable DBAccess operations precisely enough for generated runtime orchestration. `select`, `insert`, and `update` action labels alone cannot distinguish external-identity lookup from an unrelated select or define the parameter/result contract. Generating by choosing the first matching action would be ambiguous and unsafe.

## Decision

The first generated slice uses a deterministic standard-operation convention derived from the configured schema roles. It does not add function names to the SSO policy and does not guess among action-compatible functions.

For the default roles, the required generated DBAccess operations are:

| Operation | Class role | Required function | Input/result responsibility |
| --- | --- | --- | --- |
| external identity lookup | external identity | `SelectAppUserExternalIdentityByIssuerSubject` | accepts normalized issuer and subject; returns at most one identity with `app_user_id` |
| application user read | application user | `SelectAppUserByAppUserId` | returns status for the resolved server-owned ID |
| application user create | application user | `InsertAppUser` | accepts generated `app_user_id` and enabled status |
| external identity create | external identity | `InsertAppUserExternalIdentity` | writes provider, issuer, subject, app-user ID, and authentication timestamps |
| profile write | profile | `UpsertAppUserProfile` | writes only allowlisted SSO-owned profile fields for `app_user_id` |
| identity touch | external identity | `UpdateAppUserExternalIdentityLastAuthenticatedAt` | updates only the matched identity timestamp |

When physical role names differ, class and operation names are derived through the existing generated-name mapping. The validator must report every missing or ambiguous operation before artifact generation.

## Runtime artifact

The first artifact is one generated server-side composite resolver, separate from generated DBAccess classes. It:

1. accepts only a server-verified principal and normalized non-secret policy;
2. resolves `(issuer, subject)` through the exact lookup operation;
3. rejects disabled application users;
4. restores and refreshes allowlisted fields when the identity exists;
5. applies invitation-only rejection before writes;
6. for JIT, opens one transaction on the shared generated runtime DB wrapper;
7. rechecks identity inside the transaction;
8. calls user, identity, and profile DBAccess operations;
9. commits only after every required operation succeeds, otherwise rolls back;
10. returns a canonical actor result containing server-owned `app_user_id` and no credential/token material.

The DBAccess classes remain transaction-unaware. The composite resolver owns `beginTransaction`, `commit`, `rollBack`, and `inTransaction`, matching the existing Transaction Full design.

## Failure contract

- Missing readiness evidence or required operation: no resolver artifact is emitted.
- Invalid principal/policy: fail before DB reads or transaction start.
- Invitation-only unknown identity: fail without writes.
- Unexpected DBAccess result, exception, commit failure, or post-first-write failure: return failure and roll back when the resolver owns the transaction.
- Existing caller-owned transaction: do not commit or roll back the caller's transaction; return failure to the caller for transaction-wide handling.
- No email-based identity lookup or linking.

## Scope

Plan #863B adds the exact-operation validation and deterministic artifact contract. The following slice binds the artifact to a qualified generated DBAccess fixture and proves first login, repeat login, and rollback. Client/App-local propagation remains #864. Cross-driver claims remain #865.
