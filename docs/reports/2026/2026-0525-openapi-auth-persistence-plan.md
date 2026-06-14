# 2026-05-25 OpenAPI / Auth / Persistence Plan Revisit

## Status

- status updated at: `2026-05-27`
- completed in current scope:
  - `[DONE]` Phase 0. Immediate hardening
  - `[DONE]` Phase 1. OpenAPI exposure control
  - `[DONE]` Phase 3. metadata export / import
  - `[DONE]` Phase 4. canonical config DB externalization
- accepted as current:
  - `[DONE]` generated proxy auth model review
    - fail-closed hardening を入れた current legacy-compatible model を当面維持する
    - `proxy auth v2` は current blocker にしない
- closed by current policy:
  - `[DONE]` OpenAPI public alias key / raw delivery route review
    - current slice では実装しない
    - delivery 時の注意は permanent docs に明記する
- remaining:
  - `Phase 5. optional YAML / share UX`

## 結論

- 2026-05-22 plan の主線だった `Lab DB schema -> Admin import -> sync -> proxy/openapi output -> Lab Swagger Try It Out` は、2026-05-25 の browser smoke まで到達済みである。
- 次の主線は新機能追加ではなく hardening であり、論点は 4 つに分けるのがよい。
  - OpenAPI artifact の露出ポリシー
  - generated proxy auth の再設計
  - canonical metadata の export / import
  - canonical config DB の外部化
- ただし 2026-05-27 判断として、generated proxy auth の full redesign は current slice では行わず、fail-closed hardening 済みの current model を当面維持する。
- OpenAPI の canonical generated format は当面 `JSON` のままにし、`YAML` は必要になった時だけ derived mirror として足すのがよい。

## 現状

### OpenAPI output

- `ProjectSourceOutput(ClassType=OpenAPI, artifact_strategy=openapi-json, program_language=json)` は current mainline に入っている。
- generator は minimal OpenAPI 3.0.3 document を生成する。
- emitted file は現在この 3 つで固定である。
  - `openapi.json`
  - `build-plan.json`
  - `README.md`
- `program_language` は現状 `json` のみ対応で、他の値は error にしている。
- `MTOOL` core seed には `OPENAPI-JSON` definition があり、`create_project_output --publish` または UI build/publish で生成できる。
- ただし `import` や `sync` のたびに常時自動 publish しているわけではない。現状は definition があるだけで、generate/publish 自体は explicit action である。

### OpenAPI の露出面

- published raw output は `work/source-outputs/{project_key}/{source_output_key}/openapi.json` に出る。
- artifact history は `work/artifacts/source-outputs/{project_key}/{artifact_key}/...` に出る。
- local compose では `work/` は `/var/www/work` に mount されるが、Apache docroot は `mtool/admin/public` または `mtool/lab/public` であり、`work/` は static web root ではない。
- `lab` の Swagger viewer は browser が raw path を直接読むのではなく、server-side に filesystem から `openapi.json` を解決して表示する。
- `/runs/swagger/{project_key}` は `lab` または `admin` role を要求する viewer page であり、anonymous static file route ではない。
- `/settings/database-sources` も `admin` site と `admin/config` role を要求する。

### いま言えること

- 固定 filename 自体は、現在の構成では主因のセキュリティホールではない。
- 主因になりうるのは、将来 `work/` を docroot 配下へ誤って露出すること、または raw file を auth なし route で配ることの方である。
- したがって、防御の主軸は filename randomization ではなく、`storage boundary` と `access control` に置くべきである。

### generated proxy auth の現状

- current single-function proxy auth は legacy 互換をかなり引きずっており、allowed value は次である。
  - blank
  - `ProjectToken`
  - `GetFunc`
  - `ProjectTokenOrGetFunc`
  - `NoSecurity`
  - `Manual`
  - `LoginCookieToken`
- blank auth は current でも legacy default として `ProjectToken` に解決している。
- imported canonical-bootstrap function は current sync 時に `NoSecurity` default が入る。
- Lab Swagger viewer の auth helper は `TOKEN` と `LOGIN_COOKIE_TOKEN` の補助だけを持つ。

### current auth の弱点

- generated proxy の `ProjectToken` 認証は、`MTOOL_PROXY_PROJECT_TOKEN` が空の時に fail-open している。
- 現状コードでは `expected token が空` または `payload の TOKEN が一致` のどちらかで通しているため、env が空のままでも non-empty `TOKEN` が通る。
- これは fixed `openapi.json` filename より優先度の高い hardening 項目である。

### canonical metadata persistence の現状

- canonical metadata store は `db-config` / `config_app` である。
- local compose では `db-config-data` volume がそれを永続化する。
- `db-lab` / `db-lab-data` は editable import source / runtime experiment 用であり、canonical store ではない。
- external named source は config DB の `database_sources` table に persist される。
- ただし `database_sources` は import source / proxy runtime read candidate の catalog であり、canonical metadata store そのものの置き換えではない。
- project-scoped canonical metadata 全体を export/import する general bundle flow はまだ無い。

## 判断

### 1. OpenAPI の形式

- current canonical output は `JSON` のままでよい。
- 理由は次の通り。
  - 現在の generator / viewer / test が `JSON` を前提に揃っている
  - machine-generated artifact としては `JSON` で十分である
  - Swagger / OpenAPI tooling は `JSON` も `YAML` も両方読める
- `YAML` が欲しい場合は、将来 `openapi.yaml` を derived secondary file として足す。
- `JSON` と `YAML` の両方を canonical source of truth にするのは避ける。

### 2. 固定 filename と random suffix

- internal workspace filename は固定のまま維持するのがよい。
- 推奨は次の固定名維持である。
  - `openapi.json`
  - `build-plan.json`
  - `README.md`
- 理由は次の通り。
  - generator、viewer、test、tooling の参照先が単純になる
  - mis-update や stale alias の事故が減る
  - random suffix は本質的には security by obscurity であり、主要防御にはならない

### 3. random suffix を使うならどこか

- もし semi-private な共有 URL を作りたいなら、filesystem filename ではなく `public alias key` に使うのがよい。
- つまり、`openapi.json` は内部では固定のまま持ち、外向け route だけ `stable random key` を要求する。
- 具体案:
  - project ごとに一度だけ生成する `public_spec_key` を config DB に保存する
  - 公開 route は `/runs/swagger/{project_key}?spec_key=...` または `/artifacts/openapi/{project_key}/{public_spec_key}` のようにする
  - key は rotate 可能にする
- これなら内部 tooling は壊さず、外向け URL だけ revoke できる。

### 4. OpenAPI は常に ON か

- `source output definition が存在すること` と `public に見せること` は分けるべきである。
- 推奨 default は次である。
  - `OPENAPI-JSON` definition はあってよい
  - generate/publish は explicit action のまま
  - public/raw exposure は default `OFF`
  - authenticated `lab/admin` viewer は default `ON` でよい
- つまり `always generate` ではなく、`internal capability is available` と読むのがよい。
- もし将来 auto publish を足すなら、`auto_publish_openapi_on_sync=0/1` のような per-source-output opt-in flag にする。

## 認証の見直し方針

### 1. まず分けるべきもの

- `lab/admin` site の session auth
- generated proxy endpoint の API auth

この 2 つは役割が違うため、同じ枠で考えない方がよい。

### 2. current auth の読み方

- current generated proxy auth は `legacy compatibility lane` とみなすのが自然である。
- 特に次は新規 default としては弱い。
  - blank -> `ProjectToken` fallback
  - `GetFunc`
  - `ProjectTokenOrGetFunc`
  - `LoginCookieToken`

### 3. 新 auth model の案

- 新規 canonical policy は `proxy_auth_v2` として別に持つのがよい。
- first class policy の候補:
  - `none`
  - `static-bearer`
  - `hmac-request-signature`
  - `upstream-auth-trust`
  - `session-bridge`
- optional later:
  - `jwt-oidc`

### 4. policy ごとの意図

- `none`
  - truly internal / lab-only / explicitly public test lane
- `static-bearer`
  - もっとも単純な machine-to-machine auth
  - current `ProjectToken` の正しい置き換え先
- `hmac-request-signature`
  - shared secret を持ちながら replay / tamper に少し強くしたい時
- `upstream-auth-trust`
  - reverse proxy / API gateway / ingress が auth 済み header を保証する時
- `session-bridge`
  - `lab/admin` viewer から internal runtime を叩く時だけの bridge
  - external public API default にはしない

### 5. auth の新 rules

- missing secret は必ず fail-closed にする
- blank auth は新規 row では invalid にする
- `NoSecurity` は explicit opt-in のみ許可し、UI / viewer で強く見せる
- per-source-output default auth はあってよいが、per-function override は残す
- legacy auth field は read-only compatibility lane としてしばらく読む

## persistence の見直し方針

### 1. export/import と external DB は両方必要

- `external config DB` は live durability の問題を解く
- `export/import bundle` は backup / migration / review / disaster recovery の問題を解く
- したがって、どちらか片方ではなく両方やるべきである

### 2. project-scoped export/import bundle

- first slice は CLI から始めるのがよい。
- 例:
  - `export_project_metadata.php`
  - `import_project_metadata.php`
- bundle は project 単位でよい。
- shape の例:
  - `manifest.json`
  - `project.json`
  - `tables.json`
  - `data-classes.json`
  - `db-access.json`
  - `source-outputs.json`
  - optional `database-sources.json`

### 3. bundle の rules

- default export では password / secret は含めない
- secrets は placeholder または separate secrets map で扱う
- import は `preview` と `apply` を分ける
- `replace` と `merge` を最初から両方やるより、first slice は `preview + replace-project-scope` でよい
- checksum と schema version を `manifest.json` に入れる

### 4. canonical config DB の外部化

- これは `database_sources` table に寄せて解くのではなく、まず deploy/bootstrap 設定で解くべきである。
- 理由:
  - app は canonical DB に接続してからでないと `database_sources` table 自体を読めない
  - したがって canonical store の bootstrap source of truth は env / deploy config に残す必要がある
- first slice の方針:
  - local dev では今の `db-config` container を維持
  - 本番相当や共有環境では `APP_DB_*` / `APP_CONFIG_DB_*` を外部 MariaDB へ向ける
  - preflight / migration check CLI を用意する
- export/import bundle は external DB を使う場合でも残す

## 次の実装順

### [DONE] Phase 0. Immediate hardening

- generated proxy token auth の fail-open をやめる
- `NoSecurity` endpoint の視認性を上げる
- OpenAPI raw artifact は internal workspace material だと docs に明文化する
- spec viewer / database source settings / download route の auth guard を一度棚卸しする

### [DONE] Phase 1. OpenAPI exposure control

- `spec_visibility` のような metadata を追加する
- default は `internal-only`
- authenticated `lab/admin` viewer は維持する
- 必要になった時だけ `public_spec_key` 付き route を追加する

### Phase 2. proxy auth v2

- v2 policy field を追加する
- current legacy auth からの mapping を定義する
- generator と viewer helper を v2 へ対応させる
- fail-closed contract test を入れる

### [DONE] Phase 3. metadata export / import

- CLI export/import を追加する
- `preview -> apply` の 2 段にする
- project migration / backup / restore の smoke を追加する

### [DONE] Phase 4. canonical config DB externalization

- external MariaDB を config DB に使う documented flow を作る
- migration / preflight check を足す
- local compose default は壊さない

### Phase 5. optional YAML / share UX

- 需要があれば `openapi.yaml` を derived file として追加する
- 外部 reviewer 向けに signed or tokenized route を足す
- ただし filename randomization を主防御にはしない

## 受け入れ条件

- OpenAPI raw artifact は生成されても public static asset にはならない
- public or semi-public spec exposure には explicit opt-in が要る
- generated proxy auth は missing secret で fail-closed する
- `NoSecurity` endpoint は UI / viewer 上で明示される
- canonical metadata を project 単位で export / import できる
- external config DB を deployment mode として選べる

## 補足

- ユーザー案の `固定 prefix + stable random suffix` は、public alias としてなら成立する。
- ただしそれを main security control にしてはいけない。
- internal filename は固定、external share key は random という分離が一番扱いやすい。
