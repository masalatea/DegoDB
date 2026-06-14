# Root tmp Removal

## 背景

- root `tmp/` は base `compose.yaml` の `./tmp:/var/www/tmp` bind mount の副産物で、Docker 起動時に空 directory が自動作成されていた。
- 2026-05-18 時点で app / script 側に `/var/www/tmp` の実使用はなく、確認できた scratch / log の現行出力先は `work/tmp/` のみだった。

## 実施

- `compose.yaml` から `/var/www/tmp` bind mount を削除した。
- `Makefile`、`.gitignore`、`README.md`、`docs/internal/source-output-path-policy.md`、`docs/internal/runtime-architecture.md` を `work/` 基準に更新した。
- ad hoc な scratch、確認用 log、単発の作業中間物は root `tmp/` ではなく `work/tmp/` に置く方針へ統一した。

## 結果

- Docker 起動時に root `tmp/` が勝手に再作成されなくなった。
- disposable runtime file は `work/` 配下だけ見ればよくなった。
- `make clean` は `work/` を削除すれば一時生成物を一括掃除できる。
