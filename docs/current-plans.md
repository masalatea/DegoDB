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
- RSS 後の bundle / manifest / validation checklist 整理は完了済み。
- 外部 consumer handoff readiness と AI-assisted external app handoff checklist は完了済み。
- 全体整理 pass 1〜5 の初回周回は完了済み。
- 次は、simple whiteboard sample の first slice を確認し、shared-state / room sync へ進めるかPR checkpointを選ぶ。

Current main status:

- Firebird, standalone Mtool no-code, mobile/external no-code wrapper handoff, and shared-state sync packet scope are complete.
- The post-RSS bundle / manifest / validation checklist organization is complete.
- External consumer handoff readiness and the AI-assisted external app handoff checklist are complete.
- The first multi-pass cleanup sequence, passes 1 through 5, is complete.
- Next is reviewing the first slice of a simple whiteboard sample and choosing whether to continue toward shared-state / room sync or checkpoint with a PR.

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status | Next decision / 次の判断 |
| --- | --- | --- | --- | --- |
| 952 | Shared-state sync runtime integration sample / shared-state sync runtime integration sample | Add a dependency-free Node.js reference sample that consumes sample36/37 packets and validates room membership, revision conflict, event fanout, latest fetch, and secret-free events | `FIRST_SLICE_DONE` | `sample38-shared-state-sync-node-runtime` added as an in-process runtime-shaped reference; no production server, dependency install, public port, real WebSocket server, SDK, or token storage |
| 953 | Shared-state sync HTTP/SSE fallback reference / shared-state sync HTTP・SSE fallback reference | Add a loopback-only Node.js standard-library HTTP/SSE adapter and validator for read/update/conflict/latest revision/SSE event behavior | `FIRST_SLICE_DONE` | `validate-http-sse-sample.mjs` starts a temporary `127.0.0.1` server and closes it; no production server, public port, dependency install, or real WebSocket server |
| 954 | Shared-state sync Mtool artifact linkage / shared-state sync Mtool artifact linkage | Prove sample38 can consume Mtool CLI-emitted `sync-server-input.json` and `sync-client-input.json`, not only checked-in fixtures | `FIRST_SLICE_DONE` | `validate-mtool-artifact-linkage.mjs` emits server/client packets to a temporary directory, consumes them in sample38, and removes the temp files; no dependency install or production server |
| 955 | Shared-state chat domain sample / shared-state chat domain sample | Add a chat-like domain sample on top of sample38 that validates message append, revision conflict, room-scoped fanout, cross-room isolation, and secret-free events | `FIRST_SLICE_DONE` | `sample39-shared-state-chat-demo` is a domain sample, not a production chat app, UI, persistence layer, moderation system, or real WebSocket server |
| 956 | Shared-state chat image attachment metadata / shared-state chat image attachment metadata | Add sample-only ephemeral image storage and sync only image attachment metadata through the chat shared state | `FIRST_SLICE_DONE` | Image bytes are stored under a temporary local directory and removed after validation; events contain metadata only, not raw image bytes |
| 957 | Ephemeral room chat site sample / 24時間で消えるroom chat site sample | Add a cut-out-friendly sample site where URL-named rooms are recreated on access, image attachments are stored outside message state, messages expire after 24h, inactive rooms expire after 7d, room registry can remain, API routes are validated over loopback HTTP, SQLite is the default local durable store, and production hardening boundaries are documented | `FIRST_SLICE_DONE` | `sample40-ephemeral-room-chat-site` is self-contained and dependency-free; current slice uses Node `node:sqlite` plus ephemeral image directory, with JSON fallback and documented non-production boundaries |
| 958 | Ephemeral chat / shared-state next slice selection / ephemeral chat・shared-state next slice selection | Decide whether the next slice should be richer image UI validation, real WebSocket transport, or checkpoint/PR | `ACTIVE_NEXT` | Choose the next scope after sample40 SQLite storage, HTTP route validation, and production-hardening checklist |
| 959 | Simple whiteboard sample / simple whiteboard sample | Add a static-first whiteboard sample with touch/mouse/pen drawing, color, size, eraser, text, undo, clear, PNG export, and serializable drawing operations | `FIRST_SLICE_DONE` | `sample41-simple-whiteboard` is local-only and dependency-free; no realtime sync, backend persistence, room membership, authentication, or production deployment |

### Candidate Next Lanes / 次候補

These are candidates only. Promote one into Main Plan when the user chooses it. / ここは候補です。ユーザーが選んだものだけ Main Plan に昇格します。

| Candidate / 候補 | Why it exists / 理由 | Current boundary / 現在の境界 |
| --- | --- | --- |
| AI-assisted artifact execution packets | Direct browser execution remains read-only, but Mtool can expose task packets that Codex / Claude read, explain, confirm, execute through CLI, validate, and report. | Route is documented; implementation would be a new scoped lane. |
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
