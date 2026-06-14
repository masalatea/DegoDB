# 2026-05-21 Sample Pack Runtime Smoke Check

## 概要

- representative runtime sample pack として `sample2-sql-server` と `sample3-school-booking` を対象に、`up -> apply-seed -> /health -> seed-loaded check` までを見る heavier smoke を追加した。
- `sample2` で resource-free minimal pack、`sample3` で language resource / proxy metadata を含む pack を押さえる。
- `--pack=...` と `--all` により、必要時だけ他の runtime pack へ広げられるようにした。

## 変更内容

- `mtool/scripts/check_sample_pack_runtime_smoke.sh`
  - 既定では `sample2-sql-server` と `sample3-school-booking` を対象にする。
  - pack 番号から専用 host port を導出し、既存 container と衝突しにくい形で `docker compose up -d` を行う。
  - `web-admin` / `web-lab` の `/health` を container 内 `curl` で待ち、`site=admin|lab` と `database.ok=true` を確認する。
  - `apply_config_sample_seed.sh --compose-file=...` でその pack の seed を適用する。
  - `db-config` 上で `projects.slug` と `project_source_outputs` row count を確認する。
  - resource を持つ pack では、`web-admin` container 内から `app_project_language_resource_load_file_catalog()` を呼び、runtime mount 上の catalog を確認する。
- `Makefile`
  - `make sample-pack-runtime-smoke` を追加した。
- `tests/README.md`
  - representative runtime smoke の導線を追加した。
- `sample/README.md`
  - sample catalog guard の近くから runtime smoke を辿れるようにした。

## 背景

- `SamplePackCatalogTest` と `make sample-pack-compose-smoke` で static contract と compose merge は押さえられるようになったが、実際に pack を起動して health / seed-loaded 状態を見る導線はまだなかった。
- いきなり `sample2-8` 全件に重い smoke を増やすより、まず representative 2 pack で current runtime path を押さえる方がコストと効果のバランスが良かった。
- `sample3` は resource loader も含めて current runtime mount を確認できるため、static host-side check では見えない層を補える。

## 検証

- `bash -n mtool/scripts/check_sample_pack_runtime_smoke.sh`
  - pass
- `make sample-pack-runtime-smoke`
  - pass

## 補足

- これは representative smoke であり、full integration suite や `sample2-8` 全件の long-running scenario ではない。
- heavier smoke を増やす場合でも、この command は quick regression check として維持できる。
