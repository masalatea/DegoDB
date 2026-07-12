# SSO App User Standard Direction Plan

Status: `PLANNED`

This report records the product direction selected after the App-local sync identity first slice.

## Product goal

When an application author chooses SSO, Mtool should make the ordinary user-data path easy to design correctly:

- recognize the same signed-in person consistently;
- create or restore an application user without application-specific authentication glue;
- provide a clear place for profile data and application-owned user data;
- keep authentication credentials and raw IdP claims out of application user storage;
- give Mtool and AI a standard direction to follow while leaving unusual identity and business rules customizable.

This is a design standard and generation guideline. Mtool remains free to generate a different schema when the application has a concrete reason.

## Standard identity model

The standard model separates authentication identity from application user identity.

```text
verified SSO principal
  (provider/issuer, subject)
            |
            v
external identity mapping  ---->  app user
                                      |
                                      +--> standard profile
                                      +--> application-owned user data
                                      +--> roles/memberships where required
```

### External identity

- OIDC identity is recognized by the normalized pair `(issuer, subject)`.
- A deployment may assign an internal `provider_key` to stable IdP configuration, but it must not replace validation of issuer and subject.
- `email`, display name, login name, and mutable claims are not user identifiers.
- Raw claims, access tokens, refresh tokens, ID tokens, passwords, and client secrets are not persisted in the user profile.

### Application user

- The application owns an opaque, stable `app_user_id` independent of the IdP.
- Business tables refer to `app_user_id`, not directly to email or OIDC subject.
- One app user may later have multiple external identities, but linking identities requires an explicit verified workflow. Matching email alone must never link accounts automatically.
- Replacing or migrating an IdP changes identity mappings; it does not require rewriting all business rows.

### User information classes

| Class | Examples | Default owner and behavior |
| --- | --- | --- |
| Authentication identity | issuer, subject, provider key | Server/auth boundary; immutable mapping key |
| Safe SSO profile cache | display name, email, avatar reference, locale | Refresh from verified principal using an explicit field policy |
| Application profile | nickname, preferences, onboarding state | Application-owned and user-editable according to business rules |
| Business data | documents, orders, saved items | Separate domain tables referencing `app_user_id` |
| Authorization | roles, project memberships, scopes | Server-authoritative; do not trust stale App-local copies for final decisions |
| Credentials and raw claims | tokens, password, client secret, unfiltered claims | Never store in application/App-local user records |

## Standard lifecycle

1. Validate the SSO login and obtain a trusted principal.
2. Normalize issuer and subject using the configured provider policy.
3. Find the external identity mapping.
4. If it exists, restore the associated app user.
5. If it does not exist and just-in-time provisioning is allowed, create the app user and mapping in one transaction.
6. Refresh only allowlisted SSO-managed profile fields.
7. Load application-owned user information separately.
8. Use `app_user_id` as the actor/owner identifier in application data; retain issuer and subject only where authentication audit requires them.

If provisioning is disabled, an unknown valid SSO principal is denied or sent to an explicit enrollment/invitation flow. Loss of IdP access does not automatically delete application data. Account disable, retention, erasure, merge, and identity-link operations remain explicit lifecycle policies.

## Relationship to the completed App-local identity slice

The existing `app-local-user-identity-v0` is a safe cached principal and sync actor handoff. It is not yet the canonical server-side application-user model.

The current derived `local_user_id` is suitable for deterministic App-local cache lookup. Business-domain ownership should use the future stable `app_user_id` returned by the server mapping layer. A later contract version may carry both values without breaking the current proof.

## Planned work units

| Order | Work unit | Exit condition |
| --- | --- | --- |
| 853 | Select SSO app-user direction | This lane is selected from the demand-driven inventory and its scope is recorded. |
| 854 | Existing-contract inventory | Current OIDC principal, identity membership, App-local identity, generated DBAccess, and user-data examples are mapped to the standard model; gaps and reusable parts are explicit. |
| 855 | Stable design standard | A permanent Mtool/AI-facing design document defines identifiers, conceptual schema, provisioning, profile ownership, lifecycle, security boundaries, and customization points. |
| 856 | Mtool design guidance contract | Mtool/AI prompts or validation guidance can identify an SSO application and recommend the standard model while asking only decisions that materially change it. |
| 857 | Representative implementation proof | One bounded server/client or generated sample proves transactional JIT user creation, repeat-login restoration, safe profile refresh, and application-owned user data keyed by `app_user_id`. |
| 858 | Lane checkpoint | Test evidence, supported boundary, documentation entrances, and any remaining demand-driven work are recorded before integration. |

## Questions to settle during inventory

- Which existing server-side store should own app users and external identity mappings?
- Which profile fields are SSO-managed, application-managed, or user-overridable?
- Is JIT provisioning the default, or must generated projects choose explicitly between JIT and invitation-only?
- How should tenant/organization identity be represented when the issuer alone does not express the application's tenant boundary?
- Which audit fields are required without duplicating sensitive claims?
- How should `app_user_id` be handed into the existing managed-operation actor contract and App-local cache?
- Which identity-link, unlink, disable, merge, retention, and erasure operations are supported automatically, and which stay custom?

## Non-goals

- Mtool does not become an IdP.
- The first proof does not need SCIM, MFA enrollment, password reset, or every commercial SSO provider.
- Email-based automatic account linking is not supported.
- Client-side restored identity is not sufficient authorization evidence for server writes.
- The standard does not attempt to automate every exceptional account lifecycle rule.
