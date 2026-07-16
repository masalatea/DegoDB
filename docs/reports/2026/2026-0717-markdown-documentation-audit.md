# Markdown Documentation Audit / Markdown 文書全体監査

## Scope / 対象

2026-07-17 時点の repository-wide Markdown を、次の順で確認した。

- root `README.md` と主要 directory 入口
- `docs/README.md`、`docs/current-plans.md`、恒久 support / proof 文書
- `sample/README.md`、`sample/tutorials/README.md`、`docs/sample-tutorial-roadmap.md`、`docs/study/README.md`
- `sample01` から `sample47` の directory と個別 README
- `docs/internal/` と `docs/reports/2026/` を含む全 Markdown 相対 file link
- 現行文書の Markdown heading anchor

監査完了時点の inventory は Markdown `1567` files、tutorial directory `47`、tutorial top-level README `47`。

## Updates / 更新内容

- root README の tutorial lane / latest sample を `sample47` まで更新。
- current plan の完了済み #952-#968 を履歴へ移し、#969 post-sample47 scope selection だけを `ACTIVE_NEXT` とした。
- sample root catalog、current workflow、roadmap、study guide、proof matrix を `sample47` まで更新。
- roadmap の runtime-pack-only 前提を修正し、promotion、external handoff、dependency-free Node.js/static-first sample の acceptance boundary を追加。
- Firebird の恒久文書を、active feasibility から「合意済み opt-in Mtool/profile + one-way migration path scope で完了、broad production support は非claim」へ統一。
- `docs/README.md` に未掲載だった `mtool-positioning.md` と `no-code-ui-testing.md` を追加。
- `mtool/README.md` と `deploy/README.md` を新規作成し、現行 implementation ownership と durable env template の入口を追加。
- internal doc の placeholder link と、移動済み current-plan history 2 files の旧相対 link 707件を、現在位置に合わせて修正。

## Verification / 検証

- 全 Markdown relative file link: `0 broken`
- reports を除く現行 Markdown heading anchor: `0 broken`
- tutorial catalog coverage: `47 / 47`
- tutorial top-level README coverage: `47 / 47`
- root `README.md` から Markdown link のみで到達できる tutorial README: `47 / 47`
- `git diff --check`: pass
- `sample35`〜`sample47` の README に対応する focused Node.js validator: pass
  - sample38 HTTP/SSE と Mtool artifact linkage を含む
  - sample40 HTTP route / SQLite store を含む
  - sample41 room sync を含む
  - sample42-44 / 47 Mtool artifact linkage を含む

Node.js は workspace bundled runtime、artifact-linkage 内の PHP 呼び出しは `/opt/homebrew/bin` を含む PATH で実行した。sample40 の `node:sqlite` validator は experimental warning を出したが成功した。

## Result / 結果

top page から current docs、sample catalog、全 tutorial README、履歴 link まで、現在の repository state を辿れる状態になった。次の active decision は `docs/current-plans.md` #969 を正本とする。
