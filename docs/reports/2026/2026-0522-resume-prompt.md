# 2026-05-22 Resume Prompt

最新版のコピペ用再開 prompt。これは 2026-05-22 までの tutorial lane `sample10` current 化、`lab-db-ui` 追加、`lab-live-schema` import source 対応、`make up` の URL / credential 表示整備、Lab DB から Admin import できる current lane 整備に加え、2026-05-25 時点で `lab-live-schema -> canonical import -> DB Access bootstrap/sync -> proxy/OpenAPI publish -> Lab Swagger Try It Out` を実ブラウザで通し、admin-managed external named DB source でも `import -> sync -> output publish` を contract test で通した状態まで反映した派生文書であり、背景と判断根拠の正本は各 report 側にある。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。2026-05-25 週の開始点として扱ってください。

今日の到達点:
- `original-codes/` は host-side reference only のまま維持し、Docker runtime / artifact bundle / current runtime input には戻さない
- tutorial runtime lane は `sample/tutorials/sample01-simple-table-runtime` から `sample/tutorials/sample10-dbaccess-mini-crud-flow` まで current
- `sample10-dbaccess-mini-crud-flow` は `SupportTicket` 1 table に対して `GetSupportTicketList` / `GetSupportTicket` / `InsertSupportTicket` / `UpdateSupportTicket` / `DeleteSupportTicket` の 5 function を 1 class にまとめた capstone tutorial
- `lab-db-ui` service を `compose.yaml` に追加済み。軽量 DB UI として Adminer を使い、default では `http://127.0.0.1:8083` で `db-lab` を編集できる
- `make up` / `make start` は `admin` / `lab` / `lab-db-ui` の URL を表示し、login 情報と DB credential も表示する
- app config に `APP_LAB_DB_*` を追加済み
- `mtool/app/database.php` は `db` 以外の config key で silent fallback しない
- admin 側の table import source に `lab-live-schema` を追加済み
- `docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=<PROJECT_KEY> --source=lab-live-schema` で `db-lab` から canonical metadata へ import できる
- admin 側の `/projects/{project_key}/tables/import?source=lab-live-schema` でも同じ import source を選べる
- table import preflight は選択した named source DB を probe するように更新済み
- `lab-live-schema` の managed scope は `live-schema` 用の `MTOOL` self-host alias map から分離済みで、root stack の preview / apply は `lab_experiments` を `source_table_count=1` として扱い、self-host canonical 21 table を stale delete しない
- `docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=MTOOL --source=lab-live-schema` は current DB で `lab_experiments` 1 table / 10 columns を `same` として確認できる
- `docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=MTOOL` は `41 classes / 397 fields` を `same` で通る
- `docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php --project-key=MTOOL` は canonical bootstrap path を使って完走し、`lab_experiments` を含む `117 classes / 701 functions` を sync できる
- canonical bootstrap は bootstrap 生成 function に対して `single_proxy_auth_type=NoSecurity` を既定補完し、`INSERT` / `UPDATE` の `parameter_type=classobject` を補完する。既存 manual/seed item は preserve する
- canonical bootstrap function は初回 `sync_project_db_access.php` insert 時、generic single-function source output が存在すれば `DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` へ default target assignment される
- bootstrap runtime materialization は stale file を再利用せず毎回 rewrite し、generated PHP concatenation も実行可能形式に修正済み
- `ProjectSourceOutput` は `program_language=json` / `class_type=OpenAPI` / `artifact_strategy=openapi-json` / `target_binding_type=single-function-proxy` を受け付ける
- `mtool/docker/mariadb/config-seed/034_single_proxy_swagger_source_output_seed.sql` を追加し、`DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` の bootstrap-default row と `dbtable` 2 function の target assignment を core seed へ昇格済み
- `mtool/app/project_output_openapi_generator.php` で single-function proxy metadata から minimal `openapi.json` / `build-plan.json` / `README.md` を生成できる
- `mtool/app/lab_swagger_page.php` と `/runs/swagger/{project_key}` で generated spec を読める
- `mtool/app/lab_published_single_proxy_page.php` と `/runs/proxy/{project_key}/{source_output_key}/{endpoint_filename}` で published single-proxy output を lab/admin から same-origin relay 実行できる
- proxy server artifact は copied runtime subset だけでは不足していたため、published relay 実行に必要な runtime bundle file 同梱、legacy parent 非依存の DBAccess base rewrite、`build-plan.json.items` 読み込み対応を追加済み
- local canonical DB の `DBTABLE-PROXY-SERVER` と `OPENAPI-JSON` row は `bootstrap-default` へ揃えてあり、`lab_experiments` 5 endpoints を publish できる
- latest publish は `OPENAPI-JSON=20260525-055920-e5294a55`、`DBTABLE-PROXY-SERVER=20260525-055920-8bfecd56`
- published spec は `work/source-outputs/MTOOL/OPENAPI-JSON/openapi.json`、proxy build plan は `work/source-outputs/MTOOL/DBTABLE-PROXY-SERVER/build-plan.json`
- generated `openapi.json` には `lab_experiments.Getlab_experimentsList` / `Getlab_experiments` / `Insertlab_experiments` / `Updatelab_experiments` / `Deletelab_experiments` が載り、`build-plan.json` 側では `NoSecurity` と object/scalar input kind が反映される
- `docker compose exec -T web-lab ... /runs/proxy/.../proxyserver-lab_experiments-Getlab_experimentsList.php` で `db_config_key` 未指定でも `lab_db` が auto-select され、2 row を返す
- `mtool/app/lab_swagger_service.php` は schema-aware example normalization を持ち、generated `openapi.json` の empty object example `{}` を viewer request textarea で `[]` に潰さない
- `node mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js` と `make mtool-external-source-lab-browser-smoke` を追加済みで、admin-managed external named source を prepare した後に headless Chrome で lab login -> `/runs/swagger/MTOOL?source_output_key=OPENAPI-JSON&db_source_key={source_key}` -> `lab_experiments.Getlab_experimentsList` `Try It Out` まで再現できる
- browser smoke は default で `lab_experiments` の `Insert -> Get -> Update -> Get -> Delete -> List` CRUD cycle まで通す。`--list-only` を付けると旧来の list smoke に戻せる
- latest browser CRUD smoke は temporary source `ext_smoke_05250559156009` で通り、artifact は `output/playwright/external-source-lab-swagger/20260525-145915/` に残る。inserted id は `3`、`experiment_key=EXP-SWAGGER-1779688764397-62039`、delete 後の final row names は `Bootstrap Health Check` / `Compare Output Prototype`
- latest list-only browser smoke は temporary source `ext_smoke_05250559593982` で通り、artifact は `output/playwright/external-source-lab-swagger/20260525-145959/` に残る。`crud_cycle.executed=false`
- `lab` の Swagger viewer は `project-token` / `login-cookie-token` 向けに `Auth Helper Inputs` を持ち、legacy endpoint の required/optional auth field notice を表示しつつ、空欄の `TOKEN` / `LOGIN_COOKIE_TOKEN` を送信時だけ補完できる
- `lab` の Swagger viewer は `db_source_key` selector を持ち、`/runs/swagger/MTOOL?source_output_key=OPENAPI-JSON&db_source_key={source_key}` で external named source を page state と Try It Out request の両方に保持できる
- `lab` の Swagger viewer は `supports_proxy_runtime_read=1` の source だけを `db_source_key` selector に出す。manual query で policy 外 key を入れた場合は notice を出して auto-select に戻す
- `app_load_config()` は built-in named database source catalog (`db` / `config_db` / `lab_db`) を持ち、table import live source と published proxy relay の DB 解決はこの catalog を使う
- `live-schema` / `lab-live-schema` は `database_source_key` で built-in source を参照するように更新済みで、preflight probe も `app_probe_database_source()` 経由になった
- published proxy relay は `db_source_key` query を新設し、旧 `db_config_key` query は互換入力として残す。未指定時は named source catalog の proxy-runtime candidate priority と canonical store fallback で runtime DB を選ぶ
- explicit `db_source_key` / `db_config_key` は `supports_proxy_runtime_read=1` の source のみ許可し、policy 外 key は `422` で拒否する
- `mtool/docker/mariadb/config-initdb/028_database_source_metadata.sql` と `mtool/app/database_source_repository*.php` を追加し、config DB `database_sources` canonical table に external named source を persist できる
- admin 側の `/settings/database-sources` で external named DB source CRUD ができ、persisted source は runtime catalog に merge されて `named-live-schema:{source_key}` import option と published proxy relay の named runtime candidate に反映される
- `tests/Integration/ExternalDatabaseSourceOutputFlowContractTest.php` で external named source を作成し、`named-live-schema:{source_key}` import -> `sync_project_data_classes.php` / `sync_project_db_access.php` -> `OPENAPI-JSON` / `DBTABLE-PROXY-SERVER` publish -> generated `openapi.json` / proxy build-plan 検証まで通る
- `php mtool/scripts/check_external_database_source_lab_swagger_flow.php` と `make mtool-external-source-lab-smoke` を追加し、admin UI の `/settings/database-sources` で一時 external source を作成 -> `named-live-schema:{source_key}` preview/apply -> host-side sync/publish -> lab の `/runs/swagger/MTOOL?source_output_key=OPENAPI-JSON&db_source_key={source_key}` page load と selector state -> `db_source_key={source_key}` 付き published proxy route まで localhost smoke できる
- latest localhost smoke は temporary source `ext_smoke_0525044504efff` で通り、proxy route は `Bootstrap Health Check` と `Compare Output Prototype` の 2 row を返した。default では最後に temporary source を削除する
- `mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js` は operation ごとの request / response / screenshot を `result.json` に残し、current `lab_experiments` lane では mutation result の読み戻しと delete clean-up まで検証する
- docs は `README.md`、`docs/start-here.md`、`docs/current-supported-workflow.md`、`docs/common-tasks.md`、`docs/overview.md`、`docker/README.md` を更新済み
- contract test として `tests/Integration/LabDbIngressContractTest.php` を追加済み
- contract test として `tests/Integration/OpenApiSourceOutputContractTest.php` を追加済み
- contract test として `tests/Integration/ProjectDbAccessSyncBootstrapContractTest.php` と `tests/Integration/ProjectDbAccessBootstrapRuntimeContractTest.php` を追加済み
- contract test として `tests/Integration/MtoolCoreSingleProxySwaggerSeedContractTest.php` を追加済み
- contract test として `tests/Integration/ExternalDatabaseSourceOutputFlowContractTest.php` を追加済み
- targeted Docker-backed test は `LabDbIngressContractTest` が `OK (4 tests, 52 assertions)`、`ProjectDbAccessSyncBootstrapContractTest` が `OK (4 tests, 11 assertions)`、`ProjectDbAccessBootstrapRuntimeContractTest` が `OK (2 tests, 5 assertions)`、`OpenApiSourceOutputContractTest` が `OK (11 tests, 1608 assertions)`、`MtoolCoreSingleProxySwaggerSeedContractTest` が `OK (2 tests, 17 assertions)`、`DatabaseSourceCatalogContractTest` が `OK (2 tests, 45 assertions)`、`ExternalDatabaseSourceOutputFlowContractTest` が `OK (1 test, 30 assertions)`
- `bash mtool/scripts/check_sample_pack_compose_smoke.sh` は 17 pack で pass
- full suite は `make test` で `108 tests / 4143 assertions` pass
- sample 系 compose は test 後に auto-cleanup され、`docker compose -f compose.yaml -f sample/tutorials/sample01-simple-table-runtime/compose.yaml ps` は空になることを確認済み
- `lab-live-schema` の runtime check では `source_schema_name=lab_app` と `table_names=[\"lab_experiments\"]` を確認済み
- `show_runtime_reference_status.php --require-current` は現在 `reference-snapshot-only` で non-zero。durable snapshot (`20260521-023351-d52e8c8b`) は残っているが `work/` 側 latest artifact history は無い
- 次段の計画は `docs/reports/2026/2026-0522-lab-admin-swagger-flow-plan.md` に固定済み
- latest promoted artifact は引き続き `20260521-023351-d52e8c8b`

重要な前提:
- `設定` と `canonical metadata` の責務は `admin` に寄せる
- `lab` は `runtime 実験 / compare / review / Swagger viewer / proxy relay` の場として扱う
- `db-lab` は editable import source であり、canonical metadata store ではない
- external DB import は one-off DSN flag ではなく、named DB source model に一般化して足す
- `original-codes/` を runtime input に戻さない
- local では port 競合がありうるので、`make test` はまず `18091/18092/43091/43092` の成功例を使う
- admin 側の `/settings/database-sources` で external named DB source CRUD ができ、config DB の `database_sources` canonical table に保存される
- persisted external source は runtime catalog に merge され、table import の `named-live-schema:{source_key}` option と published proxy relay の named runtime candidate に反映される
- `lab-live-schema` apply は now actual lab table scope だけを managed target にする。修正前は `MTOOL` self-host alias scope に誤って落ちて canonical self-host table を巻き込むことがあったが、現在は `lab_experiments` のような実 table だけが preview / apply 対象になる
- legacy function で `single_proxy_auth_type` が空のものは current generator では `project-token` 扱いになる。`dbtable` のような旧 row は token が必要だが、viewer 側に notice と helper input を追加したため、request textarea を毎回手直ししなくても試せる。current bootstrap で作られた `lab_experiments` function は `NoSecurity` 補完で通る
- `sync_project_db_access.php --project-key=MTOOL` には imported table 向け canonical bootstrap path が入り、`lab_experiments` は current metadata 起点で DB Access class / function まで起こせる。generic single-function outputs への default target assignment と core seed への昇格も入ったため、次の主題は wording 整理と browser lane の hardening である
- current policy では explicit runtime source 指定も `supports_proxy_runtime_read` flag に従う。viewer helper と published proxy relay で挙動を揃えてある

次の優先タスク:
1. `lab` 側に設定っぽく見える導線が残っていれば、experiment / build / compare / swagger / proxy relay 中心の wording へ整理する
2. browser smoke を CI/定期確認に寄せるなら、headless Chrome 実行環境と artifact 退避方針を固める
3. 必要なら `lab_experiments` 以外の imported table や auth-required endpoint に browser lane を広げる

最初に読むべき文書:
- docs/reports/2026/2026-0522-lab-admin-swagger-flow-plan.md
- docs/reports/2026/2026-0522-end-of-day-status.md
- docs/current-supported-workflow.md
- docs/common-tasks.md
- docs/overview.md
- README.md
- tests/README.md

実装前に最初に見るファイル:
- compose.yaml
- Makefile
- mtool/app/config.php
- mtool/app/database.php
- mtool/app/project_table_import_source.php
- mtool/app/project_table_import_service.php
- mtool/app/project_tables_import_page.php
- mtool/app/router.php
- mtool/app/project_output_service.php
- mtool/app/project_output_openapi_generator.php
- mtool/app/project_output_proxy_generator.php
- mtool/app/lab_swagger_page.php
- mtool/app/lab_published_single_proxy_page.php
- mtool/app/lab_endpoint_test_page.php
- tests/Integration/LabDbIngressContractTest.php
- tests/Integration/ProjectDbAccessSyncBootstrapContractTest.php
- tests/Integration/ProjectDbAccessBootstrapRuntimeContractTest.php
- tests/Integration/MtoolCoreSingleProxySwaggerSeedContractTest.php
- tests/Integration/OpenApiSourceOutputContractTest.php

最初に確認するコマンド:
- php mtool/scripts/show_runtime_reference_status.php --require-current
- php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
- make help | sed -n '1,80p'
- make up
- ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
- docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=MTOOL --source=lab-live-schema
- docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=MTOOL
- docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php --project-key=MTOOL
- docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=OPENAPI-JSON --publish
- docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=DBTABLE-PROXY-SERVER --publish
- make mtool-external-source-lab-smoke
- make mtool-external-source-lab-browser-smoke
```
