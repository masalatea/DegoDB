# 2026-05-26 Compose Stack Helper Commonization

## 要約

- local compose lane の `compose.yaml + compose.local-db-config.yaml` 解決を small helper script へ寄せた。
- sample pack runner、sample-pack-backed PHPUnit runner、seed apply、compose smoke、runtime smoke が同じ compose stack resolver を使うようにした。
- `show_compose_access_urls.sh` は current では base-only consumer のまま残し、sample/helper 系の重複だけを先に削った。

## 変更

- `mtool/scripts/list_compose_stack_files.sh`
  - compose file path を merge 順で 1 行ずつ返す helper を追加した。
  - `--lane=local|base` と repeated `--compose-file=...` を受ける。
  - default lane は `local` で、`compose.local-db-config.yaml` を自動で含める。
- `sample/_pack-support/sample-pack-runner.sh`
- `mtool/scripts/run_sample_pack_phpunit_test.sh`
- `mtool/scripts/apply_config_sample_seed.sh`
- `mtool/scripts/check_sample_pack_compose_smoke.sh`
- `mtool/scripts/check_sample_pack_runtime_smoke.sh`
  - root compose stack を hardcode せず、helper script の出力から `docker compose -f ...` を組み立てるようにした。
- `tests/Integration/SamplePackCatalogTest.php`
  - shared runner が compose stack helper を使うこと
  - helper が local overlay を default lane に持つこと
    を固定した。

## current rule

- sample/helper 系の compose stack 解決
  - `bash mtool/scripts/list_compose_stack_files.sh`
    を single source とする。
- local default lane
  - `compose.yaml + compose.local-db-config.yaml`
  - helper の default lane と一致させる。
- base lane
  - `compose.yaml` のみ
  - external config DB や base-only consumer 用に `--lane=base` を残す。
- `show_compose_access_urls.sh`
  - current は base-only consumer のまま維持し、この slice では対象外とした。

## 検証

```bash
bash -n mtool/scripts/list_compose_stack_files.sh
bash -n sample/_pack-support/sample-pack-runner.sh
bash -n mtool/scripts/run_sample_pack_phpunit_test.sh
bash -n mtool/scripts/apply_config_sample_seed.sh
bash -n mtool/scripts/check_sample_pack_compose_smoke.sh
bash -n mtool/scripts/check_sample_pack_runtime_smoke.sh
make sample-pack-compose-smoke
make config-db-preflight
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- shell syntax check: all pass
- `make sample-pack-compose-smoke`: `17 pack(s)` pass
- `make config-db-preflight`: `ok=true`, `schema_current=true`
- full suite: `OK (124 tests, 4453 assertions)`
