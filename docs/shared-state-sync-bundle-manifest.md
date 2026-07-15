# Shared State Sync Bundle Manifest / 共有状態同期 bundle manifest

この文書は、共有状態同期（shared-state sync）で既に揃っている契約・入力 packet・sample・検証証跡を、利用者や AI が迷わず辿るための bundle / manifest 境界を定義する。

これは production Node.js server、client SDK、app source、native project を生成する仕様ではない。完了済み RSS 成果物をまとめる handoff / usability の正本である。

## 位置づけ

共有状態同期の現在の完了範囲は次の通り。

- room / membership / state / event の基本契約
- DB schema / REST API 契約
- WebSocket-first realtime 契約
- Node.js sync server 向け入力 packet
- app client 向け入力 packet
- server packet の sample / validation
- client packet の sample / validation
- Mtool CLI からの server/client packet 出力

この bundle manifest は、それらを「1つの成果物セット」として読むための索引である。

## Bundle の目的

Bundle の目的は3つ。

1. 利用者が次に読む文書を迷わないこと
2. AI / 外部実装者が server 側と client 側の責務を混同しないこと
3. どの validation を通せば packet set が ready と言えるかを明確にすること

## 推奨 bundle key

将来 Mtool が combined manifest を出す場合、推奨 key は次の形にする。

```json
{
  "shared_state_sync_bundle": {
    "schema_version": "shared_state_sync_bundle.v1",
    "contracts": {},
    "artifacts": {},
    "samples": {},
    "validation": {},
    "ownership_boundaries": {},
    "non_goals": []
  }
}
```

現時点では、この文書自体が docs-level manifest である。Mtool から combined JSON を出すかどうかは、validation checklist 作成後に判断する。

## Contract index / 契約索引

| 種別 | 文書 | 役割 |
| --- | --- | --- |
| 基本契約 | [Shared State Sync Contract](shared-state-sync-contract.md) | identity、room、membership、state、event、conflict、transport、non-goal の全体境界 |
| Schema / API | [Shared State Sync Schema/API Contract](shared-state-sync-schema-api-contract.md) | room、membership、invite、shared state、event の schema と REST API |
| Realtime | [Shared State Sync Realtime Contract](shared-state-sync-realtime-contract.md) | WebSocket event / command、heartbeat、reconnect、SSE / polling fallback |
| Server packet | [Shared State Sync Node Server Input Packet](shared-state-sync-node-server-input-packet.md) | 別 runtime の Node.js sync server owner に渡す packet |
| Client packet | [Shared State Sync App Client Input Packet](shared-state-sync-app-client-input-packet.md) | app client owner に渡す packet |

## Artifact index / artifact 索引

| Artifact | 出力 file | 作成者 | 利用者 | 備考 |
| --- | --- | --- | --- | --- |
| server input packet | `sync-server-input.json` / `SYNC-SERVER-INPUT.md` | Mtool | Node.js sync server owner / AI / 外部 generator | production server source ではない |
| client input packet | `sync-client-input.json` / `SYNC-CLIENT-INPUT.md` | Mtool | app client owner / AI / 外部 app framework | SDK / UI source ではない |

## Mtool CLI

Server input packet:

```bash
php mtool/scripts/create_shared_state_sync_server_input.php \
  --project-key=PROJECT \
  --backend-base-url-env=APP_BACKEND_BASE_URL \
  --target-dir=work/source-outputs/PROJECT/SHARED-STATE-SYNC-SERVER-INPUT
```

Client input packet:

```bash
php mtool/scripts/create_shared_state_sync_client_input.php \
  --project-key=PROJECT \
  --api-base-url-env=APP_BACKEND_BASE_URL \
  --target-dir=work/source-outputs/PROJECT/SHARED-STATE-SYNC-CLIENT-INPUT
```

両 command は controlled artifact directory 以外への出力を拒否し、既存 file の上書きを拒否する。

## Sample index / sample 索引

| Sample | 目的 | Validation |
| --- | --- | --- |
| `sample36-shared-state-sync-server-input` | 外部 Node.js sync server owner が `sync-server-input.json` を読めることを証明する | `node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs` |
| `sample37-shared-state-sync-client-input` | 外部 app client owner が `sync-client-input.json` を読めることを証明する | `node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs` |

## Focused validation

Server input:

```bash
docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncServerInputTest.php
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
```

Client input:

```bash
docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncClientInputTest.php
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
```

Docs / repository consistency:

```bash
git diff --check
```

## Ownership boundary

| 領域 | Mtool が担う | Mtool が暗黙に担わない |
| --- | --- | --- |
| 契約 | schema/API/realtime/server/client packet 契約 | app 固有の product policy 全決定 |
| Server | Node.js sync server input packet | production Node.js server 実装・運用・scale |
| Client | app client input packet | SDK生成、UI実装、token storage 選択 |
| Auth | SSO token を packet に入れない境界 | SSO provider 設定、session verification 実装 |
| Realtime | WebSocket/SSE/polling contract | Redis/pubsub、guaranteed replay、CRDT/OT、game loop |
| Validation | packet / sample / focused test の確認導線 | production 負荷試験、hosting、observability |

## Non-goals

この bundle は次をしない。

- production Node.js server source 生成
- Node.js dependency install
- server 起動
- public port open
- client SDK 生成
- React / Flutter / React Native source 生成
- SSO/OIDC provider setup
- token storage 選択
- native project 生成
- signing / store submission
- Redis/pubsub / queue / guaranteed replay 実装
- CRDT/OT / game-loop authority 実装

## 次の判断

この文書は 939 の bundle / manifest plan を満たす。

次は validation checklist を作る。Checklist 作成後に、docs-only で十分か、Mtool が `shared-state-sync-bundle.json` のような combined manifest を出すべきかを判断する。
