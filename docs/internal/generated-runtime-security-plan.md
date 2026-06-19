# Generated Runtime Security Plan

Status: `GENERATED_RUNTIME_BASELINE_DONE`

English companion:
This document is the current plan for security work on code emitted by Mtool: generated proxy handlers, OpenAPI output, generated runtime artifacts, and sample/runtime verification. It is separate from Mtool's own admin/lab authorization hardening.

## Boundary

Status: `NEXT`

This phase protects generated runtime output. It answers: when Mtool emits an API, proxy, OpenAPI document, or runtime artifact, what security contract does that emitted code carry?

In scope:

- generated proxy API authentication;
- OpenAPI security scheme output;
- generated handler fail-closed behavior;
- generated artifact visibility rules;
- generated runtime secret reference handling;
- generated runtime HTTP / browser smoke;
- sample pack references that teach generated runtime security behavior.

Out of scope:

- Mtool admin / lab route authorization;
- Mtool project metadata screen permissions;
- member / group lifecycle management;
- IdP administration UI;
- SCIM / invitation / password reset / MFA enrollment;
- product-specific authorization inside an application built on top of generated code.

Mtool's own route authorization is tracked in `docs/internal/authorization-hardening-plan.md`.

## Status Labels

| Label | Meaning |
| --- | --- |
| `DONE` | Implemented, documented, and verified. |
| `I/F_DONE` | Interface / contract shape is implemented and tested; runtime behavior may still be later work. |
| `IN_PROGRESS` | Current active work. |
| `NEXT` | Planned next work. |
| `PARKED` | Explicitly deferred. |
| `NOT_IN_SCOPE` | Not part of generated runtime security. |
| `LEGACY_COMPAT` | Kept for compatibility, not the default for new output. |

## Current Status

| Area | Status | Notes |
| --- | --- | --- |
| Generated proxy API auth v2 policy contract | `DONE` | `auth_policy_version` / `auth_policy_json`, invalid unknown policy, blank new auth invalid, missing secret reference fail-closed. |
| `static-bearer` generated runtime auth | `DONE` | `Authorization: Bearer <token>` with missing / malformed / wrong / env-missing / success coverage. |
| OpenAPI bearer security scheme | `DONE` | Generated OpenAPI emits HTTP bearer security scheme for static bearer endpoints. |
| Auth-required Swagger browser smoke | `DONE` | `sample25-browser-try-it-out-smoke` verifies auth-required Swagger Try It Out path. |
| Public raw artifact visibility policy | `DONE` | Public raw OpenAPI / artifact routes remain absent; authenticated viewer routes are used. |
| Legacy `ProjectToken` / body `TOKEN` | `LEGACY_COMPAT` | Compatibility lane only; not new default. |
| Security sample coverage | `DONE` | `sample16-authenticated-proxy` is the current generated runtime security baseline sample; `sample25` / `sample26` keep legacy ProjectToken compatibility in the ebook CMS lane. |
| Generated runtime auth policy I/F | `I/F_DONE` | `mtool/app/generated_runtime_auth_policy.php` validates policy JSON contracts separately from executable proxy resolver behavior. |
| OIDC JWT bearer for generated APIs | `I/F_DONE` | Policy JSON shape is fixed for issuer, audience, discovery/JWKS, and required claims. Runtime JWT verification remains `NEXT`. |
| OAuth2 client credentials | `NEXT` | Future machine-to-machine generated API auth policy. |
| Upstream auth trust | `PARKED` | Needs trusted proxy / header stripping / network boundary design. |
| HMAC request signature | `PARKED` | Useful for webhooks; not first expansion. |

## Capability / Policy Contract

Generated runtime auth policies should be metadata-driven. Generated code must not embed populated token, password, or client secret values.

| Policy | Status | Generated behavior |
| --- | --- | --- |
| `none` | `NEXT` | Explicit local/tutorial opt-in only. UI and docs should warn. |
| `static-bearer` | `DONE` | Verify `Authorization: Bearer` against a secret reference. Missing secret fails closed. |
| `oidc-jwt-bearer` | `I/F_DONE` | Contract accepts issuer, audience, discovery URL or JWKS URI, and optional required claims. Generated proxy execution remains `NEXT`. |
| `oauth2-client-credentials` | `NEXT` | Treat issued access token as JWT bearer when possible; token issuance stays with IdP. |
| `upstream-auth-trust` | `PARKED` | Trust signed/stripped headers only behind a trusted proxy boundary. |
| `hmac-request-signature` | `PARKED` | Verify request signature and replay window for webhook-style integrations. |
| `ProjectToken` | `LEGACY_COMPAT` | Keep body `TOKEN` compatibility for old outputs and samples where explicitly needed. |

## Planned Order

### 1. Generated Runtime Security Inventory

Status: `I/F_DONE`

Create an inventory of generated output paths, auth policy metadata, OpenAPI output, and smoke coverage.

Completion line:

- list generated proxy handler auth strategies;
- list source output types that can expose API/runtime behavior;
- list current tests and smoke commands;
- no generated behavior changes in this inventory commit;
- generated auth policy contract entry point is `mtool/app/generated_runtime_auth_policy.php`;
- current executable generated proxy resolver intentionally allows only `static-bearer`.

### 2. Static Bearer Consolidation

Status: `DONE`

Make `static-bearer` the documented generated API default for authenticated machine-to-machine sample output.

Completion line:

- sample docs prefer bearer header over body token;
- generated reference output is consistent;
- legacy body `TOKEN` is clearly marked compatibility-only.
- `sample16-authenticated-proxy` is now the static bearer baseline sample.

Security sample decision:

- no new sample is needed for the current baseline because `sample16` already teaches generated runtime auth, OpenAPI bearer output, and fail-closed behavior;
- `sample25` and `sample26` remain ebook CMS teaching samples that include legacy ProjectToken compatibility;
- add or extend a sample only when executable `oidc-jwt-bearer` runtime verification is implemented.

### 3. Secret Reference Boundary

Status: `DONE`

Tighten generated runtime handling of secret references.

Completion line:

- generated metadata stores only references;
- generated artifacts do not contain populated secrets;
- export/import/bundle checks reject populated secret-like fields where applicable.
- project metadata bundle export/import preserves `auth_policy_version` / `auth_policy_json` and rejects populated secret-like fields inside generated auth policy JSON.
- current project-core bundle scope covers DBAccess single-function proxy auth policy refs; custom proxy auth policy refs are outside the project-core bundle because `custom_proxies` is not exported/imported in that bundle scope.

### 4. OIDC JWT Bearer Design Slice

Status: `I/F_DONE / IMPLEMENTATION_NEXT`

Design `oidc-jwt-bearer` for generated APIs. This is not Mtool login SSO; it is API request authentication for emitted runtime endpoints.

Completion line:

- policy JSON shape is documented;
- issuer / audience / JWKS / required claims are defined;
- fail-closed cases are listed;
- first implementation can be planned without changing current static bearer behavior;
- current generated proxy resolver rejects `oidc-jwt-bearer` as executable behavior until JWT verification is implemented.

### 5. Generated Runtime Smoke Expansion

Status: `NEXT`

Add narrow smoke only when a generated runtime auth behavior changes.

Minimum coverage:

- missing credential fails closed;
- malformed credential fails closed;
- wrong credential fails closed;
- missing expected secret/config fails closed;
- valid credential succeeds;
- OpenAPI / Swagger helper sends the correct auth form.

## Commit Policy

Status: `NEXT`

- Keep generated runtime security commits separate from Mtool admin/lab authorization commits.
- Commit inventory separately from generated behavior changes.
- Include generated reference updates and tests in the same commit as behavior changes.
- Do not combine generated runtime security with SSO member management, IdP admin UI, or project route authorization hardening.
