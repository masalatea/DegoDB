# Disposable Output Under Work

## 概要

- disposable な runtime file は sample pack を含めて `work/` と `tmp/` に集約し、`make clean` で消える前提へ揃えた。
- `sample/<pack>/` は durable input だけを置く root とし、`output/` を正本扱いしない方針へ更新した。
- sample pack compose override は `APP_WORK_ROOT=/var/www/work/sample-packs/<pack>` を使うように変更した。

## 変更内容

- sample pack compose (`sample/sample1-simple-table/compose.yaml` から `sample/sample8-misc/compose.yaml`) の `APP_WORK_ROOT` を `work/sample-packs/<pack>/` へ変更した。
- sample pack seed の `project_source_outputs.source_output_dir` / `source_temp_output_dir` を `work/source-outputs/{project_key}/{source_output_key}` / `work/staging/source-outputs/{project_key}/{source_output_key}` に揃えた。
- root `README.md`、`sample/README.md`、各 sample README、`docs/internal/source-output-path-policy.md`、`docs/internal/runtime-architecture.md` を新ルールへ更新した。
- repo 配下に残っていた sample pack の `output/` directory を削除した。

## 意図

- `sample/` 配下には seed や curated reference のような durable input だけを残す。
- generator の staging、artifact snapshot、publish 済み current raw output のような再生成可能 file は `work/` 側へ集約する。
- `make clean` の対象と durable tree を path だけで明確に分ける。

## 補足

- `sample/sample1-simple-table/reference/` は引き続き durable な curated source として残す。
- `work/sample-packs/<pack>/` 配下の file は scratch / snapshot / staging 扱いなので、`make clean` で消えてよい。
