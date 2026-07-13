# App-local Sync Identity / SSO Auto-Restore Plan

Status: `DONE_FIRST_SLICE`

This report records the planning decision for the next product lane after the AI workspace onboarding merge.

## Decision

App-local sync should not require every application author to hand-build user identity storage, restore, and sync actor propagation.

The intended default is:

- authenticate through SSO or a compatible local/stub principal;
- normalize the principal into a small App-local user identity snapshot;
- save and restore that snapshot automatically in the App-local store;
- attach actor metadata to managed-operation sync intents and outbox entries;
- let the server-side sync handler receive enough identity context to validate, audit, and route the operation;
- never store passwords, client secrets, access tokens, refresh tokens, raw ID tokens, or full unfiltered IdP claims in App-local storage.

## Scope

The first implementation should be small and evidence-driven.

1. Define an `AppLocalUserIdentity` contract.
2. Add a side-effect-free normalizer from an existing Mtool principal shape.
3. Add safe profile persistence / restoration helpers for App-local SQLite.
4. Extend the managed-operation sync intent with actor metadata.
5. Prove the path in sample30 using an SSO-shaped fixture principal.

## Non-goals

- Do not build IdP administration, user lifecycle, SCIM, invitation, password reset, or MFA enrollment.
- Do not save credentials or bearer tokens in App-local storage.
- Do not require real IdP setup for the first sample30 proof.
- Do not make application-specific authorization rules disappear; custom business rules remain an application boundary.

## Planned work units

| Order | Work unit | Exit condition |
| --- | --- | --- |
| 849 | Contract and plan | `docs/current-plans.md` points to this lane, helper/test scope is clear, and credentials are excluded by contract. |
| 850 | Sample30 first slice | SSO-shaped principal is normalized, persisted/restored in App-local SQLite, attached to sync intent, and observed by the server handoff test. |
| 851 | OIDC handoff boundary | Existing OIDC principal/auth policy shape can feed the App-local identity contract without token persistence. |

## Open design choices

- Whether the first persisted identity helper belongs in the generated App-local artifact or in a shared runtime helper that generated artifacts call.
- Whether outbox should store actor metadata only inside `intent_json` first, or also promote selected actor columns for query/audit.
- Whether browser-local persistence should be IndexedDB-first later, while SQLite remains the deterministic sample/runtime proof.

## Implemented first slice

The first slice keeps the helper shared and deterministic:

- `mtool/app/app_local_user_identity.php` defines `app-local-user-identity-v0`.
- Safe identity snapshots include issuer, subject, display name, email, site roles, project roles, scopes, device id, and cache/auth timestamps.
- Credentials and broad raw claim fields are excluded from persisted snapshots.
- App-local SQLite can create `__app_local_user_identity`, save an identity snapshot, and restore it by `local_user_id`.
- Managed-operation sync intents can carry `actor` metadata.
- The no-code managed-operation bridge passes optional actor metadata into the sync intent.
- Sample30 proves SSO-shaped principal normalization, App-local save/restore, credential exclusion, sync intent actor propagation, and server handoff visibility.
- OIDC principal mapping now preserves safe `issuer`, `subject`, and `email` values so it can feed the App-local identity contract without storing tokens.

## Verification

- `php -l mtool/app/app_local_user_identity.php`
- `php -l mtool/app/managed_operation_sync.php`
- `php -l mtool/app/no_code_managed_operation_bridge.php`
- `php -l mtool/app/auth_oidc.php`
- `php -l mtool/scripts/lib/sample30_no_code_app_local_sync_demo_check.php`
- `php -l tests/Integration/Sample30NoCodeAppLocalSyncDemoTest.php`
- `php -l tests/Integration/OidcAuthContractTest.php`
- `make sample30-pack-runtime-test`: `OK (1 test, 29 assertions)`
- Focused OIDC contract test via sample16 pack: `OK (2 tests, 23 assertions)`

## Remaining demand-driven rollout

- Promote actor fields from `intent_json` into outbox columns only when query/audit UX needs it.
- Add browser IndexedDB/localStorage storage only when a browser-local sample needs it; SQLite remains the deterministic contract proof.
- Wire a real IdP UI flow only when an adoption scenario needs end-to-end browser login, not as part of this contract slice.
