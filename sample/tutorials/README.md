# Sample Tutorial Packs / tutorial sample 一覧

- 役割: user-facing の tutorial sample / demo sample を simple-to-complex に並べる category
- pack 名は `sample01-*` から始める
- README / Make help / 今後の導線はここを優先する
- tutorial backlog と段階設計の正本は `docs/sample-tutorial-roadmap.md` を参照する
- sample を教材として読む hands-on guide は `docs/study/README.md` を参照する
- `sample19-26` の ebook CMS lane をまとめて読む場合は `docs/study/ebook-cms-lane.md` を参照する

current tutorial packs:

- `sample01-simple-table-runtime`
- `sample02-dataclass-nullable-default-status`
- `sample03-dataclass-lookup-and-helper`
- `sample04-dataclass-parent-child-basic`
- `sample05-dbaccess-select-basic`
- `sample06-dbaccess-filter-sort-page`
- `sample07-dbaccess-crud-basic`
- `sample08-dbaccess-join-read-model`
- `sample09-dbaccess-aggregate-report`
- `sample10-dbaccess-mini-crud-flow`
- `sample11-html-template-output`
- `sample12-external-db-source-import`
- `sample13-openapi-api-surface`
- `sample14-custom-proxy-runtime`
- `sample15-project-metadata-export-import`
- `sample16-authenticated-proxy`
- `sample17-multi-output-project`
- `sample18-mini-task-board-demo`
- `sample19-json-first-content-model-demo`
- `sample20-content-publishing-demo`
- `sample21-ebook-catalog-api-demo`
- `sample22-ebook-chapter-workflow-demo`
- `sample23-ebook-media-metadata-demo`
- `sample24-ebook-public-reader-site-demo`
- `sample25-ebook-editor-auth-cms-demo`
- `sample26-ebook-headless-cms-capstone`
- `sample27-app-local-persistence-demo`
- `sample28-no-code-data-app-mvp`
- `sample29-no-code-support-case-demo`
- `sample30-no-code-app-local-sync-demo`
- `sample31-no-code-inventory-request-demo`
- `sample32-no-code-ui-test-lab`
- `sample33-sqlite-to-mysql-promotion`
- `sample34-sqlite-to-firebird-promotion`
- `sample35-capacitor-artifact-import`
- `sample36-shared-state-sync-server-input`
- `sample37-shared-state-sync-client-input`
- `sample38-shared-state-sync-node-runtime`
- `sample39-shared-state-chat-demo`
- `sample40-ephemeral-room-chat-site`
- `sample41-simple-whiteboard`
- `sample42-room-shooter-game`
- `sample43-tank-survival-game`
- `sample44-raycast-fps-line-demo`

`sample01`〜`sample32` は runtime pack であり、`compose.yaml` / `run.sh` / `seed/` を持つ。`sample33` は SQLite-to-MySQL promotion、`sample34` は SQLite-to-Firebird promotion の artifact-chain tutorial である。`sample34` は `reference/` fixture と PHPUnit contract に加え、opt-in Docker smoke で live Firebird import まで検証する。`sample35` は Capacitor-ready React app が Mtool artifact を直接 import できることを確認する app-wrapper tutorial であり、Mtool 想定操作を一通り網羅するが、`ios/` / `android/` / native build / signing は非scopeである。`sample36` は別 runtime の Node.js sync server owner が `sync-server-input.json` を読めることを、依存追加・server起動なしの静的fixtureで確認する。`sample37` は外部app client owner が `sync-client-input.json` を読めることを、SDK/source生成なしの静的fixtureで確認する。`sample38` は sample36/37 の packet と Mtool CLI 生成 packet を読む dependency-free Node.js reference runtime として、room membership、revision conflict、room-scoped event fanout、latest fetch、secret-free event を検証する。`sample39` は sample38 の上に乗るチャット風 domain sample として、message list append、revision conflict、room-scoped fanout、secret-free event を検証する。`sample40` は切り出しやすい URL room chat site sample として、image attachment metadata、ephemeral local image store、24h message expiry、7d inactive room expiry、room registry、same URL recreation、SQLite default store、production hardening checklist を検証する。`sample41` は static-first whiteboard sample として、pen / eraser / text / color / size / undo / clear / PNG export を検証する。`sample42` は room shooter game sample として、Mtool shared-state handoff packet 上の game contract、2-player join、move、shoot、hit/HP、room-scoped SSE update を検証する。`sample43` は tank survival game sample として、Mtool shared-state handoff packet 上の tank game contract、人数無制限 join、途中参加、全方向移動、障害物、前方弾、HP/explosion、last-alive winner、7日未活動reset を検証する。`sample44` は raycast FPS line demo として、Mtool shared-state handoff packet 上の FPS contract、line-only canvas raycasting、5-degree turn、wall collision、forward-angle shooting、HP/defeat、last-alive winner、7日未活動reset を検証する。

基本操作:

```bash
./sample/tutorials/sample01-simple-table-runtime/run.sh up
./sample/tutorials/sample01-simple-table-runtime/run.sh apply-seed
make sample01-pack-runtime-test
make sample11-pack-runtime-test
make sample12-pack-runtime-test
make sample13-pack-runtime-test
make sample14-pack-runtime-test
make sample15-pack-runtime-test
make sample16-pack-runtime-test
make sample17-pack-runtime-test
make sample18-pack-runtime-test
make sample19-pack-runtime-test
make sample20-pack-runtime-test
make sample21-pack-runtime-test
make sample22-pack-runtime-test
make sample23-pack-runtime-test
make sample24-pack-runtime-test
make sample25-pack-runtime-test
make sample26-pack-runtime-test
make sample27-pack-runtime-test
make sample28-pack-runtime-test
make sample29-pack-runtime-test
make sample30-pack-runtime-test
make sample31-pack-runtime-test
make sample28-no-code-runtime-ui-smoke
make sample29-no-code-runtime-ui-smoke
make sample31-no-code-runtime-ui-smoke
node sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-http-sse-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-mtool-artifact-linkage.mjs
node sample/tutorials/sample39-shared-state-chat-demo/scripts/validate-sample.mjs
node sample/tutorials/sample40-ephemeral-room-chat-site/scripts/validate-sample.mjs
node sample/tutorials/sample40-ephemeral-room-chat-site/scripts/validate-http-routes.mjs
node sample/tutorials/sample40-ephemeral-room-chat-site/scripts/validate-sqlite-store.mjs
node sample/tutorials/sample41-simple-whiteboard/scripts/validate-sample.mjs
node sample/tutorials/sample41-simple-whiteboard/scripts/validate-room-sync.mjs
node sample/tutorials/sample42-room-shooter-game/scripts/validate-sample.mjs
node sample/tutorials/sample42-room-shooter-game/scripts/validate-mtool-artifact-linkage.mjs
node sample/tutorials/sample43-tank-survival-game/scripts/validate-sample.mjs
node sample/tutorials/sample43-tank-survival-game/scripts/validate-mtool-artifact-linkage.mjs
node sample/tutorials/sample44-raycast-fps-line-demo/scripts/validate-sample.mjs
node sample/tutorials/sample44-raycast-fps-line-demo/scripts/validate-mtool-artifact-linkage.mjs
make sample18-http-runtime-smoke
make test
```

`sample18-http-runtime-smoke` は `web-lab` の `/samples/sample18-task-board` に login し、画面から task を作成・編集できることまで確認する。

`sample19` は JSON-first content model entrance として、MySQL / MariaDB と SQLite config store profile の両方を検証する。

`sample20` からは ebook / content publishing lane に入り、runtime profile は MySQL / MariaDB canonical のみに絞る。`sample21` は ebook catalog API、`sample22` は chapter workflow、`sample23` は EPUB / media delivery metadata、`sample24` は public reader site、`sample25` は legacy-compatible ProjectToken protected editor CMS API、`sample26` は headless CMS capstone に進める。`sample27` は shared contract から App-local SQLite schema / DBAccess helper へ接続する App-local persistence demo。`sample28` は data-first no-code app MVP として、shared contract / managed operation metadata から `NO-CODE-RUNTIME` artifact を生成し、generated list/detail/form と browser dispatch intent を `sample28-no-code-runtime-ui-smoke` で確認する。`sample29` は support case read-model fields を使う 2 つ目の data-first no-code sample として、polished generated runtime を `sample29-no-code-runtime-ui-smoke` で確認する。`sample30` は no-code action intent を managed operation sync outbox と App-local SQLite handler へ接続する sync-backed no-code demo。`sample31` は inventory request domain で database-first no-code runtime が ticket / support case 以外にも反復できることを確認する。`sample33` は SQLite-first から MySQL への offline promotion artifact chain、`sample34` は SQLite-first から Firebird local durable profile への promotion contract chain を、automatic cutover なしで確認する。`sample35` は external app owner 側の Capacitor-ready React sample として、Mtool artifact import、list/detail/form、local draft、required validation、action intent draft、mock submit boundary、ownership boundary を確認する。`sample36` は Node.js shared-state sync server input packet を static fixture として読み、backend authority、route/auth/state/event/fallback/validation、forbidden action 境界を確認する。`sample37` は shared-state sync app client input packet を static fixture として読み、room/state/realtime/fallback/reconnect と SDK/source/token-storage非scope境界を確認する。`sample38` は dependency-free Node.js reference runtime として、sample36/37 packet と Mtool CLI 生成 packet を実際に読み、membership、editor update、stale revision、room-scoped fanout、latest fetch、secret-free event、loopback-only HTTP/SSE fallback を検証する。`sample39` は sample38 の shared-state sync runtime を使うチャット風 domain sampleとして、message list append、image attachment metadata、ephemeral local image store、empty message rejection、non-member / viewer rejection、stale revision、same-room event、cross-room isolation、secret-free event を検証する。`sample40` は単独切り出し可能な ephemeral room chat site sample として、URL room open/recreate、image attachment metadata、ephemeral local image store、24h message expiry、7d inactive room cleanup、room registry 永続、SQLite default store、JSON fallback、loopback HTTP route validation、production hardening checklist を検証する。`sample41` は単独切り出し可能な static whiteboard sample として、touch/mouse/pen pointer drawing、pen color/size、eraser、text placement、undo/clear、PNG export、serializable operation model、room sync、SSE board update、production hardening checklist を検証する。`sample42` は単独切り出し可能な対戦式シューティング sample として、Mtool shared-state handoff packet 上の game contract、URL room、2-player join、move/shoot commands、hit/HP state、room-scoped SSE `game.updated`、dependency-free loopback server を検証する。`sample43` は単独切り出し可能な戦車 survival game sample として、Mtool shared-state handoff packet 上の tank game contract、人数無制限 join、途中参加、全方向移動、障害物、前方弾、HP/explosion、last-alive winner、7日未活動reset、dependency-free loopback server を検証する。`sample44` は単独切り出し可能な raycast FPS line demo として、Mtool shared-state handoff packet 上の FPS contract、line-only canvas raycasting、5-degree turn、grid wall collision、forward-angle shooting、HP/defeat、last-alive winner、7日未活動reset、dependency-free loopback server を検証する。current generated runtime security baseline は `sample16` の static bearer authenticated proxy で確認する。
