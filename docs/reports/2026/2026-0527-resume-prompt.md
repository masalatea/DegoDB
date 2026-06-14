# 2026-05-28 Resume Prompt

2026-05-28 にそのままコピペして再開するための prompt。  
停止点は 2026-05-27 の終了時点です。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。2026-05-27 の停止点、2026-05-28 の開始点として扱ってください。

前提:
- まず `docs/reports/2026/2026-0526-resume-prompt.md` を前日までの stable context として読む
- そのうえで、今日の差分として次を反映する

2026-05-27 の追加更新:
- broad rewrite current wave の active execution plan は `docs/reports/2026/2026-0527-broad-rewrite-temporary-closure-plan.md` の 1 本だけに固定した
- active milestone は `[ACTIVE] M1. runtime contract truth normalization`
- `[PENDING] M2. DBACCESS wrapper/base migration`
- `[PENDING] M3. canonical generated data-* wrapper/base migration`
- `[PENDING] Close. verification / docs / status freeze`
- `M1` から `M3` まで完了した時点を中間完了、`Close` 完了を current wave の status freeze とする
- `Close` の docs rule として、一般/permanent docs (`README.md`, `docs/*.md`) は日英併記へ寄せる。`docs/reports/` 配下の progress / handoff / slice report は日本語のみでよい
- supporting/reference plan の status は次へ正規化済み
  - `docs/reports/2026/2026-0518-mtool-runtime-wrapper-base-migration-plan.md`: `PENDING`
  - `docs/reports/2026/2026-0514-functional-migration-vs-self-host-plan.md`: `PENDING`
  - `docs/reports/2026/2026-0511-gradual-legacy-absorption-plan.md`: `PENDING`
  - `docs/reports/2026/2026-0511-self-host-import-loop-plan.md`: `DONE`
- `docs/reports/2026/README.md` も上記 status に更新済み
- 2026-05-27 の最後の変更は docs/status 整形だけで、再テストは回していない
- latest verification baseline は前日と同じ
  - `DocsEntranceContractTest`: `OK (6 tests, 118 assertions)`
  - full suite: `OK (124 tests, 4482 assertions)`

明日の最優先:
1. `M1. runtime contract truth normalization` に着手する
2. `RUNTIME-DBCLASSES` の actual generated tree と、docs / generator 実装の説明ズレを洗う
3. current repo における `wrapper/base contract` の source of truth を 1 つに固定する
4. `M1` の範囲で必要な focused verification を判断する
5. まだ `M2` / `M3` へは入らない

明日最初に読む文書:
- `docs/reports/2026/2026-0527-broad-rewrite-temporary-closure-plan.md`
- `docs/reports/2026/2026-0518-mtool-runtime-wrapper-base-migration-plan.md`
- `docs/reports/2026/2026-0514-functional-migration-vs-self-host-plan.md`
- `docs/reports/2026/2026-0511-gradual-legacy-absorption-plan.md`
- `docs/reports/2026/2026-0526-progress-snapshot.md`
- `docs/reports/2026/2026-0526-resume-prompt.md`

明日最初に確認するコマンド:
- `php mtool/scripts/show_runtime_reference_status.php --require-current`
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
- `rg -n "RUNTIME-DBCLASSES|_base/|_wrappers/|base/|wrapper/base" docs mtool/shared mtool/extensions/MTOOL -g '*.md' -g '*.php'`
- `find mtool/extensions/MTOOL/RUNTIME-DBCLASSES -maxdepth 3 | sort | sed -n '1,160p'`
- `find work -path '*RUNTIME-DBCLASSES*' | sed -n '1,160p'`

M1 の完了条件:
- `RUNTIME-DBCLASSES` の actual emitted layout を docs で正しく説明できる
- docs / implementation / emitted layout の食い違いが解消される
- 次に `M2` へ入ってよい file contract が明文化される

継続前提:
- `original-codes/` は host-side reference only。runtime input に戻さない
- OpenAPI public alias key / raw route は current slice では実装しない
- generated proxy auth の full redesign は current blocker にしない
- general/permanent docs は最終的に日英併記へ寄せる
```
