# Repository Cleanup Pass Checklist / repository 全体整理 pass checklist

この文書は、DegoDB / Mtool repository を複数周回で整理するときの checklist である。

目的は、古いものを単純に消すことではない。必要なもの、履歴として残すもの、参照として残すもの、実験として隔離するもの、明確に不要なものを分類し、current plan と恒久 docs を小さく保つことである。

## 基本ルール

- 履歴は消さない。完了済み計画や判断経緯は日付付き履歴ファイルへ移す。
- 古いだけで削除しない。
- 削除前に active / historical / reference / experimental / obsolete に分類する。
- 代表的な validation evidence は残す。
- `docs/current-plans.md` は active / next / parked の正本として小さく保つ。
- 判断が曖昧なものは記録し、勝手に削除しない。
- code behavior を変える cleanup は、docs-only cleanup と分ける。

## 分類

| 分類 | 意味 | 扱い |
| --- | --- | --- |
| active | 現在の実装・計画・導線で使う | 残す。必要なら整える |
| historical | 完了済み作業や判断経緯 | 日付付き履歴に置く |
| reference | 旧実装・比較・移行確認用 | `mtool/reference/` や docs 履歴から辿れるように残す |
| experimental | spike / feasibility / work artifact | scope と owner を明記し、必要なら work / experimental に隔離 |
| obsolete | 明確に不要で、履歴価値も代表証跡価値もない | 削除候補。削除前に差分と理由を記録 |

## Pass 1: docs / navigation

見るもの:

- `README.md`
- `docs/README.md`
- `docs/current-plans.md`
- `docs/*.md`
- `docs/reports/2026/README.md`
- docs 内 link

確認すること:

- 古い status が current に残っていないか
- DONE が current に溜まりすぎていないか
- 同じ説明が複数箇所で矛盾していないか
- 日付付き履歴に移すべきものが current に残っていないか
- 日本語説明が足りない箇所がないか
- link が壊れていないか

## Pass 2: samples / artifacts

見るもの:

- `sample/tutorials/`
- sample README
- sample fixtures
- sample validation scripts
- generated reference artifacts

確認すること:

- sample 番号と README の説明が一致しているか
- sample が現在の support boundary と矛盾していないか
- fixture が古い schema_version のまま残っていないか
- validation command が README / docs と一致しているか
- orphaned generated files がないか

## Pass 3: mtool code / scripts

見るもの:

- `mtool/app/`
- `mtool/scripts/`
- `mtool/shared/`
- `mtool/admin/`
- `mtool/lab/`
- experimental scripts

確認すること:

- CLI 名と docs が一致しているか
- artifact emitter が同じ命名規則を使っているか
- helper が重複していないか
- spike code が active code と混ざっていないか
- target-dir / overwrite / forbidden action policy が docs と矛盾していないか

## Pass 4: tests / validation evidence

見るもの:

- `tests/`
- `Makefile`
- smoke scripts
- `docs/proof-matrix.md`
- sample validation docs

確認すること:

- docs に書かれた validation command が存在するか
- Make target が古い sample / artifact を指していないか
- test 名と対象 artifact が一致しているか
- proof matrix が現在の support claim と一致しているか
- broad cleanup 前に必要な focused test があるか

## Pass 5: final consistency / history archive

見るもの:

- `git status`
- `git diff --stat`
- `git diff --check`
- current plan
- history index
- branch state

確認すること:

- current plan が小さいか
- DONE が履歴に移っているか
- 日付付き履歴から判断経緯を辿れるか
- branch / commit が意味単位になっているか
- PR にするか、squash するか、docs-only としてまとめるか

## 最小 command

Docs-only pass:

```bash
git diff --check
```

Repository scan examples:

```bash
rg -n "TODO|FIXME|WAITING|ACTIVE|PARKED|obsolete|deprecated" docs mtool sample tests
rg -n "shared-state|external-output|task packet|validation" docs sample mtool tests
```

Git state:

```bash
git status --short --branch
git diff --stat
```

## 終了条件

全体整理 phase は、少なくとも次を満たすまで続ける。

- `docs/current-plans.md` が active / next / parked に絞られている
- 完了済みの詳細が日付付き履歴へ移っている
- docs / samples / scripts / tests の主要導線が矛盾していない
- 明確に不要なもの、残すべきもの、判断保留のものが分類されている
- 最終 checkpoint に validation と commit/PR 方針が残っている
