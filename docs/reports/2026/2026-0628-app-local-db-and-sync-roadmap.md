# 2026-06-28 App Local DB And Sync Roadmap

## Purpose

Mtool の今後のロードマップ候補として、App 内 DB と server DB の同期型ユースケースを整理する。

ここでいう App は browser app、desktop app、mobile app、local-first web app を含む。App 内 DB は SQLite / SQLite WASM / PGlite / IndexedDB backed store などを想定するが、最初の設計整理では個別実装技術よりも、Mtool の canonical design、Source Output、DataClass、DBAccess の責務分離を優先して考える。

これは 2026 年時点の新規アイデアではなく、元々の 10 年前の構想に含まれていた方向性の再整理である。基本思想は、server と App を別々の設計として扱うのではなく、同じ設計情報から複数環境向けの型、DB access、保存処理、同期境界を出すことにある。

近年の browser / local-first 技術により、当時の構想をより現実的な実装候補として再検討できるようになっている。したがって、この report は「新規機能案」というより、旧構想の中核を current Mtool の canonical metadata / multi-output model に接続し直すためのメモとして読む。

## Status

- roadmap draft status: `DONE`
- implementation status: `PENDING`
- feasibility catalog: [2026-0628 App Local DB Feasibility Studies](2026-0628-app-local-db-feasibility-studies.md)
- next active step: Mtool auth foundation first slice, tracked in `docs/current-plans.md`

Status reading:

- This report being done means the roadmap / detailed implementation draft has been written.
- It does not mean App-local DB / sync / no-code implementation has started.
- The next step is deliberately smaller and broadly useful beyond this roadmap: implement the Mtool auth foundation first slice before applying SSO or starting App-local / no-code implementation.

## Core Hypothesis

Mtool は backend DB だけでなく、App 内 DB を持つアプリケーションにも有効である。

特に有用なのは、単体の App 内 DB 生成ではなく、次のような同期型構成である。

- server 側に RDB がある。
- App 側にも一次情報または local copy を保存する。
- App 側の保存データは server の copy の場合もある。
- App 側だけに存在する内部情報の場合もある。
- server DB と App DB の同期、差分、競合、保存先の責務分離が課題になる。

このとき、server と App で完全に別設計を持つより、同じ master design から server 向け output と App 向け output を分けて生成できる方が開発しやすい。

この段階まで進むと、Mtool は単に DB class や runtime artifact を生成する道具ではなく、データ操作そのものを framework 化する道具になる。どのデータをどの型で読み、どこへ保存し、どの状態で同期し、どの操作が許されるかが design metadata と generated access layer によって管理される。

さらに先の構想としては、no-code app 生成に接続する。データ操作が管理されれば、その上に載る表示や入力 UI は安定しやすい。これは表示を先に作る思想ではなく、データ先行の思想である。先に data model、operation、storage role、sync boundary を固め、その結果として UI / screen / app behavior を組み立てる。

ただし、同期型構成は今後の開発上の目的地であって、既存の単体 DB 利用を置き換えるものではない。Mtool の元々の価値である、単体の SQLite、単体の MySQL / MariaDB / PostgreSQL などの RDB、既存 DB import から単独 output を生成して使えることは維持する。

単体 DB モードは、同期型構成の簡易版や劣化版ではなく、独立した基本ユースケースである。local-only App、server-only backend、prototype、small tool、single-user workflow では、同期を持たない構成の方が正しい場合がある。

## Compatibility Principle

この roadmap は additive である。

Non-regression principles:

- existing server-only RDB workflow を壊さない。
- existing SQLite / lightweight workflow を壊さない。
- single Source Output / single runtime の利用を複雑にしない。
- sync role metadata が未設定でも、従来どおり DataClass / DBAccess / documentation output が生成できる。
- App-local output や sync output は opt-in capability として扱う。
- full sync を使わない project に sync-specific columns / helpers / endpoints を強制しない。

同期型の最終形は、単体 DB / 単体 output の上に積み上がる。したがって first slice でも、単体 SQLite output、単体 RDB output、server-only output の contract gate を regression guard として残す必要がある。

## Current Mtool Reading

現在の Mtool は、設計情報を canonical metadata として持ち、そこから DataClass / DBAccess / OpenAPI / custom proxy / documentation など複数 Source Output を出す方向に進んでいる。

ただし現状の DB 操作は、基本的に source DB / runtime environment に結び付いている。つまり、同じ table / dataclass design から複数 runtime 向けの access layer を分けて持つというより、対象環境に固定された DBAccess を生成する読み方が強い。

一方、Source Output 自体は複数出力を前提にできる。Source 設定を追加すれば、同じ project design から複数 artifact を publish する方向は既に Mtool の思想と合っている。

## Desired Model

master となる設計情報は 1 つにする。

基本形:

1. import は 1 つの canonical design を作る。
2. import source は、多くの場合 server 側 DB schema とする。
3. 必要なら App 側 DB schema から import してもよい。
4. canonical design から server 向け output と App 向け output をそれぞれ生成する。
5. 同じ DataClass shape を server read / App local persist / sync payload の共通型として使う。

この場合、server と App は次のように分かれる。

| Layer | Server side | App side |
| --- | --- | --- |
| Canonical design | shared | shared |
| DataClass / DTO | shared shape | shared shape |
| DBAccess | server RDB access | App local DB access |
| Runtime environment | PHP / server runtime / API | TypeScript / browser worker / mobile runtime |
| Storage role | source of truth or sync peer | local copy / local first / internal state |
| Output | server DBAccess, API, OpenAPI | local DBAccess, migration, sync helper |

この model の上位には、no-code app generation がある。DataClass / DBAccess / sync role が安定すると、画面は raw table editing ではなく、定義済み operation と validation に従って構成できる。つまり no-code app は独立した別機能ではなく、managed data operation の上に立つ presentation layer として位置づける。

## Data Flow Sketch

server copy case:

1. server DBAccess reads rows from server RDB.
2. data is placed into the generated DataClass / DTO shape.
3. API or sync transport passes the same shape to App.
4. App-side DBAccess stores the DataClass / DTO into App local DB.
5. App reads from local DB for UI and offline work.
6. changes are queued or marked for sync.
7. sync process sends changed DataClass / DTO payloads back to server.

App internal information case:

1. canonical design may define tables that are App-only.
2. App-side DBAccess persists those tables locally.
3. server output may be omitted, generated as a sync endpoint only, or generated as no-op / unsupported.
4. documentation must clearly mark the table as `app_local_only`.

## Sync Classification

Each table or DataClass should eventually have a storage / sync classification.

Candidate values:

| Classification | Meaning |
| --- | --- |
| `server_source` | server DB is source of truth; App stores a copy. |
| `app_source` | App creates primary data; server receives sync or backup. |
| `bidirectional_sync` | both sides can update; conflict policy is required. |
| `app_local_only` | App-only internal data; no server persistence by default. |
| `server_only` | server-only data; App output may not be needed. |
| `cache` | App stores derived or disposable cache data. |
| `outbox` | App stores pending operations for later sync. |

This classification should be design metadata, not hardcoded inside generated access classes.

## Output Strategy

The main design direction is `one canonical design, multiple outputs`.

Possible outputs:

- server DataClass / DBAccess
- server API / OpenAPI / proxy runtime
- App DataClass / DTO
- App local DB schema / migration
- App local DBAccess
- sync payload codec
- sync queue / outbox helper
- AI context documentation that explains server/App storage roles

The first practical slice can generate all selected outputs for the project. Fine-grained Source Output selection, such as `server only` or `App only`, is useful but can remain future work.

Reason:

- Mtool already has multi-output direction.
- Generating extra artifacts is tolerable in early exploration.
- Output selection adds UI / metadata / test matrix complexity.
- The key roadmap question is whether the shared design model works.

Future output selection candidates:

- per Source Output target environment: `server`, `app`, `shared`, `documentation`
- per table / DataClass inclusion rule
- per DBAccess function inclusion rule
- per runtime language / adapter
- publish bundle profiles, such as `server-api-bundle` and `app-local-db-bundle`

## App DB Technology Candidates

This report does not choose a final technology. The likely exploration order is:

1. SQLite / SQLite WASM / OPFS oriented output
   - Most natural for local RDB semantics.
   - Good first candidate for browser / desktop local DB.
2. PGlite oriented output
   - Useful when Postgres-compatible behavior or extensions matter.
   - Good candidate after the SQLite shape proves useful.
3. IndexedDB backed adapter
   - Browser-native storage.
   - Better treated as storage backend / fallback than as the canonical relational design model.

The Mtool design should avoid making the canonical metadata depend on one App DB technology.

## Design Implications For Mtool

Potential metadata additions:

- table storage role / sync classification
- source of truth side
- local table physical name, if different from server physical name
- local-only columns
- server-only columns
- revision / version / updated_at / deleted_at / tombstone policy
- conflict policy
- sync key / natural key / server id mapping
- generated output target environment

Potential generator additions:

- App local schema generator
- App migration generator
- TypeScript DataClass / DTO generator
- TypeScript local DBAccess generator
- browser worker runtime wrapper
- sync outbox helper
- server sync endpoint / OpenAPI companion
- storage role documentation output

Potential test additions:

- same canonical design produces server and App outputs.
- generated DataClass shape is compatible across server and App outputs.
- App DBAccess can persist data read by server DBAccess.
- local copy tables preserve id / nullable / default / enum semantics.
- sync metadata is present for classified sync tables.

## Required Capabilities

この構想で必要になる機能は、単なる App 内 DB 対応ではなく、同じ設計情報を server / App / sync / documentation へ展開するための機能群として捉える。

### 1. Environment-Aware Source Output

Source Output に、出力先環境の意味を持たせる。

Candidate target environments:

| Target | Meaning |
| --- | --- |
| `server` | server runtime、server DBAccess、server API。 |
| `app` | App local DB、App runtime、browser / desktop / mobile side access。 |
| `shared` | DataClass / DTO / contract など両方で共有する型情報。 |
| `sync` | sync payload、pull / push endpoint、outbox helper。 |
| `documentation` | AI context、storage role、sync policy documentation。 |

初期段階では全 output を生成してもよい。重要なのは、将来 `server only` / `App only` / `shared` の出力選択に進めるよう、Source Output 側に target environment の概念を置けることである。

### 2. Shared DataClass Contract

DataClass を PHP class 生成物としてだけでなく、server / App / API / sync をつなぐ shared contract として扱う。

Design decision:

- shared contract は `dataclass` / `dataclassfields` に flag を足して表現しない。
- 最初から別 metadata として持つ。
- `dataclass` / `dataclassfields` は canonical type seed / existing DataClass design として維持する。
- contract / storage role / sync policy / operation intent は、それぞれ DataClass を参照する別データとして定義する。

Reason:

- shared contract は yes/no flag ではなく、server / App / sync / API / no-code UI を横断する多面的な情報である。
- DataClass 本体に flag を増やすと、単体 RDB / 単体 SQLite / plain DataClass output の責務を汚しやすい。
- 別 metadata にすることで、既存の DataClass output を壊さず、shared contract を opt-in capability として追加できる。

Required contract fields:

- logical field name
- physical column name
- generated field name
- type / normalized type
- nullable
- default
- enum / status values
- identity key
- sync key
- revision / version field
- updated timestamp field
- deleted / tombstone field

この contract が定まれば、server PHP DataClass、App TypeScript DTO、OpenAPI schema、sync payload schema を同じ設計から出せる。

### 3. Storage Role And Sync Role Metadata

table / DataClass ごとに、どちらが一次情報を持つか、どのように同期するかを metadata として持つ。

Minimum metadata:

- storage role: `server_source`, `app_source`, `bidirectional_sync`, `app_local_only`, `server_only`, `cache`, `outbox`
- source of truth side
- sync direction: pull only, push only, bidirectional
- id policy: server id, client temporary id, UUID, natural key
- delete policy: hard delete, soft delete, tombstone
- conflict policy: server wins, app wins, last write wins, manual merge, field-level merge

最初の実装では、すべての policy を実行する必要はない。まず documentation と generated contract に出し、対象 table がどの同期責務を持つかを読める状態にする。

### 4. App Local DB Schema Output

App 側 DB 用の schema / migration を出す。

First target candidate:

- SQLite oriented local schema
- browser / desktop どちらにも寄せられる SQL
- later SQLite WASM / OPFS runtime adapter

Generated candidates:

- local table DDL
- local migration file
- local index
- sync metadata columns
- outbox table
- schema version table
- seed file, where useful

server schema と App schema は同一でもよいが、App 側には `sync_status`, `dirty`, `last_synced_at`, `local_updated_at`, `tombstone` などの local metadata が追加される可能性がある。

### 5. App Local DBAccess Output

server DBAccess と別に、App 内 DB 向けの access layer を出す。

First TypeScript method candidates:

- `saveXxx(dto)`
- `getXxx(id)`
- `listXxx()`
- `deleteXxx(id)`
- `listDirtyXxx()`
- `markSyncedXxx(...)`
- `markConflictXxx(...)`

この layer の責務は、server から取得した DataClass / DTO を App local DB に保存し、App 内部で同じ shape として読み戻せるようにすることである。

### 6. Server Sync API Output

App から server へ同期する場合、server 側にも companion output が必要になる。

Candidate outputs:

- pull endpoint
- push endpoint
- bulk upsert endpoint
- tombstone sync endpoint
- conflict response schema
- OpenAPI sync schema

これは既存の OpenAPI / proxy output と接続できる可能性が高い。ただし first slice では server sync API を実装せず、`server read -> DTO -> App local save -> App local read` の確認を優先する。

### 7. Bundle / Profile Output

Source Output が増えるため、file 単位ではなく bundle / profile 単位で publish できると扱いやすい。

Candidate bundles:

- `server-api-bundle`
- `app-local-db-bundle`
- `sync-contract-bundle`
- `full-stack-sync-demo-bundle`
- `documentation-bundle`

これは first slice の必須ではないが、sample 化する場合は output 一覧が膨らむため、早めに設計候補として置いておく。

### 8. Cross-Output Verification

この構想の価値は、生成物が同じ型としてつながることである。したがって、検証機能が重要になる。

Required verification candidates:

- server DataClass と App DTO の field contract が一致する。
- server DBAccess の取得結果を App DBAccess に保存できる。
- App DBAccess から読んだ内容が同じ DTO shape で返る。
- local copy table が id / nullable / default / enum semantics を保つ。
- sync role metadata が generated documentation に出る。
- server-only / app-only の table が誤った output に混ざらない。
- future sync API payload が OpenAPI schema と shared contract に一致する。

### 9. Managed Data Operation Layer

no-code app へ進む前提として、データ操作を framework 化する layer が必要になる。

Candidate operation metadata:

- list / detail / create / update / delete
- search / filter / sort / pagination
- aggregate / report
- lookup / reference selection
- validation rule
- permission / allowed operation
- offline allowed operation
- sync required operation
- conflict-sensitive operation

この layer は DBAccess function と UI action の中間に位置する。DBAccess は実行手段、DataClass は型、operation metadata はユーザーまたは App が何をしてよいかを表す。

Authorization note:

- operation permission は SSO / OIDC / upstream auth と強く関連する。
- Mtool が重い identity / role management を自前実装しない。
- 既存の generated API auth policy v2 / `oidc-jwt-bearer` 方針を流用し、IdP 側の user / group / role / claim を operation policy に map する。
- Mtool 側は operation metadata に `required_roles`, `required_claims`, `required_scopes`, `permission_key` などを持つ。
- runtime は authenticated principal の roles / claims / scopes と operation policy を照合する。
- local dev / tutorial では stub principal または `no-security` / static bearer を使えるが、production-like no-code app は OIDC / upstream-auth-trust を本命にする。
- default SSO は core runtime に必須同梱しない。代わりに optional standard SSO profile / sample stack として同梱する。

This keeps Mtool responsible for operation policy metadata and generated enforcement points, while identity, login, MFA, group membership, account lifecycle, and organization-level role management stay outside Mtool.

SSO packaging decision:

- Adopt `OIDC generic` as the Mtool contract.
- Provide a default local/prototype SSO stack, likely Keycloak-first, for sample / verification / production-like evaluation.
- Do not make every local dev or single-user workflow start an IdP by default.
- Keep stub auth for local development and tutorial speed.
- Keep static bearer for simple machine-to-machine / small internal API use.
- Generated no-code / operation-permission examples should use the standard SSO profile so permission behavior is demonstrable without custom IdP work.
- The default SSO profile must be replaceable by authentik, Authelia, Ory, managed IdP, or upstream gateway as long as OIDC claims / roles / scopes can be mapped.

This means "default SSO included" should be read as `batteries-included optional profile`, not `mandatory bundled identity system`.

Identity responsibility split:

- Mtool should not put all authorization responsibility into SSO, because operation permission is an app/data-operation concern.
- Mtool should not become a full identity platform either.
- The stable boundary is:
  - authentication / account lifecycle / MFA / organization groups are external identity responsibilities.
  - operation policy / required role or claim / generated enforcement point are Mtool responsibilities.
  - principal normalization is the bridge between them.

Auth provider profiles:

| Profile | Purpose | Scope |
| --- | --- | --- |
| `stub` | local dev / tutorial | fixed local principal, not production. |
| `static-bearer` | simple API / machine-to-machine | token verification only, no human account management. |
| `simple-local` | small self-contained no-code prototype or personal install | minimal built-in users / roles if needed; no enterprise claims. |
| `oidc` | production-like / enterprise / SSO | recommended default for serious deployment. |
| `upstream-auth-trust` | reverse proxy / gateway protected deployment | trust normalized headers from controlled upstream. |

`simple-local` should be intentionally limited:

- admin-created users only, if implemented.
- small fixed role set or project role assignment only.
- no MFA, SCIM, LDAP, enterprise group lifecycle, password recovery product surface, or audit-grade identity claims.
- migration path to `oidc` must be clear.

This keeps small installs usable without requiring an IdP, while enterprise installs can replace the simple profile with OSS or managed SSO without changing operation metadata.

Auth composition decision:

- principal provider は基本的に択一選択にする。
- operation authorization は layered check として全通しにする。
- API / generated endpoint auth policy は endpoint / operation ごとに選択できる。

Principal provider selection:

```text
auth.mode = stub | simple-local | oidc | upstream-auth-trust
```

`stub` / `simple-local` / `oidc` / `upstream-auth-trust` を同時に並列で通す設計にはしない。複数 provider を同時に通すと、account linking、logout、same email handling、role precedence、audit source が重くなるため、first design では避ける。

Authorization checks after principal normalization:

```text
principal exists
-> project membership ok
-> operation required role ok
-> required claim / scope ok
-> storage role / environment allowed
-> optional future row-level rule
```

この check は基本的に全通しであり、どれか 1 つが通れば許可という OR chain にはしない。OR が必要な場合は、operation policy 側で明示的な allow rule として設計する。

Endpoint auth policy:

- generated endpoint / API ごとに `static-bearer`, `oidc-jwt-bearer`, `none`, `upstream-auth-trust` などを選べる。
- ただし 1 endpoint に複数方式を暗黙 OR で許すのは first design では避ける。
- 将来必要になった場合だけ、明示的な `auth_chain` / `accepted_auth_methods` として設計する。

Upstream trust note:

`upstream-auth-trust` は、gateway / reverse proxy が OIDC などの認証を完了し、Mtool には normalized header が届く構成で使う。この場合、Mtool から見る principal provider は `upstream-auth-trust` であり、Mtool 内部で `oidc` と `upstream-auth-trust` を同時実行するわけではない。

Auth selection model:

SSO provider selection is not a hardcoded product switch inside Mtool. Mtool chooses a principal provider profile, and OIDC provider details / claims mapping are configured around that profile.

Layered selection:

1. Deployment auth mode
   - selected by environment / deploy config.
   - examples: `stub`, `simple-local`, `oidc`, `upstream-auth-trust`.
2. Provider details
   - used when `auth.mode=oidc` or `upstream-auth-trust`.
   - examples: issuer, audience, client id, JWKS URI, trusted header names.
3. Principal mapping
   - maps external claims / headers to Mtool normalized principal.
   - examples: subject, email, display name, roles, groups, scopes.
4. Operation policy
   - stored in Mtool metadata.
   - examples: required roles, required claims, required scopes, permission key.

Example deployment shape:

```text
MTOOL_AUTH_MODE=oidc
MTOOL_OIDC_ISSUER=https://idp.example.com/realms/dego
MTOOL_OIDC_AUDIENCE=dego-app
MTOOL_OIDC_CLIENT_ID=dego-app
MTOOL_OIDC_CLIENT_SECRET_ENV=MTOOL_OIDC_CLIENT_SECRET
```

Example principal mapping:

```json
{
  "principal_mapping": {
    "subject": "sub",
    "email": "email",
    "display_name": "name",
    "roles": "realm_access.roles",
    "groups": "groups",
    "scopes": "scope"
  },
  "role_mapping": {
    "dego-admin": "admin",
    "dego-editor": "editor",
    "dego-viewer": "viewer"
  }
}
```

Minimum Mtool-owned scope:

- `stub` principal provider.
- limited `simple-local` principal provider.
- `static-bearer` API auth.
- normalized principal model.
- operation policy evaluator.
- OIDC provider config reader.
- claims / roles / scopes mapping.
- generated runtime enforcement point.
- generated documentation explaining auth mode, principal mapping, and operation requirements.

Explicit non-goals for Mtool-owned scope:

- MFA.
- SAML.
- LDAP.
- SCIM.
- full password reset product surface.
- organization lifecycle.
- enterprise group management.
- IdP admin API integration.

This makes the built-in path useful for local / small installs while preserving a direct migration path to OSS SSO or managed IdP for enterprise use.

### 10. No-Code App Candidate Layer

no-code app は、managed data operation layer の上に置く将来候補である。

Candidate generated pieces:

- screen definition
- form definition
- list / detail view definition
- action button definition
- validation message definition
- navigation / workflow definition
- offline behavior hint
- sync status display hint

この layer は immediate first slice ではない。ただし、storage role / operation / validation / permission が canonical metadata に入っていれば、将来 no-code app へ自然に進める。

## Proposed Metadata Shape

DataClass 昇格は、既存 table へ flag を追加するのではなく、最初から別 metadata として設計する。

Core relationship:

```text
dataclass
  -> dataclass_contracts
      -> dataclass_field_contracts
      -> dataclass_storage_roles
      -> operation_definitions
```

Candidate tables:

| Metadata | Role |
| --- | --- |
| `dataclass_contracts` | DataClass を shared contract として扱う単位。contract key、target use、version、status を持つ。 |
| `dataclass_field_contracts` | field ごとの contract 意味。sync key、revision field、tombstone field、validation hint、DTO exposure などを持つ。 |
| `dataclass_storage_roles` | DataClass / table の保存責務。server source、app local only、bidirectional sync、cache などを持つ。 |
| `operation_definitions` | list / detail / create / update / delete / lookup などの data operation intent。 |
| `operation_fields` | operation ごとの input / output / editable / readonly / required field。 |
| `operation_auth_policies` | operation ごとの required roles / claims / scopes / permission key。SSO / OIDC claims を参照し、identity management 自体は外部 IdP に委ねる。 |
| `source_output_targets` | server / app / shared / sync / documentation などの output target profile。 |

Initial minimal set:

1. `dataclass_contracts`
2. `dataclass_field_contracts`
3. `dataclass_storage_roles`

`operation_definitions` は no-code app への接続で必須になるが、first implementation slice では Phase 7 に置く。

Default behavior:

- contract row がない DataClass は従来どおり plain DataClass として扱う。
- storage role row がない DataClass は existing server/output behavior を維持する。
- App-local / sync / no-code output は contract metadata が存在する場合だけ対象にする。
- generated documentation は contract metadata がない場合も、その DataClass が non-contract / legacy-compatible であることを説明できる。

## Draft Plan

この計画は、full sync engine から始めない。最初の到達点は、同じ canonical design から server access と App local access が両方出て、同じ shared DataClass / DTO contract で受け渡せることを示すことである。

### Phase 0. Report And Scope

Status: draft by this report.

Scope:

- 10 年前の元構想として位置づける。
- current Mtool の canonical metadata / multi-output model との接続を整理する。
- immediate active plan ではなく roadmap candidate として保存する。

Exit criteria:

- report に required capabilities と first slice candidate が残っている。
- active 実装へ進む場合の最初の sample 候補が読める。

### Phase 1. Metadata Draft

Goal:

- storage role / sync role / output target environment を metadata として設計する。

Work candidates:

- table / DataClass の storage role を定義する。
- DataClass contract に sync key / revision / tombstone などの候補を追加する。
- Source Output に target environment を付ける案を作る。
- 既存 metadata bundle に入れる場合の export / preview / apply 境界を整理する。

Exit criteria:

- generated artifact を作らなくても、1 project の table が `server_source` / `app_local_only` などとして説明できる。
- AI context documentation に storage role を出せる見通しがある。

### Phase 2. Shared Contract Output

Goal:

- DataClass を shared contract として出力できることを確認する。

Work candidates:

- existing DataClass metadata から language-neutral contract manifest を生成する。
- PHP DataClass と TypeScript DTO が同じ field contract を満たすことを比較する。
- physical / logical / generated name の扱いを server / App で揃える。

Exit criteria:

- server DataClass output と App DTO output の field contract が一致する。
- nullable / default / enum / id の基本 semantics が manifest に残る。

### Phase 3. App Local Schema Output

Goal:

- server import 由来の canonical design から App local DB schema を出す。

Work candidates:

- SQLite-oriented local DDL を experimental output として生成する。
- App local metadata columns を追加する policy を決める。
- App-only table と server-copy table を両方扱う sample を作る。

Exit criteria:

- generated local schema を SQLite に適用できる。
- server-copy table と app-local-only table の区別が generated docs に出る。

### Phase 4. App Local DBAccess Output

Goal:

- generated App local DBAccess が shared DTO を保存・復元できる。

Work candidates:

- TypeScript local DBAccess を experimental output として生成する。
- `saveXxx`, `getXxx`, `listXxx` の first slice を実装する。
- Node or browser-worker-adjacent test harness で SQLite local DB に保存する。

Exit criteria:

- server fixture row shape を DTO にし、App local DBAccess で保存できる。
- App local DBAccess で読み戻した DTO が shared contract と一致する。

### Phase 5. Sync Skeleton

Goal:

- actual full sync ではなく、sync の骨格を生成物として読める状態にする。

Work candidates:

- dirty row extraction method。
- mark synced method。
- outbox table / helper。
- pull / push payload schema。
- sync policy documentation。

Exit criteria:

- `server read -> DTO -> App local save -> App local read` に加えて、dirty / synced 状態の最小 lifecycle が表現できる。
- conflict policy は実行されなくても、metadata と docs に残る。

### Phase 6. Server Sync API Companion

Goal:

- App local DB と server DB をつなぐ server-side companion output を検討する。

Work candidates:

- pull endpoint OpenAPI。
- push endpoint OpenAPI。
- server bulk upsert DBAccess。
- tombstone handling。
- conflict response schema。

Exit criteria:

- generated OpenAPI / server proxy output が shared contract と一致する。
- App output と server output が同じ sync payload manifest を参照する。

### Phase 7. Managed Data Operation Layer

Goal:

- DBAccess function を、App / API / no-code UI が共有できる operation metadata として扱う。

Work candidates:

- list / detail / create / update / delete operation を canonical metadata として整理する。
- operation ごとの allowed environment を持たせる。
- offline allowed / sync required / conflict-sensitive などの operation flag を検討する。
- operation permission は SSO / OIDC claims / roles / scopes へ map する前提で設計する。
- `operation_auth_policies` は Mtool の独自 user store ではなく external IdP principal を参照する policy metadata として扱う。
- generated documentation に operation catalog を出す。

Exit criteria:

- UI を生成しなくても、1 project の「利用者ができるデータ操作」が metadata と docs から読める。
- DBAccess method と operation intent の対応が確認できる。
- operation permission が required role / claim / scope として documentation に出る。

### Phase 8. No-Code App Direction

Goal:

- no-code app を、managed data operation の上に立つ presentation layer として検討する。

Work candidates:

- list screen / detail screen / edit form の screen definition draft。
- operation metadata から action button / form field を導けるか確認する。
- storage role / sync status を UI hint として出す。
- no-code app generation を immediate implementation ではなく long-term destination として記録する。

Exit criteria:

- data-first model から no-code screen definition へ進む道筋が説明できる。
- 表示先行ではなく、データ操作管理の結果として UI が安定するという思想が documentation に残る。

## Detailed Implementation Draft

この詳細計画は、current active plan ではなく roadmap candidate の実装ドラフトである。着手前に `docs/current-plans.md` へ昇格し、対象 sample / output key / test gate を確定する。

The roadmap spine is intentionally linear: feasibility studies first, then auth foundation, shared data contract, App-local persistence, managed operation, and finally no-code screen/runtime generation. If each stage is promoted only after its exit criteria are met, the path should naturally end at a data-first no-code app MVP rather than stopping at disconnected infrastructure pieces.

End-to-end spine:

| Stage | Outcome | Depends on |
| --- | --- | --- |
| 0. Auth foundation first slice | Mtool permission units, roles, principal, and policy evaluator are clear | none |
| 1. App-local feasibility studies | data-contract and App-local risks are reduced before core changes | auth foundation |
| 2. Shared DataClass contract | server / App / API / UI share one data contract | contract metadata |
| 3. App-local persistence | same DTO can be saved/read in App-local DB | shared contract |
| 4. Managed data operations | list/detail/create/update/delete are metadata, not ad hoc UI actions | shared contract + auth |
| 5. Sync skeleton | local dirty/synced/outbox lifecycle is visible | App-local persistence + operations |
| 6. Screen definitions | no-code list/detail/form definitions are generated from operations | managed operations |
| 7. No-code runtime MVP | generated definitions render and execute allowed operations | screen definitions + auth |
| 8. No-code sample | a small generated app demonstrates data-first behavior | runtime MVP |

Planning scale:

| Scope | Rough effort | Reading |
| --- | ---: | --- |
| Contract metadata and documentation only | 3-5 days | schema / bundle / docs first slice。 |
| Shared contract + App local first demo | 3-4 weeks | sample27 で `server read -> DTO -> app save -> app read` を確認する。 |
| Operation metadata + permission skeleton | 2-4 additional weeks | no-code app の手前まで。 |
| OIDC / SSO profile integration | 1-2 additional weeks | generated API auth の既存実装を流用しつつ、principal mapping を固定する。 |
| No-code screen definition entrance | 2-4 additional weeks | list / detail / form definition の first slice。 |
| No-code runtime MVP and sample | 4-8 additional weeks | generated definitions を実行可能な最小 app として動かす。 |

### Work Unit 0. Feasibility Study Group

Goal:

- Full App-local / no-code implementation に入る前に、独立した FS で data-contract / App-local persistence の主要リスクを減らす。

Prerequisite:

- Mtool auth foundation first slice is implemented as normal feature/foundation work, not merely as an FS item.

Reference:

- [2026-0628 App Local DB Feasibility Studies](2026-0628-app-local-db-feasibility-studies.md)

Recommended first sequence:

1. Shared Contract Manifest Spike.
2. App Local SQLite Schema Spike.
3. DTO Save/Read Spike, if schema spike passes.

Exit criteria:

- Contract FS shows existing DataClass metadata can emit a useful manifest.
- App-local schema FS shows SQLite-oriented local schema is technically viable.
- The roadmap can choose promoted implementation units without guessing.

Rough effort:

- 1-5 days depending on how many spikes are run before promotion.

### Work Unit 1. Contract Metadata Foundation

Goal:

- DataClass contract / field contract / storage role を、既存 `dataclass` / `dataclassfields` とは別 metadata として保存できるようにする。

Candidate files / areas:

- config DB schema migration.
- repository / helper for contract metadata.
- project metadata bundle export / preview / apply.
- AI context documentation output.
- sample seed for first candidate project.

Data model first slice:

| Metadata | Required fields first | Deferred fields |
| --- | --- | --- |
| `dataclass_contracts` | project, dataclass ref, contract key, status, version, target use | inheritance, profile variants, lifecycle workflow |
| `dataclass_field_contracts` | contract ref, dataclass field ref, role flags, exposed name, normalized type hint | validation DSL, transformation rule, field-level merge |
| `dataclass_storage_roles` | dataclass ref, role, source of truth, sync direction, offline read/write flags | conflict engine, retention policy, encrypted local storage |

Implementation notes:

- No `dataclass` flag migration for shared contract.
- Missing contract row means legacy-compatible plain DataClass behavior.
- Missing storage role row means existing server/output behavior.
- Keep generated outputs unchanged until an explicit experimental output is added.

Verification:

- schema preflight passes for MySQL / MariaDB and SQLite config store if config schema is touched.
- metadata bundle includes contract rows and can preview/apply them.
- documentation explains which DataClass is contract-enabled and which is legacy-compatible.

Exit criteria:

- A project can store contract metadata without changing existing DataClass PHP output.
- AI context output can describe contract / storage role metadata.
- Existing tutorial samples without contract metadata still pass unchanged.

Rough effort:

- 3-5 days.

### Work Unit 2. Shared Contract Manifest And DTO Output

Goal:

- Existing DataClass metadata plus contract metadata can produce a language-neutral shared contract manifest and a first App-facing DTO output.

Outputs:

- `DATACLASS-CONTRACT-JSON` or similar language-neutral manifest.
- `DATACLASS-TS` or similar TypeScript DTO output.
- optional documentation section in `AI-CONTEXT-MD`.

Manifest fields:

- contract key.
- DataClass logical name.
- physical name and generated name.
- fields with normalized type, nullable, default, key, enum/status hint.
- storage role summary.
- contract status and version.

Verification:

- PHP DataClass generated output and contract manifest agree on field names and basic types.
- TypeScript DTO output agrees with contract manifest.
- physical / logical / generated names are handled consistently.
- a contract compare tool can fail with readable field-level diffs.

Exit criteria:

- One sample project emits PHP DataClass, shared contract JSON, and TypeScript DTO from the same canonical metadata.
- Contract compare proves the shared field shape is aligned.

Rough effort:

- 4-7 days.

### Work Unit 3. App Local SQLite Schema Output

Goal:

- A contract-enabled DataClass can produce App-local SQLite-oriented schema output.

Outputs:

- `APP-LOCAL-SQLITE-SCHEMA` experimental Source Output.
- local table DDL.
- local metadata columns policy.
- optional schema version / outbox helper table draft.

First policy:

- server-copy table keeps the same core fields as shared contract.
- App-local metadata columns are added only when storage role requires them.
- App-only table can exist in the same project if explicitly marked.
- sync metadata is explicit in generated docs.

Suggested local metadata columns:

- `local_updated_at`
- `last_synced_at`
- `sync_status`
- `dirty`
- `tombstone`

Deferred:

- automatic conflict resolution.
- encrypted local store.
- browser OPFS runtime.
- PGlite.

Verification:

- generated SQLite schema applies cleanly to a SQLite DB.
- schema fields match shared contract.
- server-copy and app-local-only roles are visible in generated documentation.
- existing server-only samples remain unaffected.

Exit criteria:

- sample27 can create an App-local SQLite database schema from canonical metadata.

Rough effort:

- 4-7 days.

### Work Unit 4. App Local DBAccess First Slice

Goal:

- App-local access layer can persist and read the shared DTO shape.

Outputs:

- `APP-LOCAL-DBACCESS-TS` experimental Source Output.
- minimal TypeScript runtime helper.
- Node-based test harness first, browser worker later.

First methods:

- `saveXxx(dto)`
- `getXxx(id)`
- `listXxx()`
- `deleteXxx(id)` only if storage role permits.
- `listDirtyXxx()` and `markSyncedXxx(...)` can be included if sync metadata is present.

First demo flow:

```text
server fixture row
-> shared DTO
-> App local DBAccess save
-> App local DBAccess read
-> contract compare
```

Verification:

- Node test creates local SQLite DB, applies generated schema, saves DTO, reads DTO, compares manifest.
- server PHP DataClass / DBAccess output still passes existing contract tests.
- generated TypeScript can type-check in the sample harness if TypeScript toolchain is included.

Exit criteria:

- `server read -> DTO -> app save -> app read` works in sample27.

Rough effort:

- 1-2 weeks.

### Work Unit 5. Sample27 App Local DB Sync Demo

Goal:

- Provide a user-facing sample that proves the concept without full sync engine.

Candidate name:

- `sample27-app-local-db-sync`

Candidate domain:

- task board with local draft / pending operation.
- notes with server copy plus local edit state.
- ebook reading state where content is server-copy and reading progress is app-local.

Recommended domain:

- task board or notes for first implementation, because CRUD / local draft / sync status are easy to understand.

Sample shape:

| DataClass | Storage role | Purpose |
| --- | --- | --- |
| `Task` or `Note` | `server_source` | server row copied into App local DB. |
| `LocalDraft` or `PendingOperation` | `app_local_only` / `outbox` | App-side state not persisted as server source row. |
| optional `SyncState` | `cache` / helper | generated helper table if needed. |

Expected outputs:

- PHP DataClass / DBAccess.
- shared contract JSON.
- TypeScript DTO.
- App local SQLite schema.
- App local DBAccess.
- AI context documentation.

Completion gate:

- sample run applies seed.
- Mtool publishes all selected outputs.
- sample check compares generated references.
- runtime harness proves DTO local persistence.

Rough effort:

- 3-5 days after Work Units 1-4, or included across them.

### Work Unit 6. Operation Metadata And Permission Skeleton

Goal:

- Move from data shape to managed data operation.

Data model first slice:

- `operation_definitions`
- `operation_fields`
- `operation_auth_policies`

First operations:

- list.
- detail.
- create.
- update.
- delete.
- lookup.

Auth / permission model:

- principal provider remains deployment-level択一.
- operation authorization is layered and all required checks must pass.
- operation policy can require role / claim / scope / permission key.
- storage role can further reject environment-incompatible operation.

Verification:

- generated operation catalog documentation.
- DBAccess method and operation intent are linked.
- operation policy evaluator can evaluate a stub principal.
- negative tests fail closed for missing principal / missing role / wrong storage environment.

Exit criteria:

- A project can explain "what data operations are available" without generating no-code UI yet.

Rough effort:

- 1-2 weeks.

### Work Unit 7. Auth Provider And SSO Profile First Slice

Goal:

- Make operation permissions usable without building a full identity platform.

Mtool-owned minimum:

- normalized principal model.
- `stub` provider.
- limited `simple-local` provider, if selected for small installs.
- static bearer API auth.
- OIDC config reader.
- claims / roles / scopes mapping.
- operation policy evaluator.

SSO profile:

- optional Keycloak-first local profile for verification.
- generic OIDC contract.
- replaceable with authentik / Authelia / Ory / managed IdP / upstream gateway.

Verification:

- stub principal can pass/fail operation checks.
- static bearer can protect generated API endpoint.
- OIDC JWT principal can be normalized from claims in generated runtime.
- documentation shows auth mode, provider config, principal mapping, operation requirements.

Exit criteria:

- no-code / operation-permission sample can demonstrate permission behavior with stub and one production-like OIDC profile.

Rough effort:

- 1-2 weeks if generated API OIDC pieces are reused.
- longer if admin/lab human OIDC login is included in the same slice.

### Work Unit 8. No-Code Screen Definition Entrance

Goal:

- Show that managed data operation can drive stable screen definition.

First generated definitions:

- list view definition.
- detail view definition.
- edit form definition.
- action button definition.
- field visibility / readonly / required hints.
- sync status display hint.
- permission-based action availability.

Important boundary:

- Do not start with a full UI builder.
- Generate screen definition / documentation first.
- Runtime UI rendering can be separate after operation metadata stabilizes.

Verification:

- operation metadata maps to list / detail / form definitions.
- unavailable operations are hidden or disabled by policy.
- storage role affects offline / sync status hints.
- generated docs explain why each screen action exists.

Exit criteria:

- A reader can see how data-first operation metadata becomes no-code screen behavior.

Rough effort:

- 2-4 weeks if runtime rendering is deferred.

### Work Unit 9. No-Code Runtime MVP

Goal:

- Generated screen definitions can render and execute allowed operations against generated data access / API boundaries.

First runtime scope:

- list view renderer.
- detail view renderer.
- edit/create form renderer.
- action dispatcher bound to operation metadata.
- permission-aware disabled / hidden actions.
- validation message display from operation / field metadata.
- sync status display for App-local tables where available.

Important boundary:

- This is still not a full visual app builder.
- No drag-and-drop designer.
- No arbitrary workflow engine.
- No custom component marketplace.
- Runtime renders generated definitions from managed data operations.

Execution modes:

- server-backed mode: operations call generated server API / DBAccess boundary.
- app-local mode: operations call App-local DBAccess where storage role allows.
- hybrid mode: read from local copy, sync skeleton indicates pending changes.

Verification:

- generated list/detail/form definitions render in a minimal runtime.
- role/permission policy changes visible action availability.
- operation calls go through generated operation dispatcher, not hand-coded screen logic.
- storage role affects whether local write is allowed.

Exit criteria:

- A minimal generated app can view, create, edit, and persist one DataClass through generated operation metadata.

Rough effort:

- 3-5 weeks for a minimal runtime.

### Work Unit 10. No-Code App Sample

Goal:

- Provide a user-facing sample proving the roadmap reaches a data-first no-code app MVP.

Candidate name:

- `sample28-no-code-data-app-mvp`

Candidate domain:

- task board.
- notes / local drafts.
- small catalog with editor workflow.

Expected behavior:

- generated list screen.
- generated detail screen.
- generated create/edit form.
- role-based action availability.
- App-local or server-backed persistence depending on selected profile.
- generated documentation explaining data model, operations, permissions, storage role, and screen definitions.

Completion gate:

- sample run publishes contract, DTO, DBAccess, operation catalog, screen definitions, and no-code runtime bundle.
- browser or headless smoke confirms list/detail/form render and one create/update flow works.
- no-code sample remains additive; existing single DB / server-only samples remain unaffected.

Rough effort:

- 1-3 weeks after Work Unit 9.

### Suggested Milestones

Milestone 0. FS Results:

- Work Unit 0.
- Result: App-local / shared-contract risks are documented and selected items are promoted deliberately.

Milestone A. Contract Foundation:

- Work Unit 1.
- Result: contract metadata exists and does not disturb existing outputs.

Milestone B. Shared Contract Proof:

- Work Unit 2.
- Result: PHP DataClass / contract JSON / TypeScript DTO agree.

Milestone C. App Local Persistence Proof:

- Work Units 3-5.
- Result: sample27 proves `server read -> DTO -> app save -> app read`.

Milestone D. Managed Operation Proof:

- Work Unit 6.
- Result: operation catalog and permission skeleton exist.

Milestone E. Auth Profile Proof:

- Work Unit 7.
- Result: stub/simple-local/OIDC profile can feed normalized principal and operation checks.

Milestone F. No-Code Entrance:

- Work Unit 8.
- Result: operation metadata can generate screen definitions.

Milestone G. No-Code Runtime MVP:

- Work Unit 9.
- Result: generated screen definitions render and execute operations.

Milestone H. No-Code App Sample:

- Work Unit 10.
- Result: a sample demonstrates the data-first no-code app path end to end.

### Risk And Control Points

| Risk | Control |
| --- | --- |
| DataClass contract bloats existing DataClass tables | Keep contract metadata separate and opt-in. |
| SSO integration becomes identity platform work | Limit Mtool to principal normalization and operation policy evaluation. |
| App-local output breaks single DB workflows | Keep App-local / sync outputs opt-in and add regression gates for plain outputs. |
| Sync engine scope expands too early | First demo stops at local persistence and dirty/synced skeleton only. |
| TypeScript/browser runtime expands toolchain too early | Use Node harness first; browser worker later. |
| no-code UI becomes product-sized too soon | Generate screen definitions before UI renderer. |
| Permission semantics become ambiguous | Principal provider is択一; authorization checks are all-pass layered checks. |

## First Slice Candidate

Do not start with full sync engine.

Recommended first slice:

1. Add a dated design report. Done by this file.
2. Create a small sample project where one canonical design emits both server and App-local outputs.
3. Use server DB as import source.
4. Generate normal server DataClass / DBAccess output.
5. Generate App-local schema and App-local DBAccess as experimental output.
6. Demonstrate `server read -> DataClass -> App local save -> App local read`.
7. Document sync classification as metadata, even if actual bidirectional sync is not implemented yet.

Candidate sample:

- `sample27-app-local-db-sync`
- domain: small task board, notes, catalog, or ebook reading state.
- server table: task / note / content.
- App local table: same shape for copy plus local sync metadata.
- App-only table: reading state, draft, pending operation, or UI preference.

## Open Questions

- Should App-local output start as TypeScript-first, or should it first be generated as SQL / schema documentation only?
- Should the first runtime target be browser worker, Node script, or desktop local app?
- Should sync metadata be stored as normal columns, sidecar tables, or generated helper tables?
- Should App-only tables live in the same canonical project as server tables, or in a related App profile?
- How should Mtool show per-output inclusion without making early Source Output setup too heavy?
- How much of sync should Mtool generate, and how much should remain application policy?

## Current Recommendation

Keep this as a roadmap candidate, not an immediate active implementation lane.

The idea is strong enough to preserve because it extends Mtool's existing strengths:

- one canonical design
- multiple outputs
- generated DataClass / DBAccess
- DB-first and existing-DB-first import
- AI-readable generated documentation
- non-destructive support for single DB / single output workflows
- managed data operation as a path toward no-code app generation
- a staged path from feasibility studies to a no-code runtime MVP and user-facing no-code sample

The likely product phrase is:

> Use one DB design to generate both backend access and App-local persistence, with explicit sync roles.

Longer-term product phrase:

> Manage data operations first, then generate stable app behavior and no-code screens on top.

Roadmap target:

> Feasibility studies -> auth foundation -> shared contract -> App-local persistence -> managed operations -> generated screen definitions -> no-code runtime MVP -> no-code app sample.

This should be considered as a staged roadmap. The current active entry is only the feasibility-study group; each later stage should be promoted after the previous stage's exit criteria are met.
