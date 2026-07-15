# Shared State Sync Validation Checklist / 共有状態同期 validation checklist

この文書は、shared-state sync の contract / packet / sample set が利用可能な状態かを確認するための checklist である。

対象は Mtool が所有する handoff 成果物であり、production Node.js server、client SDK、app source、SSO provider setup、native build の完成確認ではない。

## Ready の意味

この checklist でいう ready は、次の意味である。

- contract の読み順が明確である
- server owner が `sync-server-input.json` を読める
- client owner が `sync-client-input.json` を読める
- sample36 / sample37 が packet shape を静的に検証できる
- sample38 が packet を runtime-shaped reference として読んで、membership / revision / fanout / latest fetch / secret-free event を検証できる
- sample38 が Mtool CLI 生成 packet を読んで同じ runtime reference に接続できる
- Mtool CLI が server/client packet を出せる
- 禁止 action と ownership boundary が明確である
- AI / 外部実装者が、何を実装してよいか、何を実装してはいけないかを判断できる

Ready は、production runtime が完成したという意味ではない。

## 1. Contract checklist

| Check | 必須 | 確認対象 |
| --- | --- | --- |
| 基本契約がある | yes | [Shared State Sync Contract](shared-state-sync-contract.md) |
| schema/API 契約がある | yes | [Shared State Sync Schema/API Contract](shared-state-sync-schema-api-contract.md) |
| realtime 契約がある | yes | [Shared State Sync Realtime Contract](shared-state-sync-realtime-contract.md) |
| server packet 契約がある | yes | [Shared State Sync Node Server Input Packet](shared-state-sync-node-server-input-packet.md) |
| client packet 契約がある | yes | [Shared State Sync App Client Input Packet](shared-state-sync-app-client-input-packet.md) |
| bundle manifest がある | yes | [Shared State Sync Bundle Manifest](shared-state-sync-bundle-manifest.md) |

確認すること:

- SSO token を共有しないことが明記されている
- app/backend が identity / membership / persistence / conflict authority を持つことが明記されている
- Node.js sync server は separate runtime owner であることが明記されている
- app client は token storage / UI / SDK ownership を持つことが明記されている

## 2. Server packet checklist

| Check | Command / file |
| --- | --- |
| sample fixture が存在する | `sample/tutorials/sample36-shared-state-sync-server-input/reference/sync-server-input.sample.json` |
| static validation が通る | `node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs` |
| Mtool packet builder test が通る | `docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncServerInputTest.php` |
| CLI docs がある | [Shared State Sync Node Server Input Packet](shared-state-sync-node-server-input-packet.md) |

Server packet で確認すること:

- `schema_version` は `shared_state_sync_server_input.v1`
- `bundle_manifest_key` は `shared_state_sync_server_input`
- contracts に基本契約 / schema API / realtime / server input contract が含まれる
- backend integration は app/backend authority を前提にする
- WebSocket / SSE / polling profile が区別されている
- raw SSO token を event / packet に入れない
- Node.js dependency install / server start / public port open をしない

## 3. Client packet checklist

| Check | Command / file |
| --- | --- |
| sample fixture が存在する | `sample/tutorials/sample37-shared-state-sync-client-input/reference/sync-client-input.sample.json` |
| static validation が通る | `node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs` |
| Mtool packet builder test が通る | `docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncClientInputTest.php` |
| CLI docs がある | [Shared State Sync App Client Input Packet](shared-state-sync-app-client-input-packet.md) |

Client packet で確認すること:

- `schema_version` は `shared_state_sync_client_input.v1`
- `bundle_manifest_key` は `shared_state_sync_client_input`
- contracts に基本契約 / schema API / realtime / node server input / client input contract が含まれる
- app client owner が token storage と UI 実装を所有する
- SDK / source generation は false
- WebSocket / SSE / polling fallback の責務が明示されている
- SSO setup / dependency install / app source generation をしない

## 4. Runtime reference sample checklist

| Check | Command / file |
| --- | --- |
| reference runtime sample が存在する | `sample/tutorials/sample38-shared-state-sync-node-runtime/` |
| runtime-shaped validation が通る | `node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs` |
| Mtool artifact linkage validation が通る | `node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-mtool-artifact-linkage.mjs` |
| dependency-free である | sample root に `package.json` / `node_modules` を置かない |
| production server ではない | README が public port / real WebSocket server / deployment 非scopeを明記する |

Sample38 で確認すること:

- sample36/server packet と sample37/client packet を読む
- member は subscribe できる
- non-member は subscribe できない
- viewer は update できない
- editor は update できる
- stale revision は reject される
- accepted update は同じ room の subscriber にだけ fanout される
- reconnect/latest-fetch で最新 revision を取得できる
- event / audit event に SSO token、refresh token、raw invite token、secret を含めない
- loopback-only HTTP/SSE fallback reference が read / update / conflict / latest revision / SSE event を検証できる
- Mtool CLI が一時ディレクトリに出した server/client packet を読める

この sample は in-process event bus で WebSocket 相当の境界を検証し、Node.js 標準 `http` による loopback-only HTTP/SSE fallback を検証する。real WebSocket server、dependency install、public port、production deploy は別scopeである。

## 5. Handoff checklist for AI / external owner

AI または外部実装者に渡すときは、次を確認する。

| 質問 | 期待する答え |
| --- | --- |
| server と client のどちらを実装するのか | server packet / client packet のどちらを読むか明確 |
| app/backend authority はどこか | app/backend が session / membership / persistence / conflict を持つ |
| token を packet に含めるか | 含めない |
| generated source を作るか | この scope では作らない |
| dependency install をしてよいか | この packet scope ではしない。別途明示承認が必要 |
| validation は何か | sample validator + focused PHPUnit + `git diff --check` |
| 成功とは何か | packet が読めて、境界・禁止 action・validation が説明可能 |

## 6. Forbidden implicit actions

この checklist の範囲では、次を暗黙に行わない。

- production Node.js server source 生成
- dependency install
- server 起動
- public port open
- DB migration 実行
- SSO/OIDC provider setup
- token storage 選択
- client SDK 生成
- React / Flutter / React Native source 生成
- native project 生成
- signing
- store submission
- Redis/pubsub / queue / guaranteed replay 実装
- CRDT/OT / game-loop authority 実装
- user source overwrite

## 7. 最小 validation command set

Docs-only 変更の場合:

```bash
git diff --check
```

Server packet を変更した場合:

```bash
docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncServerInputTest.php
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
git diff --check
```

Client packet を変更した場合:

```bash
docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncClientInputTest.php
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-http-sse-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-mtool-artifact-linkage.mjs
git diff --check
```

Server / client 両方を変更した場合:

```bash
docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncServerInputTest.php
docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncClientInputTest.php
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-http-sse-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-mtool-artifact-linkage.mjs
git diff --check
```

Runtime reference sample を変更した場合:

```bash
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
git diff --check
```

## 8. Pass / fail

Pass:

- 必須 contract が存在する
- server/client packet の責務境界が説明できる
- sample36 / sample37 がそれぞれ validation できる
- sample38 が runtime-shaped reference として validation できる
- sample38 が Mtool CLI 生成 packet を読んで validation できる
- Mtool emission test が対象範囲で通る
- forbidden implicit actions が明記されている

Fail:

- server と client の責務が混ざる
- SSO token / secret を packet に入れる
- production runtime 完成と誤読される
- validation command が不明
- native / SDK / dependency install を暗黙に始める

## 次の判断

この checklist により、docs-level bundle / manifest と validation checklist は揃った。

次は、Mtool が `shared-state-sync-bundle.json` のような combined artifact を出すべきか、それとも docs-level manifest + existing server/client packet で十分かを判断する。
