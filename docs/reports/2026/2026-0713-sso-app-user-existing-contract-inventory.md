# SSO App User Existing Contract Inventory

Status: `DONE`

This report completes plan #854. It maps the current Mtool contracts to the selected SSO application-user standard and fixes the first implementation boundary before runtime or generator changes.

## Inventory result

Mtool already has the authentication, safe principal handoff, generated database access, and transaction primitives needed for this lane. It does not yet have a canonical server-side application-user model or a mapping from an external SSO identity to a stable application-owned user ID.

The missing product contract is therefore narrower than a new SSO implementation:

```text
validated OIDC principal
    -> external identity lookup (issuer, subject)
    -> stable app_user_id
    -> profile refresh and application-owned user data
```

## Existing contract map

| Existing area | Current evidence | Reuse decision | Gap or caution |
| --- | --- | --- | --- |
| OIDC protocol and principal | `auth_oidc.php` validates configured issuer, audience, nonce and signature path, then exposes issuer, subject, display name and email | Reuse as the trusted principal source after protocol validation | Principal `id` is currently subject; callers must not treat it as a globally unique app user ID |
| Auth foundation | Normalizes principal, site roles and project roles and evaluates permissions | Reuse for Mtool operator authorization | Its normalized shape omits issuer/email and is not an application-user persistence contract |
| Project identity membership | Stores `principal_source + principal_subject` roles in Mtool config DB | Keep for Mtool project administration only | It omits issuer, so it is not the standard application-user mapping and can collide across multiple issuers |
| Audit events | Records `actor_login_id + actor_source`, with sanitized metadata | Reuse existing audit behavior where compatible | Additive future actor fields may be needed; current columns do not identify issuer or canonical `app_user_id` |
| App-local user identity | Derives a safe identity from issuer and subject, excludes credentials, persists/restores SQLite snapshot and emits sync actor metadata | Reuse as the offline/cache handoff | Derived `local_user_id` is not the server's durable application-user ID; device handling is cache behavior, not account identity |
| Managed-operation sync actor | Carries the safe actor snapshot in sync intent JSON | Reuse and later add `app_user_id` after server resolution | Client-provided actor data is context, not final server authorization evidence |
| Generated DBAccess | Supports generated reads/writes for MySQL, PostgreSQL and SQLite user DB contracts | Reuse for generated application-user repositories | No standard app-user schema or generated identity resolver exists yet |
| Transaction Full | Composite caller can begin/commit/rollback multiple generated DBAccess writes on one shared connection | Reuse for JIT app-user plus identity-mapping creation | The resolver must use one target DB connection; Mtool config DB and application DB cannot share the transaction |
| Samples | Sample30 proves App-local safe identity and actor handoff; OIDC tests prove safe claim mapping | Reuse as prerequisites | No sample proves canonical server `app_user_id`, JIT creation, repeat login, or app-owned user profile/data |

## Boundary decisions

### Storage ownership

The standard application-user records belong in the generated application's user database, not Mtool's config database.

Reasons:

- application business rows need a normal foreign key to `app_user_id`;
- application retention, export, deletion and migration remain application-owned;
- a generated deployment must not depend on Mtool's administration database;
- JIT user and identity creation can share one transaction with the target user database.

### Conceptual minimum schema

The permanent design may refine names and dialect details, but the minimum relationship is fixed:

| Record | Required purpose |
| --- | --- |
| `app_user` | Own opaque stable `app_user_id`, account status and lifecycle timestamps |
| `app_user_external_identity` | Map normalized issuer and subject to exactly one `app_user_id`; retain provider/config reference where useful |
| `app_user_profile` or selected `app_user` columns | Store only the declared safe profile fields and their ownership policy |
| Application domain tables | Reference `app_user_id` for owner/actor relationships |

The uniqueness invariant is `(normalized_issuer, subject)`. Email, username and display name are never identity keys. Multiple external identities may point to one app user only through an explicit verified link operation.

### Provisioning default

The standard should support both modes, with an explicit project choice:

- `jit`: a valid unknown SSO identity creates an enabled app user and external identity mapping in one transaction;
- `invitation-only`: a valid unknown identity is denied or routed to enrollment until a pre-authorized mapping exists.

For the representative proof, use `jit`. Mtool must not infer account linking from matching email.

### Profile ownership

Each generated project must classify persisted fields:

- SSO-managed cache: allowlisted fields refreshed from a verified principal;
- application-managed profile: values controlled by application logic or user input;
- authorization data: server-authoritative roles/memberships, not trusted from stale local cache;
- forbidden persistence: credentials, tokens, secrets and unfiltered claims.

An IdP refresh must not overwrite application-managed fields unless the project explicitly selects that policy.

### Identifier handoff

- Before server resolution, App-local code may use its deterministic `local_user_id` as a cache key.
- After server resolution, application business writes and server audit should use `app_user_id`.
- Sync actor context may carry both identifiers, issuer and subject, but the server revalidates identity and authorization.
- An IdP migration changes identity mappings, not domain-row owner IDs.

## Gaps classified by priority

### Required for the standard path

1. Permanent design contract for identifiers, schema, normalization and lifecycle.
2. Side-effect-free principal-to-app-user resolution plan and result shape.
3. Transactional JIT create-or-restore implementation for generated user DB access.
4. Safe, allowlisted profile refresh behavior.
5. Representative evidence that application-owned data references `app_user_id`.
6. Mtool/AI design guidance that asks provisioning and profile-ownership decisions.

### Compatibility hardening, not required before the first proof

- Add issuer/canonical app-user context to Mtool audit events.
- Evolve Mtool project identity membership beyond `auth_source + subject` if multi-issuer operator administration is required.
- Add `app_user_id` to App-local identity contract after server resolution.
- Promote actor fields from sync intent JSON to queryable outbox columns.

### Explicit custom or demand-driven scope

- Identity link/unlink and account merge UI.
- SCIM provisioning and deprovisioning.
- Organization/tenant mapping beyond a configured claim policy.
- Legal retention, erasure and anonymization workflows.
- Real IdP administration, MFA and password lifecycle.
- Cross-database distributed transactions.

## First implementation boundary

The representative implementation should use one generated application database and one deterministic SSO-shaped verified principal fixture. It must prove:

1. first login creates `app_user` and `app_user_external_identity` atomically;
2. failure of either required insert rolls back both;
3. repeat login resolves the same `app_user_id` without duplicating the user;
4. an allowlisted mutable profile field can refresh without changing `app_user_id`;
5. changed email does not create or merge an account;
6. an application-owned row is stored using `app_user_id`;
7. tokens, secrets and raw claims never enter persisted user/profile records;
8. unknown identity under invitation-only policy fails closed.

SQLite is suitable for the first deterministic contract proof. Cross-driver generated DBAccess evidence should be added only after the contract shape is stable, using the existing user DB contract test infrastructure.

## Decision for the next plan

Plan #855 should now create the permanent Mtool/AI-facing design standard. It should keep the conceptual model independent of OIDC vendor and SQL dialect, define normalization and lifecycle rules precisely, and include the decisions Mtool asks an application author before generation.
