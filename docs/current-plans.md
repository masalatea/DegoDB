# Current Plans / 現在の計画

English companion:
This page is the active plan index for DegoDB. It should stay short. Completed work lives in dated history files under `docs/reports/`.

このページは DegoDB の現在有効な計画索引です。短く保ちます。完了済み作業は `docs/reports/` 配下の日付付き履歴ファイルに置きます。

Use this page before searching historical files. / 履歴ファイルを探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

### Main Plan / 主計画

現在の主計画ステータス:

- Firebird、Mtool no-code 単体、mobile / external no-code wrapper handoff、shared-state sync packet scope は完了済み。
- RSS 後の bundle / manifest / validation checklist、external consumer handoff readiness、AI-assisted external app handoff checklist は完了済み。
- 全体整理 pass 1〜5 の初回周回と、`sample38`〜`sample47` の first slice は完了済み。
- AI-facing plugin interface と game domain plugin first slice は完了済み。
- game domain AI plugin validator first slice は完了済み。
- music / SFX の AI-facing plugin slice は完了済み。
- game audio validator first slice は完了済み。
- AI-facing plugin examples を使った簡単な static game sample は完了済み。
- 対戦式の大きな迷路 race sample は Node ありの room 対戦に更新済み。
- 高さ情報つき 45度視点 RPG map sample は別サンプルとして完了済み。
- vendored Three.js による 3D height-map view sample は完了済み。
- Node API から Mtool-style map packet を読む Three.js height-map runtime sample は完了済み。
- 次は、sample52 browser smoke / API contract check、height collision/pathfinding policy、shared-state sync AI plugin、または code-facing plugin interface を選ぶ。

Current main status:

- Firebird, standalone Mtool no-code, mobile/external no-code wrapper handoff, and shared-state sync packet scope are complete.
- The post-RSS bundle / manifest / validation checklist, external consumer handoff readiness, and AI-assisted external app handoff checklist are complete.
- The first cleanup sequence, passes 1 through 5, and the first slices of `sample38` through `sample47` are complete.
- The AI-facing plugin interface and game-domain plugin first slice are complete.
- The game-domain AI plugin validator first slice is complete.
- The music/SFX AI-facing plugin slice is complete.
- The game-audio validator first slice is complete.
- The simple static game sample using AI-facing plugin examples is complete.
- The competitive large scrolling maze race sample has been updated to Node-backed room multiplayer.
- The separate 45-degree height-map RPG view sample is complete.
- The vendored Three.js 3D height-map view sample is complete.
- The Three.js height-map runtime sample that reads a Mtool-style map packet from a Node API is complete.
- Next is choosing a sample52 browser smoke / API contract check, height collision/pathfinding policy, shared-state sync AI plugin, or separate code-facing plugin interface.

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status | Next decision / 次の判断 |
| --- | --- | --- | --- | --- |
| 969 | Post-sample47 next-scope selection / sample47 後の次scope選択 | Choose one coherent next work unit after the completed sample38-47 sequence | `DONE` | Selected AI-facing plugin interface first slice |
| 970 | AI-facing plugin interface and game domain package first slice / AI向け plugin interface・game domain package first slice | Define the AI-facing plugin root, manifest, task-packet authority, confirmation boundary, and `domain.game-content` package without code-facing hooks | `DONE` | Root, manifest, candidate schema, task template, minimal example, validator contract, and runtime handoff added |
| 971 | Game domain AI plugin validator first slice / game domain AI plugin validator first slice | Add a focused validator for the AI plugin manifest/task/candidate package and ID-reference checks | `DONE` | `validate_ai_plugin_packet.php` and integration coverage added |
| 972 | Game audio AI plugin package first slice / game audio AI plugin package first slice | Add a music/SFX AI-facing plugin package with cue metadata, trigger mapping, example, validator contract, and runtime handoff only | `DONE` | `domain.game-audio` package added; no audio generation, playback runtime, licensing decision, or engine project generation |
| 973 | Game audio AI plugin validator first slice / game audio AI plugin validator first slice | Add focused validation for audio plugin manifest/task/candidate, cue ID uniqueness, trigger cue references, and handoff non-goals | `DONE` | `validate_ai_game_audio_packet.php` and integration coverage added |
| 974 | AI plugin lantern game static sample / AI plugin lantern game static sample | Add a small static browser game that uses the game-content and game-audio plugin examples as source linkage | `DONE` | `sample48-ai-plugin-lantern-game` added with no runtime/package/audio asset generation |
| 975 | Competitive maze race Node room sample / 対戦式 maze race Node room sample | Add a four-corner-start scrolling maze race where Space hold drives forward, released state rotates the facing arrow at 90 degrees/sec, and same-room human players can race with AI fillers | `DONE` | `sample49-ai-plugin-maze-race` uses a local Node room server, SSE updates, and no production multiplayer claim |
| 976 | Height-map RPG 45-degree view sample / height-map RPG 45度視点 sample | Add a separate static RPG map sample with deterministic smooth random height values and 45-degree projected view | `DONE` | `sample50-height-map-rpg-view` added without changing sample47 runtime |
| 977 | Height-map Three.js 3D view sample / height-map Three.js 3D view sample | Add a separate vendored-Three.js terrain mesh sample with deterministic height values, vertex colors, lights, player marker, and 45-degree camera | `DONE` | `sample51-height-map-threejs-view` added with vendored `three.module.js` and no npm/CDN runtime dependency |
| 978 | API-fed height-map RPG runtime sample / API-fed height-map RPG runtime sample | Add a Node-backed sample where an internal Mtool-style map packet provider is exposed through `/api/map` and a Three.js runtime fetches that packet to render terrain | `DONE` | `sample52-api-height-map-rpg-runtime` added with no production collision/pathfinding/auth/deployment claim |
| 979 | Post-sample52 next-scope selection / sample52 後 next-scope選択 | Choose the next lane after the API-fed Three.js height-map runtime sample | `ACTIVE_NEXT` | Select sample52 browser smoke/API contract check, height collision/pathfinding policy, shared-state sync AI plugin, or code-facing plugin interface |

### Candidate Next Lanes / 次候補

These are candidates only. Promote one into Main Plan when the user chooses it. / ここは候補です。ユーザーが選んだものだけ Main Plan に昇格します。

| Candidate / 候補 | Why it exists / 理由 | Current boundary / 現在の境界 |
| --- | --- | --- |
| Code-facing plugin interface | AI-facing plugin work is promoted as #970. Code-facing generator/runtime hooks may be useful later. | Requires a separate compatibility and safety scope; do not mix with AI-facing plugins. |
| Shared-state sync AI plugin | Game content and game audio AI plugins now exist. Shared state sync could provide AI-facing room/state/event packet planning. | Keep it AI-facing only unless a separate runtime owner is selected. |
| Shared-state sync runtime integration | Promoted as #952. Server/client input packets are ready; the next slice is a Node.js reference sample, not production runtime generation. | Must not silently include deployed Node.js ownership, SDK generation, SSO setup, token storage, dependency install, public port, Redis/pubsub, or guaranteed replay. |
| External consumer concrete integration | Handoff readiness is documented. A concrete consumer could be selected next. | New scope only; avoid native project generation, dependency install, signing, or store submission by default. |
| New domain/sample lane | A different sample or domain can be promoted if it better matches the next product goal. | Requires a fresh scope decision. |

## Auxiliary Later Review / 補助・後日検討

These are useful candidates, but they are not part of the main plan unless a fresh priority decision promotes them. / これらは有用な候補ですが、新しい優先判断で昇格するまでは主計画には含めません。

| Item / 項目 | Status | Reopen condition / 再開条件 |
| --- | --- | --- |
| Mtool self no-code role boundary / Mtool 自身の no-code 役割分担 | `DONE_ROLE_BOUNDARY` | Mtool's responsibility is to emit supported no-code output, contracts, metadata, and handoff artifacts. React/app shell ownership is a role boundary, not unfinished Mtool work. |
| Custom operation execution routes / custom operation execution routes | `PARKED_REPLAN` | Reopen only after policy/auth/CSRF/audit/stale-artifact checks are explicit and testable. |
| Mtool admin/lab route authorization hardening / admin・lab route authorization 強化 | `PARKED_REPLAN` | Replan when a concrete deployment need or one route cluster is ready, with audit/test scope defined. |
| Mtool config store PostgreSQL support / Mtool config store PostgreSQL 対応 | `NOT_REQUIRED` | Not a remaining PostgreSQL task: Mtool config store stays MySQL/MariaDB default + SQLite lightweight; PostgreSQL completion is defined at the user DB/generated output/input layer. |
| SQL Server / Oracle current support / SQL Server・Oracle 現行対応 | `PARKED` | Reopen only with explicit enterprise need and support-scope decision. |
| Japanese invoice / billing / compliance sample / 日本向け請求・インボイス sample | `PARKED` | Reopen only after domain review is available. |

## History / 履歴

Completed detailed history was moved out of this active list. / 完了済みの詳細履歴は、この active list から移動しました。

Use these history indexes first; individual dated history files remain under `docs/reports/2026/`. / まず以下の履歴索引を参照します。個別の日付付き履歴ファイルは `docs/reports/2026/` 配下に残します。

| Completed scope / 完了済み範囲 | Historical source / 履歴ソース |
| --- | --- |
| Full current-plan state before older cleanup / 以前の整理前 current-plan 全体 | [2026-0712 Current Plan Pre-Cleanup Full History](reports/2026/2026-0712-current-plan-pre-cleanup-full-history.md) |
| Main Plan rows #459-#835 | [2026-0712 Current Plan History Archive](reports/2026/2026-0712-current-plan-history-archive.md) |
| Main Plan rows #836-#884 and Immediate Next Sequence N1-N6 | [2026-0713 Current Plan Done History](reports/2026/2026-0713-current-plan-done-history.md) |
| Main Plan rows #885-#900 and Immediate Next Sequence F1-F6 / I1-I2 / M1-M8 | [2026-0713 Firebird Mobile C1 Current Plan History](reports/2026/2026-0713-firebird-mobile-c1-current-plan-history.md) |
| Main Plan rows #901-#937 and Immediate Next Sequence through RSS-16 | [2026-0715 Current Plan History After RSS Merge](reports/2026/2026-0715-current-plan-history-after-rss-merge.md) |
| Main Plan rows #938-#950 and post-RSS docs/checklist cleanup | [2026-0715 Post-RSS Bundle, External Consumer, And Cleanup History](reports/2026/2026-0715-post-rss-bundle-external-cleanup-history.md) |
| Main Plan row #951 and PR #82 merge cleanup | [2026-0716 Post-RSS Docs Cleanup PR Merge](reports/2026/2026-0716-post-rss-docs-cleanup-pr-merge.md) |
| Main Plan rows #952-#968 and sample38-47 first slices | [2026-0717 Sample38-47 Current Plan History](reports/2026/2026-0717-sample38-47-current-plan-history.md) |
| Current mobile wrapper restart and feasibility-first correction | [2026-0714 Mobile Wrapper Active Restart](reports/2026/2026-0714-mobile-wrapper-active-restart.md), [2026-0714 Mobile FS Common Requirements](reports/2026/2026-0714-mobile-fs-common-requirements.md), [2026-0714 Mobile Output Mode Hardening](reports/2026/2026-0714-mobile-output-mode-hardening.md), [2026-0714 Mobile Artifact Execution UI Policy](reports/2026/2026-0714-mobile-artifact-execution-ui-policy.md) |
| Shared state sync roadmap candidate | [2026-0714 Shared State Sync Node Server Roadmap](reports/2026/2026-0714-shared-state-sync-node-server-roadmap.md) |
| Earlier no-code runtime, public delivery, runtime-data, and packaging work / 以前の no-code runtime・public delivery・runtime-data・packaging 作業 | See `docs/reports/2026/README.md` and the archived through-#459 plan history. |

## Status Meanings / 状態の意味

| Status | Meaning / 意味 |
| --- | --- |
| `ACTIVE_NEXT` | Recommended next decision/work / 次に進める判断または作業 |
| `DONE` | Completed and retained here only when it anchors current decisions / 完了済み。現在の判断の基準として必要な場合だけここに残す |
| `PARKED` | Intentionally deferred and not part of the quick plan list / 意図的に保留し、quick plan list には入れない |
| `PARKED_REPLAN` | Deferred until a fresh scope / value / risk decision is made / scope・価値・risk を再判断するまで保留 |

## Finding Rules / 探し方のルール

- Start here when asking "what plans remain?" / 「残っている計画は何か」を見る時はここから始める。
- Use date-less docs for current commitments. / 現在有効な約束は日付なし文書を見る。
- Use dated history files for history, decisions, and implementation records. / 履歴、判断経緯、実装記録は日付付き履歴ファイルを見る。
- Promote a history item into this page only when it becomes active or user-facing. / 履歴内の項目が active または user-facing になった時だけ、このページへ昇格する。
- Move completed items back to dated history files and keep this list short. / 完了項目は日付付き履歴ファイルへ戻し、この一覧は短く保つ。
