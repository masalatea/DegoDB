# 2026-06-17 API Auth Inventory Plan

## Status

- status: `PLANNED / WAITING`
- scope: API 認証棚卸しと modern auth 方針
- primary target: generated proxy / OpenAPI / admin-lab session auth
- legacy policy: 旧実装方式は参照に留め、新規 default にはしない
- implementation gate: SQLite / config-store 対応が安定してから DB schema / repository / seed / tutorial 実装へ進む

## Resume Link

実装再開時はこの report を入口にする。まず SQLite / config-store 関連の未完了変更が落ち着いていることを確認し、その後 `Phase 1. Policy Contract` から再開する。

Related SQLite / config-store reports:

- [2026-0617-lightweight-sqlite-persistence-plan.md](2026-0617-lightweight-sqlite-persistence-plan.md)
- [2026-0617-sql-dialect-inventory.md](2026-0617-sql-dialect-inventory.md)
- [2026-0617-sql-dialect-helper-first-slice.md](2026-0617-sql-dialect-helper-first-slice.md)
- [2026-0617-config-store-folder-profile-first-slice.md](2026-0617-config-store-folder-profile-first-slice.md)
- [2026-0617-config-store-sqlite-bootstrap-first-slice.md](2026-0617-config-store-sqlite-bootstrap-first-slice.md)
- [2026-0617-lightweight-runtime-lane-first-slice.md](2026-0617-lightweight-runtime-lane-first-slice.md)

## Summary

API 認証は、旧実装の互換方式を直接伸ばすのではなく、次の 2 面に分けて再設計する。

- `admin/lab` site の人間向け session auth
- generated proxy endpoint の API auth

現行の `ProjectToken` は fail-closed hardening 済みだが、request body の `TOKEN` に依存する legacy compatibility lane である。新規設計では `proxy_auth_v2` のような canonical policy を追加し、Bearer token / OIDC JWT / upstream auth trust を first-class に扱う。

ただし実装は SQLite / config-store 対応と競合しやすい。auth v2 は DB metadata、bootstrap schema、repository、sample seed に触る可能性が高いため、当面は設計更新までに留め、SQLite 対応が安定してから実装する。

## Current Inventory

### Session auth

- implementation:
  - `mtool/app/auth.php`
  - `mtool/app/session.php`
  - `mtool/app/middleware.php`
- current mode:
  - `.env` 由来の stub username / password
  - login 後の principal は `$_SESSION['app_principal']`
  - principal fields: `id`, `display_name`, `roles`, `auth_source`, `site`
- route guard:
  - `mtool/app/router.php::app_route_requires_auth()`
  - `dashboard`, `projects`, `experiments`, `lab_swagger`, `lab_published_single_proxy`, `project_source_output_download` などを auth-required route として扱う
- cookie:
  - session cookie は `HttpOnly`
  - `SameSite=Lax`
  - HTTPS または `X-Forwarded-Proto: https` で `secure`

### Generated proxy auth

- implementation:
  - `mtool/app/project_output_proxy_generator.php`
  - generated handler の `authorizeRequest()`
- current strategies:
  - `no-security`
  - `manual`
  - `project-token`
  - `get-function`
  - `project-token-or-get-function`
  - `login-cookie-token`
- current secret:
  - `MTOOL_PROXY_PROJECT_TOKEN`
- current hardening:
  - `project-token` は env missing で fail-closed
  - missing / empty / wrong `TOKEN` は reject
  - `project-token-or-get-function` は token path 失敗時に get-function fallback を維持

### Swagger / OpenAPI auth helper

- implementation:
  - `mtool/app/lab_swagger_service.php`
  - `mtool/app/lab_swagger_page.php`
- current helper fields:
  - `TOKEN`
  - `LOGIN_COOKIE_TOKEN`
- current exposure policy:
  - OpenAPI viewer は authenticated lane
  - generated proxy preview route も authenticated lane
  - public raw OpenAPI route / public alias key route は current では持たない

### Canonical metadata

- `project_db_access_functions.single_proxy_auth_type`
  - single-function proxy 用
  - legacy enum として `''`, `ProjectToken`, `GetFunc`, `ProjectTokenOrGetFunc`, `NoSecurity`, `Manual`, `LoginCookieToken` を許容
- `project_custom_proxies.auth_type`
  - multi-step custom proxy 用
  - single-function proxy metadata とは責務を分ける
- issue:
  - current enum は legacy compatibility と current API policy が混在している
  - blank default を新規 row の semantic として残すべきではない

## Risk Notes

- request body に `TOKEN` を入れる方式は、HTTP API としては現代的ではない。
- `LoginCookieToken` は site session と API auth の責務を混ぜやすく、external public API default に向かない。
- `GetFunc` / `ProjectTokenOrGetFunc` は legacy 互換としては残せるが、新規設計の primary lane にしない。
- `NoSecurity` は明示 opt-in の検証・内部用に限定し、UI / OpenAPI viewer で目立たせる。
- secret / password / token は project metadata bundle の default export に含めない。

## Recommended Auth Model

### `none`

- 用途:
  - local tutorial
  - private lab-only endpoint
  - 明示 public test
- rule:
  - default にはしない
  - UI / OpenAPI viewer で warning を出す

### `static-bearer`

- 用途:
  - simplest machine-to-machine auth
  - current `ProjectToken` の置き換え先
- transport:
  - `Authorization: Bearer <token>`
- secret source:
  - env reference
  - deploy secret
  - future secret provider
- rule:
  - missing expected token は fail-closed
  - request body token は新規 canonical では使わない

### `oidc-jwt-bearer`

- 用途:
  - SSO / IdP と合わせた API auth
  - service-to-service と user-delegated API の共通土台
- transport:
  - `Authorization: Bearer <JWT>`
- validation:
  - issuer allowlist
  - audience check
  - expiry / not-before
  - signature verification with JWKS
  - required claims / role mapping
- rule:
  - OIDC Discovery URL は deploy config で固定し、untrusted dynamic discovery は避ける

### `oauth2-client-credentials`

- 用途:
  - machine-to-machine
  - CI / backend worker / integration client
- implementation note:
  - runtime 側では access token を `oidc-jwt-bearer` と同じ validator に通す
  - token issuance は IdP 側に任せる

### `upstream-auth-trust`

- 用途:
  - reverse proxy / API gateway / ingress が認証を完了し、DegoDB runtime は trusted headers だけを見る場合
- rule:
  - trusted proxy network / header signing / header stripping policy が必須
  - direct access できる deployment では使わない

### `session-bridge`

- 用途:
  - authenticated `lab/admin` viewer から internal runtime を叩く時だけ
- rule:
  - external public API default にはしない
  - CSRF と same-site browser context を前提にする

### `hmac-request-signature`

- 用途:
  - webhook
  - replay / tamper 対策が必要な integration
- priority:
  - initial slice では optional

## SSO Candidate Notes

### Keycloak

- strong fit:
  - organization / realm / role 管理を含む大きめの IdP
  - OIDC / OAuth2 / SAML
  - LDAP / Active Directory integration
- cost:
  - 運用はやや重い
  - realm / client / role 設計を先に決める必要がある
- source:
  - https://www.keycloak.org/

### authentik

- strong fit:
  - self-hosted SSO を Docker compose で始めやすい
  - OAuth2 / SAML / LDAP / SCIM
  - admin UI と flow customization が豊富
- cost:
  - Keycloak より ecosystem 標準としての採用実績は薄い可能性がある
- source:
  - https://docs.goauthentik.io/

### Authelia

- strong fit:
  - reverse proxy forward-auth / homelab / self-hosted gateway 型
  - OIDC provider としても使える
- cost:
  - app-native OIDC 連携より upstream-auth-trust の設計に寄りやすい
- source:
  - https://www.authelia.com/integration/openid-connect/introduction/

### Ory

- strong fit:
  - headless / API-first identity platform
  - OAuth2 / OpenID Connect provider を自前 service として組み込みたい場合
- cost:
  - DegoDB 側の integration code と運用設計が増える
- source:
  - https://www.ory.com/docs/network/hydra

## Recommended Direction

Adopt `Keycloak first, OIDC generic`.

Keycloak は検証用 / sample 用の first IdP として採用する。ただし DegoDB 側に Keycloak 固有の認証実装は入れず、標準 OIDC の `issuer`, `audience`, `jwks_uri`, `required_claims` で表現する。これにより、将来 authentik / Authelia / Ory / managed IdP へ差し替えられる余地を残す。

first practical lane は次の順にする。

1. `static-bearer`
   - current `ProjectToken` を modern API style に寄せる。
   - generator / OpenAPI / Swagger helper / sample test の変更量が小さい。
2. `oidc-jwt-bearer`
   - SSO と API auth の本命。
   - first validation IdP は Keycloak。
   - implementation は generic OIDC JWT verifier として作る。
3. `upstream-auth-trust`
   - reverse proxy や API gateway で守る deployment 向けに後から足す。

admin/lab の SSO は `auth.mode=oidc` として stub auth と並べる。local dev は `stub` を維持し、production-like stack では OIDC を選ぶ。

## Proposed Metadata Shape

新規 canonical field は、legacy enum と分けて保存する。

```json
{
  "auth_policy_version": 2,
  "auth_policy": {
    "type": "oidc-jwt-bearer",
    "issuer": "https://idp.example.com/application/o/dego/",
    "audience": "dego-api",
    "jwks_uri": "https://idp.example.com/application/o/dego/jwks/",
    "required_claims": {
      "groups": ["dego-admin"]
    }
  }
}
```

`static-bearer` の例:

```json
{
  "auth_policy_version": 2,
  "auth_policy": {
    "type": "static-bearer",
    "secret_env": "DEGODB_PROXY_BEARER_TOKEN"
  }
}
```

## Legacy Mapping

| Legacy value | v2 interpretation | Migration stance |
| --- | --- | --- |
| `''` | `static-bearer` compatibility if legacy row | new row では invalid |
| `ProjectToken` | `static-bearer` compatibility | replace with `Authorization: Bearer` |
| `NoSecurity` | `none` | explicit opt-in only |
| `Manual` | custom handler responsibility | keep compatibility |
| `GetFunc` | legacy custom authorization | do not use as new default |
| `ProjectTokenOrGetFunc` | legacy hybrid | do not use as new default |
| `LoginCookieToken` | session bridge compatibility | restrict to internal viewer |

## Implementation Plan

### Phase 0. Inventory Freeze

- Create this report.
- Confirm current protected routes:
  - `lab_swagger`
  - `lab_published_single_proxy`
  - `project_source_output_download`
- Confirm generated `ProjectToken` fail-closed tests remain green.

### Phase 1. Policy Contract

- Add internal design doc section for `proxy_auth_v2`.
- Decide DB storage shape:
  - columns such as `auth_policy_version`, `auth_policy_json`
  - or a separate auth policy table
- Add validation rules:
  - missing secret fail-closed
  - unknown policy invalid
  - new blank auth invalid

### Phase 2. `static-bearer`

- Generate `Authorization: Bearer` verification in proxy runtime.
- Keep `ProjectToken` compatibility but mark it legacy in viewer.
- Emit OpenAPI `http` bearer security scheme.
- Add contract tests for:
  - missing header
  - malformed header
  - missing env
  - wrong token
  - matching token

### Phase 3. OIDC JWT Bearer

- Add JWKS-based JWT verifier boundary.
- Validate issuer, audience, exp, nbf, signature, and required claims.
- Add IdP config via env/deploy config.
- Add OpenAPI security scheme and viewer helper notes.
- Add local integration sample with Keycloak.
- Keep the generated runtime and validator generic OIDC, not Keycloak-specific.

### Phase 4. Admin/Lab OIDC Login

- Add `auth.mode=oidc`.
- Implement authorization code flow for human login.
- Store local session principal with:
  - `id`
  - `display_name`
  - `email`
  - `roles`
  - `groups`
  - `auth_source=oidc`
  - `site`
- Keep `stub` as local dev fallback.

### Phase 5. Authorization Layer

- Map IdP groups / claims to DegoDB roles.
- Add project membership evaluation.
- Move toward controller/service authorization rather than page-only policy.

## Acceptance Criteria

- New API auth default is not legacy body `TOKEN`.
- Missing configured secret / issuer / audience fails closed.
- Generated OpenAPI reflects selected auth policy.
- Swagger viewer can explain and exercise `static-bearer` and OIDC bearer endpoints.
- Stub auth still works for local dev.
- OIDC login can replace stub auth without reintroducing legacy login cookie semantics.
- Public raw OpenAPI / artifact route remains absent unless explicitly designed later.

## Open Decisions

- DB shape:
  - JSON policy columns on existing function/custom proxy tables
  - normalized `project_auth_policies` table
- JWT verification implementation:
  - PHP library dependency
  - minimal local verifier with cached JWKS
- Whether `static-bearer` secrets are per project, per source output, or deployment-wide in the first slice.
