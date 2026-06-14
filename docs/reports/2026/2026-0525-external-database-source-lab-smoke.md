# 2026-05-25 External Database Source Lab Smoke

## 結論

- admin-managed external named DB source の local smoke を current root stack で通した。
- `/settings/database-sources -> named-live-schema import -> sync -> OPENAPI-JSON / DBTABLE-PROXY-SERVER publish -> lab Swagger page load -> published proxy route` の縦導線は now reusable command で再現できる。

## 追加したもの

- `mtool/scripts/check_external_database_source_lab_swagger_flow.php`
- `make mtool-external-source-lab-smoke`

## smoke の内容

- admin 側に temporary source `ext_smoke_0525041918a07c` を作成した
- `/projects/MTOOL/tables/import?source=named-live-schema:{source_key}&table=lab_experiments` の preview / apply を HTTP 経由で通した
- host-side service call で `sync_project_data_classes` / `sync_project_db_access` / `OPENAPI-JSON` publish / `DBTABLE-PROXY-SERVER` publish を通した
- lab 側で `/runs/swagger/MTOOL?source_output_key=OPENAPI-JSON` の page load を確認した
- `db_source_key=ext_smoke_0525041918a07c` を付けた published proxy route が `Bootstrap Health Check` と `Compare Output Prototype` の 2 row を返すことを確認した
- default cleanup で temporary source は最後に削除した

## 実行結果

- command: `php mtool/scripts/check_external_database_source_lab_swagger_flow.php`
- published artifact:
  - `OPENAPI-JSON=20260525-041924-5dd8577e`
  - `DBTABLE-PROXY-SERVER=20260525-041924-5a32a01d`

## 補足

- `lab-live-schema -> Swagger Try It Out` の実ブラウザ確認は既に別 lane で済んでいる
- external named source については今回 HTTP smoke までを current 化し、browser-only の `Try It Out` 実演は次段の任意タスクへ回す
