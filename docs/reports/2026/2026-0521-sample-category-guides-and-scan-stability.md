# 2026-05-21 Sample Category Guides And Scan Stability

## 概要

- `sample/` の category split 後に、`patterns` / `legacy-projects` / `old` それぞれの入口 README を追加した。
- `SamplePackCatalogTest` を強化し、category guide README、file-based sample fixture catalog、`Sample9-22` dedicated output test coverage を current layout の contract として固定した。
- sample output/reference compare 中に稀に起きていた `RecursiveDirectoryIterator` の scan failure に対し、tree scan helper を安定化した。

## 変更内容

- `sample/patterns/README.md`
  - `sample1` と `sample9-22` の役割差を category 単位で読めるようにした。
- `sample/legacy-projects/README.md`
  - `sample2-8` を representative project runtime pack として一覧化した。
- `sample/old/README.md`
  - archive root の意味と、active catalog へ戻す時の条件を書いた。
- `sample/README.md`
  - category README を入口として参照すること、category と structure type が別軸であることを追記した。
- `mtool/app/sample_pack_catalog.php`
  - `sample9-22` の curated fixture input を catalog 化した。
- `tests/Integration/SamplePackCatalogTest.php`
  - category guide README 存在確認
  - file-based sample fixture catalog の完全性確認
  - `Sample9-22` dedicated output test coverage の完全性確認
- `mtool/app/project_output_service.php`
  - `app_project_output_scan_tree()` で `clearstatcache(true)` を呼び、scan root を `realpath()` 正規化するようにした。

## 背景

- category split 後も `sample/README.md` 1 枚だけだと、`patterns` と `legacy-projects` の見分け、さらに `runtime pack` と `file-based migration sample` の見分けがまだ弱かった。
- あわせて `make test` 実行中、delete/recreate 直後の `work/sample-packs/.../output/` を scan するタイミングで、稀に `RecursiveDirectoryIterator` が `No such file or directory` を返す揺れが出た。
- これは sample gate の本質ではない flake なので、scan helper 側で吸収する方が妥当だった。

## 検証

- `make test`
  - `61 tests / 1502 assertions`
- `make mtool-self-loop-check`
  - pass

## 次段

- `sample/legacy-projects/` 側に project-shaped verification gate を追加するかは別タスクで判断する。
- current 時点では、category guide / catalog / fixture / output-test coverage の構造 guard まで入ったので、sample/test 整理の土台としては十分である。
