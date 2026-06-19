# Auth Architecture / 認証構成

English companion:
This document explains the current authentication architecture for the rewrite. It separates the local stub setup from future production-ready authentication, and it records the present session, principal, role, route-guard, and CSRF boundaries.

## ステータスラベル

この文書では、認証・SSO・権限まわりの進捗を次のラベルで管理する。

| Label | Meaning |
| --- | --- |
| `DONE` | 実装・テスト・ドキュメント反映まで完了している。 |
| `FIRST_SLICE` | 接続点や contract は実装済みだが、横展開や実IdP接続の検証を残す。 |
| `NEXT` | 次に小さく進める候補。 |
| `PARKED_EXTERNAL` | Mtool では作らず、外部IdPや周辺OSSへ任せる前提。 |
| `NOT_IN_MTOOL` | Mtool の責務から外す。必要な場合も別製品・別サービスとして扱う。 |
| `LEGACY_COMPAT` | 旧実装・既存データとの互換目的で残す。新しい設計の主経路にはしない。 |

## 現在のステータス

| Area | Status | Notes |
| --- | --- | --- |
| local stub auth | `DONE` | local Docker / sample verification 用に維持する。 |
| admin / lab session principal | `DONE` | stub と OIDC で同じ session principal shape を使う。 |
| generated proxy API auth v2 | `DONE` | `static-bearer`、OpenAPI bearer scheme、fail-closed contract を実装済み。 |
| OIDC login I/F | `DONE` | env / compose / route / callback / ID token verification の接続点を持つ。 |
| OIDC login HTTP smoke | `DONE` | mock IdP で authorization redirect、callback、session principal、project role claim を検証する。 |
| project permission capability model | `FIRST_SLICE` | `viewer` / `editor` / `publisher` / `admin` と source output enforcement を実装済み。 |
| external IdP project-role claims resolver | `DONE` | `dego:project:<PROJECT_KEY>:<role>` style の group claim を project role に変換する。 |
| SSO audit first slice | `DONE` | OIDC login と audited permission decision に actor source / capability / role source / result を残す。 |
| local project identity role table | `FIRST_SLICE` | primary membership ではなく local override / break-glass / test support として扱う。 |
| legacy `project_memberships` | `LEGACY_COMPAT` | compatibility fallback のみ。新しい SSO 挙動は追加しない。 |
| member / group lifecycle management | `PARKED_EXTERNAL` | Keycloak などの外部IdP / OSS 側で管理する。 |
| SCIM / invitation / MFA enrollment | `NOT_IN_MTOOL` | Mtool の feature としては作らない。 |

## 目的

- 新実装で認証をどう切り出すかを、旧実装との差分が見える形で残す。
- 現段階のローカル用スタブ認証と、将来の本番向け認証統合を混同しないようにする。

## 現在の方針

- いまは Docker ローカルで確実に起動確認できることを優先する。
- ローカル開発では `mtool/app/auth.php` に置いたスタブ認証を使える。
- SSO は Mtool 内で会員管理を作り込まず、外部 identity provider へ接続する I/F として扱う。
- 旧実装の共有ログイン基盤や `ProjectUser` をそのまま復元しない。

## 現在の構成

### session

- `mtool/app/session.php` が session を開始する。
- `admin` と `lab` は別の session 名を持てる。
- cookie は `HttpOnly` と `SameSite=Lax` を付ける。

### principal

- ログイン後の principal は `$_SESSION['app_principal']` に保持する。
- 現在は以下の最小情報を持つ。
  - `id`
  - `display_name`
  - `roles`
  - `auth_source`
  - `site`
- OIDC 連携時も session principal の形は stub と揃える。

### role

- 現在は session principal の `roles` をそのまま使う。
- site role の最小集合は以下。
  - `admin`
  - `config`
  - `lab`
- 旧実装の `ProjectUser` の多数の read/write flag はまだ再現しない。
- project role の最小集合は以下。
  - `viewer`
  - `editor`
  - `publisher`
  - `admin`

### credential source

- 認証情報は `.env` から注入する。
- `admin` と `lab` で別のユーザー名、パスワード、roles を持てる。
- `make env` は `.env.example` を元に local password をランダム生成する。
- repo 側の password fallback は最小限にし、起動時は `.env` を前提にする。
- OIDC mode では `.env` / compose から issuer、client、redirect URI、claim mapping を注入する。
- OIDC client secret は populated value を metadata に保存しない。

### route guard

- `mtool/app/router.php` が route 名を返す。
- `mtool/app/middleware.php` が route ごとの認証要否を判定する。
- 現時点では `/dashboard`、`/projects`、`/experiments` を保護ルートにしている。
- さらに page renderer 側で `admin` / `lab` のサイト境界を判定する。
- `projects`
  - `admin` または `config` role を要求
- `experiments`
  - `lab` または `admin` role を要求
- いまは一覧、追加、更新で同じ role 判定を使う
- read / write の分離はまだ行わない

### CSRF

- `mtool/app/csrf.php` が token を session に保持する。
- `POST /login`
- `POST /logout`

上記の POST は token 一致を前提にする。

### generated proxy API auth v2

- generated proxy の API auth は、admin / lab の session auth とは別の contract として扱う。
- 旧 `ProjectToken` / body `TOKEN` は compatibility lane として残すが、新規 default にはしない。
- v2 policy は first slice では既存 proxy/function metadata の横に `auth_policy_version` と `auth_policy_json` を置く。
- `auth_policy_json` には secret env 名や deploy secret reference だけを保存し、token / password / populated secret value は保存しない。
- 最初の v2 runtime 実装候補は `static-bearer` とし、`Authorization: Bearer <token>`、missing env fail-closed、unknown policy invalid を contract にする。
- OIDC / Keycloak などの IdP 接続は admin / lab の login SSO の話であり、generated proxy API auth v2 とは別 contract として扱う。

## SSO / Membership Boundary

Status: `DONE`

Mtool は SSO 接続先として OpenID Connect compatible identity provider を想定する。first target は Keycloak を置ける形にするが、実装は Keycloak 専用ではなく OIDC generic に寄せる。

### Mtool が持つ責務

Status: `DONE`

- `stub` / `oidc` の auth mode 切り替え。
- OIDC issuer discovery、authorization request、callback route、ID token verification。
- IdP claims から session principal への変換。
- IdP claims / groups / roles から site role と project role を評価する差し込み点。
- project capability enforcement。
- local override / break-glass 用の project identity role table。
- legacy membership table からの compatibility fallback。
- audit log へ残すための principal / auth source / permission decision shape。

### Mtool が作らない責務

Status: `PARKED_EXTERNAL`

- user / member / group の lifecycle 管理。
- invitation flow。
- password reset。
- MFA / WebAuthn enrollment。
- organization / tenant administration。
- SCIM provisioning。
- IdP 管理画面の代替 UI。

これらは Keycloak、Authentik、Zitadel、Dex、Authelia などの外部 OSS / IdP 側へ外出しする。Mtool 側で必要にするのは、claims を受け取り、DegoDB の最小 role contract に変換する接続点だけにする。

### Role Source Priority

Status: `DONE`

project permission は次の優先順位で評価する。

1. external IdP claims / groups / roles
2. local override / break-glass table
3. legacy `project_memberships` compatibility fallback

`project_identity_memberships` は「Mtool 内で会員管理を作る」ための primary membership table ではない。SSO first slice では、外部IdPで表現しきれない緊急 override、移行期間の補助、またはテスト用の identity-role source として扱う。

### Claim Contract Candidate

Status: `DONE`

IdP 側 group / role の推奨表現は次のようにする。

- site role: `dego:site:admin`
- site role: `dego:site:config`
- site role: `dego:site:lab`
- project role: `dego:project:<PROJECT_KEY>:viewer`
- project role: `dego:project:<PROJECT_KEY>:editor`
- project role: `dego:project:<PROJECT_KEY>:publisher`
- project role: `dego:project:<PROJECT_KEY>:admin`

実装では、固定 prefix に閉じすぎず、env / config で claim name と group prefix を差し替えられる余地を残す。現在の first slice では `APP_AUTH_OIDC_GROUPS_CLAIM` と `APP_AUTH_OIDC_PROJECT_ROLE_GROUP_PREFIX` を使う。

## 旧実装との関係

旧実装は `original-codes/docs/auth-and-authorization.md` に整理した通り、次の要素を分離して持っていた。

- 共有ログイン基盤との連携
- login token cookie
- メール認証状態
- `ProjectUser` ベースのモジュール権限
- ページ単位セキュリティ
- 内部管理者オーバーライド

新実装ではこれらを一気に再現せず、まず以下の境界を先に固定する。

- identity provider との境界
- session snapshot の保持方法
- route / page policy の差し込み位置
- role / permission の評価位置

## 今後の拡張ポイント

### Phase 1

- Status: `DONE`
- スタブ認証から外部 identity provider 連携へ置換できる I/F を持つ。
- `oidc` mode、callback route、claims to principal contract を維持する。

### Phase 2

- Status: `FIRST_SLICE`
- external IdP claims から project role を評価する first slice は完了。
- source output publish / download 以外の project 操作にも permission enforcement を広げる作業は次フェーズで扱う。

### Phase 3

- Status: `NEXT`
- 旧実装の `ProjectSecurityForEachPage` 相当を route policy として再設計する
- UI 単位ではなく controller / service 単位で認可を定義する

## 現段階でやらないこと

Status: `NOT_IN_MTOOL`

- 旧 cookie 名との互換吸収
- BASIC 認証フォールバック
- メール認証ワークフロー
- 自動ページ台帳登録
- Mtool 内の user / member / group 管理 UI
- invitation / password reset / MFA enrollment
- SCIM provisioning

これらは旧実装の事情を引きずりやすいため、新実装では必要性を再評価してから取り込む。
