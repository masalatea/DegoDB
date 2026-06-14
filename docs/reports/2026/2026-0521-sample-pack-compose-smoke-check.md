# 2026-05-21 Sample Pack Compose Smoke Check

## 概要

- active runtime sample pack (`sample1`, `sample2-8`) に対して、`docker compose config --services` ベースの軽い host-side smoke check を追加した。
- 目的は container を起動せずに、root compose と pack override の merge が current catalog path で解決できることを素早く確認すること。
- static contract test に加えて、「compose 自体が読める」ことを軽く見る導線ができた。

## 変更内容

- `mtool/scripts/check_sample_pack_compose_smoke.sh`
  - `app_sample_pack_runtime_pack_names()` / `app_sample_pack_relative_path()` を使って active runtime pack を列挙する。
  - `docker compose -f compose.yaml -f sample/<category>/<pack>/compose.yaml config --services` を実行し、`db-config` / `web-admin` / `web-lab` が解決できることを確認する。
  - `--pack=...` で単一 pack に絞れるようにした。
- `Makefile`
  - `make sample-pack-compose-smoke` を追加した。
- `tests/README.md`
  - host-side 補助 check として `make sample-pack-compose-smoke` を追記した。
- `sample/README.md`
  - sample catalog guard の近くから compose smoke check を辿れるようにした。

## 背景

- `SamplePackCatalogTest` で compose path / runner path の static contract は固定できているが、実際に `docker compose` がその override を解決できるかは別の層である。
- full `up` や heavier scenario を `sample2-8` 全件へ追加する前に、まずは compose merge の破綻を素早く止める軽い smoke がある方が実用的だった。
- この check は daemon 上で container を立ち上げないので、構成 drift の早期検知に向いている。

## 検証

- `bash -n mtool/scripts/check_sample_pack_compose_smoke.sh`
  - pass
- `make sample-pack-compose-smoke`
  - pass

## 補足

- これは host-side の compose smoke であり、runtime pack の seed 内容や app 動作までは見ない。
- heavier runtime smoke を増やす場合でも、この check は前段の fast guard として残せる。
