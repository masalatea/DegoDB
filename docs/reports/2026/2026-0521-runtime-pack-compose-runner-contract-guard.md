# 2026-05-21 Runtime Pack Compose Runner Contract Guard

## 概要

- `sample/` の active runtime pack について、`compose.yaml` / `run.sh` / shared runner の path contract を `SamplePackCatalogTest` で固定した。
- これにより、sample catalog の category / README / fixture だけでなく、runtime pack の起動導線そのものも static test で崩れにくくなった。
- current 対象は `sample1-simple-table` と `sample2-8` である。

## 変更内容

- `tests/Integration/SamplePackCatalogTest.php`
  - 各 runtime pack の `compose.yaml` が
    - `name: mtool-sample-<pack>`
    - `APP_WORK_ROOT: /var/www/work/sample-packs/<pack>`
    - `./sample/<category>/<pack>/seed:/docker-entrypoint-initdb-sample:ro`
    - core initdb copy / sample initdb copy
    を current catalog と一致させていることを検証する test を追加した。
  - 各 runtime pack の `run.sh` が
    - bash strict mode
    - default `up`
    - `sample/_pack-support/sample-pack-runner.sh` への共通委譲
    を維持していることを検証する test を追加した。
  - `sample/_pack-support/sample-pack-runner.sh` が
    - root `compose.yaml` + pack `compose.yaml`
    - `PACK_DIR/seed`
    - `apply_config_sample_seed.sh --compose-file=...`
    の導線を維持していることを検証する test を追加した。

## 背景

- sample/test 整理で category guide と pack catalog は既に固定されていたが、runtime pack の compose / runner path はまだ「ファイルが存在している」以上の保証が薄かった。
- category split や `_pack-support` rename のような整理を今後も続けるなら、起動導線も pack catalog と同じ水準で contract 化しておく方が安全だった。
- 特に `compose.yaml` 内の seed mount path や `APP_WORK_ROOT` は pack 名 drift が起きやすく、static test で早めに止める価値がある。

## 検証

- `docker compose -f compose.yaml -f sample/patterns/sample1-simple-table/compose.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SamplePackCatalogTest.php`
  - pass

## 補足

- この slice は static contract guard の追加であり、runtime pack の実際の起動フローや seed 内容自体は変更していない。
