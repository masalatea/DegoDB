# Runtime Architecture / ランタイム構成

English companion:
This architecture guide explains the runtime layout of the rewrite. It covers the Docker services, the request flow, the role of `admin`, `lab`, `app`, and `reference`, and how generated runtime artifacts fit into the current system.

## 目的

- 新実装の HTTP 入口、共通コード、Docker サービス境界を短時間で把握できるようにする。
- `mtool/` 配下の `admin` / `lab` / `app` / `extensions` / `reference` / `scripts` を current runtime code として明文化する。

補足:

- この文書は技術構成に寄せた説明であり、ツール本来の `DB 構造 -> import -> Data Class -> DB Access -> Source Output` という利用フローは `docs/overview.md` で説明する。

## 全体像

- `mtool/admin/public/index.php`
  - 設定変更用サイトの front controller
- `mtool/lab/public/index.php`
  - 実験用サイトの front controller
- `mtool/app/http.php`
  - 2 サイト共通の request 処理入口
- `mtool/`
  - current runtime code の正本
- `mtool/reference/dbclasses/`
  - promoted runtime dbclasses reference
- `work/runtime-sources/`
  - html / proxy / legacy source output 用の disposable staging root
- `work/source-outputs/`
  - `MTOOL` current raw output の root
- `work/artifacts/source-outputs/`
  - Source Output artifact の file-based storage
- `sample/`
  - sample pack の durable root
  - `Project 2+` 相当の pack を置く
- `work/sample-packs/<pack>/`
  - sample pack ごとの disposable runtime root
- `mtool/extensions/`
  - human / Codex companion layer の配置 root
- `work/compare-output-assets/`
  - Compare Output template / ignore rule asset の file-based storage
- `work/job-history/compare-output/`
  - Compare Output job manifest / snapshot の file-based storage
- `compose.yaml`
  - 2 つの Web コンテナと 2 つの DB コンテナを定義
- `docker/`
  - repo 全体で共有する base Docker 資産を置く
  - `php-apache/` と共通 initdb だけを持つ
- `mtool/docker/compose/`
  - `Project 1 = MTOOL` の core compose override を置く
- `tests/scenarios/`
  - Docker を使う検証 scenario を置く
- `sample/<category>/<pack>/compose.yaml`
  - `Project 2+` 相当の sample pack compose override を置く

Web コンテナはどちらも同じ `docker/php-apache/Dockerfile` から起動し、違いは環境変数で切り替える。
root `docker/` は base image / base initdb 専用とし、`Project 1 = MTOOL` 固有の compose override と seed は `mtool/docker/` 側へ分離する。
必要なら `compose.yaml` に `mtool/docker/compose/*.compose.yaml` または `sample/<category>/<pack>/compose.yaml` を重ね、scenario ごとに initdb seed と writable runtime state を分離する。sample pack では共通 schema (`docker/mariadb/config-initdb/`) と pack seed だけを scenario-local initdb staging volume へコピーしてから MariaDB entrypoint を起動し、disposable runtime state は `work/sample-packs/<pack>/` へ出す。

## Docker 上の責務分離

### `web-admin`

- `APP_SITE=admin`
- 設定変更用サイト
- `db-config` へ接続
- `/var/www/work` を writable mount
- `/var/www/mtool/app` を read-only mount
- `/var/www/mtool/extensions` を writable mount
- `/var/www/mtool/reference` を read-only mount
- `/var/www/mtool/scripts` を read-only mount
- `/var/www/sample` を read-only mount
- scratch / log も `work/tmp/` 配下を使う
- sample compose override は `APP_WORK_ROOT=/var/www/work/sample-packs/<pack>` を切り替える
- canonical metadata を読みながら artifact を UI / CLI の両方から生成する

### `web-lab`

- `APP_SITE=lab`
- 実験用サイト
- `db-lab` へ接続
- `db-config` を read-only 参照先として追加接続
- `/var/www/mtool/app` を read-only mount
- `/var/www/mtool/extensions` を writable mount
- `/var/www/mtool/reference` を read-only mount
- `/var/www/mtool/scripts` を read-only mount
- `/var/www/work` を writable mount
- `/var/www/sample` を read-only mount
- scratch / log も `work/tmp/` 配下を使う
- sample compose override は `APP_WORK_ROOT=/var/www/work/sample-packs/<pack>` を切り替える
- compare output 実行時は canonical definition を `db-config` から読み、`work/compare-output/` と `work/job-history/compare-output/` を中心に disposable file を出力する

### `db-config`

- 設定の正本を置く DB
- `admin` 側の管理系データを保持する前提
- default では `Project 1 = MTOOL` core seed を持つ
- sample pack では `sample/<category>/<pack>/seed/*.sql` だけを `/docker-entrypoint-initdb.d/` へ staged copy して fresh initdb 時にだけ混ぜる

### `db-lab`

- 実験、比較、試験のための DB
- 壊して作り直しやすいことを優先する前提
- scenario compose ごとに volume を分離し、sample pack 実験で core state を汚さない

## HTTP リクエストの流れ

1. Apache が `mtool/admin/public/index.php` または `mtool/lab/public/index.php` に着地させる。
2. front controller が `mtool/app/http.php` を読み込む。
3. `mtool/app/bootstrap.php` が環境変数からサイト設定を組み立てる。
4. `mtool/app/session.php` が session 名と cookie 属性を確定し、session を開始する。
5. `mtool/app/request.php` が request id、path、method などを正規化する。
6. `mtool/app/router.php` が route 名と path parameter を決定する。
7. `mtool/app/middleware.php` が保護ルート判定と例外ハンドリングを行う。
8. route ごとの page renderer または JSON renderer へ dispatch する。

## 現在の route 一覧

| route | 用途 | 認証 |
| --- | --- | --- |
| `/` | ブートストラップ確認画面 | 不要 |
| `/health` | DB 接続確認付き health JSON | 不要 |
| `/login` | スタブ認証ログイン | 不要 |
| `/logout` | POST ログアウト | session 必須 |
| `/dashboard` | ログイン後の確認画面 | 必要 |
| `/projects` | `admin` 側の canonical project 一覧、作成、更新 | 必要 |
| `/projects/{project_key}` | 旧 `index.php` 相当の project 詳細ハブ | 必要 |
| `/projects/{project_key}/settings` | project 基本設定 | 必要 |
| `/projects/{project_key}/source-outputs` とその配下 | Source Output definition / artifact 管理 | 必要 |
| `/projects/{project_key}/proxy/custom` とその配下 | Custom Proxy metadata / step / target 管理 | 必要 |
| `/projects/{project_key}/compare-output-settings` とその配下 | Compare Output definition / additional path 管理 | 必要 |
| `/runs/compare-output/{project_key}` | `lab` 側の Compare Output 実行 | 必要 |
| `/runs/compare-output/{job_key}` | `lab` 側の Compare Output job review | 必要 |
| `/api/runs/compare-output/{job_key}` | `lab` 側の Compare Output job JSON API | 必要 |
| `/projects/{project_key}/tables` とその配下 | table metadata import / canonical detail | 必要 |
| `/projects/{project_key}/data-classes` とその配下 | data class sync / canonical detail | 必要 |
| `/projects/{project_key}/db-access` とその配下 | DB Access / function preview | 必要 |
| `/experiments` | `lab` 側の experiment 一覧、作成、更新 | 必要 |

`/projects?edit=<project_key>` と `/experiments?edit=<experiment_key>` は、同じ route 上で編集フォームを開くための query 入口として扱う。
`/projects/{project_key}` は、旧 `dev web/db/index.php` 相当の Project ハブとして扱う。

補足:

- 現在の proxy UI は `custom` 側が先行している。
- ただし target design としては、`single-function proxy` は `db-access` / function detail 配下に戻す。
- `custom proxy` はその代用品ではなく、multi-step composition 専用の route として分ける。

## `mtool/app/` の役割

### 設定と初期化

- `config.php`
  - サイト既定値と環境変数をまとめる
- `bootstrap.php`
  - 起動時の設定配列を返す
- `database.php`
  - PDO と接続確認

### HTTP 基盤

- `request.php`
  - request 情報、フォーム値、redirect path の正規化
- `response.php`
  - HTML / JSON / redirect の共通 response
- `router.php`
  - path から route 名を決定
- `middleware.php`
  - 認証前提の route 制御と例外処理

### 認証まわり

- `session.php`
  - site ごとの session 名を適用
- `csrf.php`
  - CSRF token の発行と検証
- `auth.php`
  - session principal とスタブ認証を扱う

### repository adapter

- `project_repository.php`
  - page 層へ公開する project repository adapter
- `project_repository_pdo.php`
  - 現行 bootstrap schema 向け PDO 実装
- `project_repository_generated_bootstrap.php`
  - 将来の generated 実装差し替え用の placeholder
- `experiment_repository.php`
  - page 層へ公開する experiment repository adapter
- `experiment_repository_pdo.php`
  - 現行 bootstrap schema 向け PDO 実装
- `experiment_repository_generated_bootstrap.php`
  - 将来の generated 実装差し替え用の placeholder
- `db_access_repository.php`
  - DB Access class/function metadata 用 adapter
- `db_access_repository_pdo.php`
  - `project_db_access_classes` / `project_db_access_functions` / query designer sub-resource 向け PDO 実装
- `source_output_repository.php`
  - Source Output canonical definition 用 adapter
- `source_output_repository_pdo.php`
  - `project_source_outputs` 向け PDO 実装
- `compare_output_repository.php`
  - Compare Output canonical definition 用 adapter
- `compare_output_repository_pdo.php`
  - `project_compare_outputs` / `project_compare_output_additional_paths` 向け PDO 実装
  - `lab` 実行時も `db-config` を read-only 参照先として使う
- `compare_output_service.php`
  - local filesystem 上で compare output file を生成する service
  - `compare_path` 配下の `- tmp output` folder と additional path pair を比較し、project asset で上書き可能な template / ignore rule を使って Text / Windows Batch / Mac Command を出力する
- `compare_output_asset_service.php`
  - Compare Output template / ignore rule asset の file-based service
  - `work/compare-output-assets/{project_key}/` 配下の project override と built-in default を切り替える
- `compare_output_job_service.php`
  - Compare Output 実行結果を file-based job として保存する service
  - `work/job-history/compare-output/{project_key}/{job_key}/manifest.json` と snapshot file を扱う

### generated runtime reference helper

- `generated_runtime.php`
  - generated runtime の配置状況と file count を要約
- `generated_catalog.php`
  - `data-*.php` / `dbaccess-*.php` を対にして runtime reference catalog を作る
  - self-generated runtime bundle の `base/` / `_base/` / `_wrappers/` を解決し、logical method/property/class catalog を返す
- `project_output_service.php`
  - prepared runtime source tree または staged proxy source tree を Source Output artifact に束ねる service
  - `manifest.json` schema version 3 と `tar.gz` archive を生成する
- `project_output_runtime_generator.php`
  - `RUNTIME-DBCLASSES` 用に runtime reference tree を staging し、sync 済み canonical DB Access metadata で root `dbaccess-*` を overlay する service
  - `_support/runtime-generation-manifest.json` へ generation summary を書く
- `project_output_proxy_generator.php`
  - canonical custom proxy metadata と runtime dbclasses reference を入力に proxy source tree を staging する service
  - `custom-proxy-server` / `custom-proxy-client` strategy を扱う
- `mtool/scripts/create_compare_output.php`
  - Compare Output definition を元に local compare output file を生成する CLI
  - file-based job manifest を保存し、`job_key` と review route を JSON で返す

### 画面と endpoint

- `bootstrap_page.php`
  - 起動確認
- `login_page.php`
  - ログインフォームと POST 処理
- `dashboard_page.php`
  - ログイン後の保護画面
- `project_list_page.php`
  - `admin` 側の project 一覧、追加、更新
- `project_detail_page.php`
  - `admin` 側の project hub と設定モジュール導線
  - generated runtime の reference / generation 状態表示
- `project_settings_page.php`
  - `admin` 側の project 基本設定
- `project_source_outputs_page.php`
  - `admin` 側の Source Output 一覧
  - canonical definition の追加と runtime/proxy artifact 一覧を表示する
- `project_source_output_detail_page.php`
  - `admin` 側の Source Output detail
  - definition の詳細表示、build plan preview、artifact 生成を行う
- `project_source_output_edit_page.php`
  - `admin` 側の Source Output edit
  - canonical definition を保存する
- `project_source_output_download_page.php`
  - `admin` 側の generated artifact download endpoint
- `project_source_output_route_common.php`
  - Source Output route 共通の bootstrap / path helper
- `project_custom_proxies_page.php`
  - `admin` 側の Custom Proxy 一覧 / create / delete
- `project_custom_proxy_detail_page.php`
  - `admin` 側の Custom Proxy detail
  - auth / transaction / target source output を保存する
- `project_custom_proxy_functions_page.php`
  - `admin` 側の Custom Proxy step 一覧 / create / update / delete
- `project_custom_proxy_route_common.php`
  - Custom Proxy route 共通の bootstrap / path helper
- `project_compare_output_settings_page.php`
  - `admin` 側の Compare Output 一覧 / create / update / delete
  - 選択中 definition から local compare output file を生成する
  - template asset / ignore rule asset の project override を保存する
- `project_compare_output_additional_paths_page.php`
  - `admin` 側の Compare Output Additional Path 一覧 / create / update / delete
- `project_compare_output_route_common.php`
  - Compare Output route 共通の bootstrap / path helper
- `lab_compare_output_page.php`
  - `lab` 側の Compare Output 実行画面
  - canonical definition を read-only 参照し、local compare output file を生成する
  - recent jobs 一覧と job review への導線を表示する
- `lab_compare_output_job_page.php`
  - `lab` 側の Compare Output job review 画面
  - snapshot、warning、pair、manifest path を表示する
- `lab_compare_output_job_api_page.php`
  - `lab` 側の Compare Output job JSON API
  - API 利用向けに保存済み manifest 内容を返す
- `project_tables_page.php`
  - `admin` 側の DB Table metadata 入口
  - canonical metadata を優先表示し、未導入 project だけ runtime reference fallback を出す
- `project_tables_import_page.php`
  - `admin` 側の table import preview / apply
- `project_table_detail_page.php`
  - `admin` 側の table detail
- `project_table_columns_page.php`
  - `admin` 側の column detail
- `project_data_classes_page.php`
  - `admin` 側の Data Class 入口
  - canonical metadata を優先表示し、未導入 project だけ runtime reference fallback を出す
- `project_data_classes_sync_page.php`
  - `admin` 側の data class sync preview / apply
- `project_data_class_detail_page.php`
  - `admin` 側の data class detail
- `project_data_class_fields_page.php`
  - `admin` 側の data class field detail
- `project_data_class_source_page.php`
  - `admin` 側の data class source preview
  - canonical-only class では runtime reference source 不在を明示する
- `project_db_access_page.php`
  - `admin` 側の DB Access 入口
  - generated catalog と canonical class metadata を重ねて表示
- `project_db_access_sync_page.php`
  - `admin` 側の DB Access class/function sync UI
  - legacy import は持たず、designer baseline は initdb seed で扱う
- `project_db_access_sync_service.php`
  - runtime reference 内の `dbaccess-*.php` を `project_db_access_classes` / `project_db_access_functions` へ bulk sync する
- `mtool/scripts/export_mtool_db_access_seed.php`
  - host 側の dev-time export tool
  - current config DB と legacy metadata から `019_project_db_access_class_function_seed.sql` / `020_project_db_access_designer_seed.sql` / `022_backfill_runtime_legacy_selectlist_sort_order_columns.sql` を再生成する
  - legacy 側は temporary imported `legacy_seed_tmp` でも host から明示指定した `--host-side --sql-dump=original-codes/mtool.sql` でもよい
  - base Docker runtime には `original-codes/` を mount しない
- `project_db_access_detail_page.php`
  - `admin` 側の DB Access class detail preview
  - saved class metadata があれば併記
- `project_db_access_edit_page.php`
  - `admin` 側の DB Access class setting
  - `data-da.php` / `dbaccess-da.php` を使った legacy schema draft と class metadata 保存
- `project_db_access_functions_page.php`
  - `admin` 側の function candidate 一覧 preview
  - saved function metadata があれば併記
- `project_db_access_function_change_order_page.php`
  - `admin` 側の function change-order
  - 保存済み canonical function row の一括順序更新と `RESET`
- `project_db_access_source_page.php`
  - `admin` 側の DB Access source preview
- `project_db_access_function_detail_page.php`
  - `admin` 側の function detail
  - `data-dafunc.php` を使った canonical field draft と function metadata 保存
- `project_db_access_function_move_page.php`
  - `admin` 側の function move
  - generated dbaccess file に同名 method がある DB Access へ canonical function row を移す
  - child designer row は同じ function id に紐づいたまま追従する
- `project_db_access_function_select_where_page.php`
  - `admin` 側の select where designer 一覧
  - `data-dafuncselectwhere.php` を参照しつつ canonical row を一覧する
- `project_db_access_function_select_where_input_aid_page.php`
  - `admin` 側の select where input-aid
  - generated property 候補と既存 row の対応を表示する
- `project_db_access_function_select_where_change_order_page.php`
  - `admin` 側の select where change-order
  - 一括順序更新と `RESET` を行う
- `project_db_access_function_select_where_edit_page.php`
  - `admin` 側の select where designer 編集
  - create / update / delete を行う
- `project_db_access_function_select_target_fields_page.php`
  - `admin` 側の select target fields designer 一覧
  - `data-dafuncselecttargetfields.php` を参照しつつ canonical row を一覧する
- `project_db_access_function_select_target_field_edit_page.php`
  - `admin` 側の select target field designer 編集
  - create / update / delete を行う
- `project_db_access_function_select_having_page.php`
  - `admin` 側の select having designer 一覧
  - `data-dafuncselecthaving.php` を参照しつつ canonical row を一覧する
- `project_db_access_function_select_having_edit_page.php`
  - `admin` 側の select having designer 編集
  - create / update / delete を行う
- `project_db_access_function_insert_target_fields_page.php`
  - `admin` 側の insert target fields designer 一覧
  - `data-dafuncinserttargetfields.php` を参照しつつ canonical row を一覧する
- `project_db_access_function_insert_target_field_edit_page.php`
  - `admin` 側の insert target field designer 編集
  - create / update / delete を行う
- `project_db_access_function_update_target_fields_page.php`
  - `admin` 側の update target fields designer 一覧
  - `data-dafuncupdatetargetfields.php` を参照しつつ canonical row を一覧する
- `project_db_access_function_update_target_field_edit_page.php`
  - `admin` 側の update target field designer 編集
  - create / update / delete を行う
- `project_db_access_function_update_delete_where_page.php`
  - `admin` 側の update/delete where designer 一覧
  - `data-dafuncupdatedeletewhere.php` を参照しつつ canonical row を一覧する
- `project_db_access_function_update_delete_where_input_aid_page.php`
  - `admin` 側の update/delete where input-aid
  - generated property 候補と既存 row の対応を表示する
- `project_db_access_function_update_delete_where_change_order_page.php`
  - `admin` 側の update/delete where change-order
  - 一括順序更新と `RESET` を行う
- `project_db_access_function_update_delete_where_edit_page.php`
  - `admin` 側の update/delete where designer 編集
  - create / update / delete を行う
- `project_db_access_function_source_page.php`
  - `admin` 側の function source preview
- `project_db_access_function_endpoint_page.php`
  - `admin` 側の endpoint contract preview
  - `data-dafunc.php` の proxy/auth 項目と saved function metadata を踏まえた draft
- `experiment_list_page.php`
  - `lab` 側の experiment 一覧、追加、更新
- `health.php`
  - health JSON
- `error_page.php`
  - 400 / 404 / 405 / 500

## 生成 runtime code の位置づけ

- 旧 `original-codes/mtool_lib/dbclasses/` は、システム自身が使う generated runtime layer だった。
- 新実装でもこの前提を維持する。
- 現在は promoted self-generated tree を `mtool/reference/dbclasses/` の durable runtime reference として使い、旧 `dbclasses` コピー導線は legacy fallback / recovery 用に残している。
- `bootstrap_dbclasses.sh` は archived historical helper として current mainline から外した。current supported runtime/tool workflow では実行せず、`make bootstrap-dbclasses` も archive 済み helper と snapshot restore を案内して fail fast するだけにした。
- authoritative runtime reference `mtool/reference/dbclasses/` の repair / rollback は、`make restore-runtime-reference-snapshot ARTIFACT_KEY=...` または `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` の snapshot-backed recovery に限定する。旧 `make bootstrap-dbclasses-runtime-reference` と archive 済み `bootstrap_dbclasses.sh --apply-to-runtime-reference` は retired guidance として fail fast する。
- current promote 導線は `make promote-runtime-reference` または `php mtool/scripts/promote_runtime_reference.php --artifact-key=...` であり、verified self-generated artifact の `mtool/dbclasses` を durable reference へ昇格する。
- promote / self-loop で扱う `_support/runtime-generation-manifest.json` には per-source rollout metadata に加えて `artifact_key` も保持し、durable reference 側でも最後に promote した verified artifact を辿れるようにする。
- promote 時には同じ tree を `mtool/reference/runtime-reference-snapshots/MTOOL/RUNTIME-DBCLASSES/{artifact_key}/` に保存し、`make restore-runtime-reference-snapshot ARTIFACT_KEY=...` または `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` で `work/` 外から restore できる。
- `php mtool/scripts/show_runtime_reference_status.php` または `make mtool-runtime-reference-status REQUIRE_CURRENT=1` で、promoted runtime reference と latest runtime artifact の同期状態を `artifact_key` 単位で確認しつつ、`durable_recovery_ready` で snapshot-backed recovery の有無も確認できる。
- `make test` / `make mtool-self-loop-check` のような verification run で latest artifact が進んでも、それだけで promoted runtime reference を更新したことにはならない。`stale-reference` は「new artifact が未採用」という運用状態として読み、default runtime reference を前進させる意思決定を伴う run にだけ promote を実行する。
- このため current runtime reference への legacy direct overwrite 導線は retire し、host-side staged legacy copy helper も current mainline から外した。もし host-side quarantined full-tree legacy copy が再び必要になった場合だけ、archive から archive 済み helper を明示的に復帰させて使う。
- `export_legacy_*_reference.php` 群も host-side export helper とする。`dataclass` / `dbtable` / `db_access` / `html` / `language_resource` は `--host-side --sql-dump=original-codes/mtool.sql` のような host filesystem 上の dump path を明示して使い、`table_schema` だけは `--host-side` と temporary imported legacy schema に対する `--dsn` / `--schema-name` を使う。
- `mtool/reference/*-catalog.json` や `mtool/resources/manifest.json` などに残る `source_dump_path=original-codes/mtool.sql` は provenance metadata であり、current runtime がその path を開く導線ではない。
- current live host-only helper inventory は `explicit export` と `provenance metadata` の 2 lane を主系として読む。旧 `last-resort staging` lane は archived historical lane であり、copied reference JSON や seed SQL を再生成する export helper だけを current durable input refresh の導線として残す。
- current emitted `RUNTIME-DBCLASSES` file contract の source of truth は `docs/internal/generated-code-strategy.md` の runtime artifact layout 節とする。ここでは generation flow だけを要約する。
- `RUNTIME-DBCLASSES` 生成時は、この runtime reference source を一度 staging し、sync 済み canonical DB Access metadata があれば root `dbaccess-*` を再生成する。data 側も final artifact では全 `99` class を `root data-*.php wrapper + base/data-*Base.php` へ出力する。promoted / emitted runtime tree は top-level wrapper、`base/`、`_support/legacy-dbaccess/`、`_support/runtime-generation-manifest.json`、`_runtime_loader.php` を持ち、`_base/` / `_wrappers/` は含めない。`generated_catalog.php` と runtime build-plan は historical self-generated bundle input として残る `base/` / `_base/` / `_wrappers/` layout も logical source として解釈できる。`_support/legacy-dbaccess/` は legacy delegate が残る場合の copied support か、delegate 不要時の compatibility placeholder として扱う。
- 現在の mode は `canonical-dbaccess-partial-sql-regenerated` であり、simple CRUD / first-pass joined select は SQL 本体まで canonical 再生成する。未対応の関数が残る場合だけ `_support/legacy-dbaccess/` を使うが、current promoted baseline では `legacy_delegate_function_count=0` のため generated DBAccess base は standalone である。
- `SELECTLIST` のうち legacy baseline に明示 `sort_order_columns` があるものは `022_backfill_runtime_legacy_selectlist_sort_order_columns.sql` で canonical metadata へ補完し、blank のものは legacy と同様に `ORDER BY` なしで生成する。
- 2026-05-19 時点の current baseline は `sql_regenerated_dbaccess_count=98` / `sql_regenerated_function_count=505` / `canonical_helper_function_count=7` / `canonical_data_class_count=99` / `data_entity_count=99` / `plain_data_candidate_count=63` / `non_plain_data_candidate_count=36` / `bootstrap_data_class_count=0` / `legacy_delegate_function_count=0` である。
- runtime 置換の横展開は 2 段で進める。plain DTO / simple CRUD / 既に generated manifest で一致している単純形は direct replacement lane とし、`MTOOL` runtime へそのまま広げてよい。一方で non-plain `data-*`、helper-heavy class、複数 declaration、未知の file contract は sample gate 先行とし、`tests/Integration/Sample9TestPatternDefaultPropertyOutputTest.php`、`Sample10CompareOutputCompanionDeclarationsOutputTest.php`、`Sample11DaDataclassMethodOnlyOutputTest.php`、`Sample12DbtablecolumnsWrapperPropertyOutputTest.php`、`Sample13ReqMethodAndEnumOutputTest.php`、`Sample14BuildSourceFuncCacheCompanionDeclarationsOutputTest.php`、`Sample15BuildLogCompanionDeclarationsOutputTest.php`、`Sample16LiveCheckResultCompanionDeclarationsOutputTest.php`、`Sample17SpecContentTopLevelDeclarationOutputTest.php`、`Sample18ProjectUserTopLevelDeclarationOutputTest.php`、`Sample19HtmlTemplateTopLevelDeclarationOutputTest.php`、`Sample20DaCustomProxyMethodAndEnumOutputTest.php`、`Sample21ProjectMethodAndEnumOutputTest.php`、`Sample22ProjectSourceOutputMethodAndEnumOutputTest.php`、`LegacyTopLevelDeclarationMigrationTest.php` を current gate / coverage とする。
- `LanguageResource*` designer child row の backfill 後、full self-loop は self-generated artifact 入力でも通過した。さらに artifact `20260519-031821-49b3d04f` を `mtool/reference/dbclasses/` へ promote した状態でも `make mtool-self-loop-check` が通っている。
- `config.php` は `APP_GENERATED_DBCLASSES_MODE` 未指定時に `_support/runtime-generation-manifest.json` を読み、current default mode を `self-generated-reference:canonical-dbaccess-partial-sql-regenerated` として自動判定する。
- `ProjectUser` / `SpecContent` / `htmlTemplate` も top-level declaration lane で wrapper/base へ昇格し、transition-state `data-*` は解消済みである。`ProjectUser` の `ProjectUserInOtherProject*` 2 property は legacy DTO compatibility を保つ supplemental property として維持した。`parameter_type=anotherfield` を含む joined select は non-empty `or_group` と `or_group_type=andorand` を含む join ON grouping まで current generator で扱え、`select_having` も canonical `select_target_fields` を参照する argument / fixed / field 比較まで再生成できる。残る主な gap は file/blob parameter であり、legacy 実体 (`DegoWorkplaceFile`, `email_buffer_attachment`) では `prepare()` + `bind_param("b")` + `send_long_data()` を使うため、current generator は `is_blob_target=1` を明示 delegate 対象にしている。さらに canonical metadata の保存経路でも repository 層が同じ contract を検証し、unsupported な blob/file metadata が sync や bridge 経由で混入しないようにしている。seed/export 経路も current runtime reference に対する preflight で同じ contract を確認する。
- `ApacheHostSetting` と `ApacheHostSettingTemplate` は runtime reference scope から明示除外している。旧実装での用途は Apache config template 展開、log monitor snapshot、project host assignment 選択肢の組み立てであり、current app runtime 自体の自己生成 bundle が依存する class ではない。必要になった場合は runtime dbclasses ではなく infra catalog / host-assignment module 側で別管理する。
- 生成結果の要約は `_support/runtime-generation-manifest.json` に保存し、`data_generation_items` で per-source の `data-*` generation / skip reason を追える。promoted reference でも同じ manifest に rollout lane と `artifact_key` provenance が残る。
- ただし既存の `projects` / `lab_experiments` は旧 generated class と schema が一致しないため、repository driver は `pdo` を既定とする。
- page 層は repository adapter 経由にしてあり、schema が揃った後に generated driver へ差し替える。
- path より basename compatibility を優先し、少なくとも次を揃える。
  - `data-*.php`
  - `dbaccess-*.php`
  - `autoload_mtool.php`
- `autoload_mtool.php` は current contract 上の loader entry として残すが、内部実装は eager include list ではない。top-level function を持つ runtime file と `_runtime_loader.php` を preload し、それ以外の class / interface / trait / enum は generated classmap + `spl_autoload_register()` で lazy load する。
- 将来は `ProjectSourceOutput` と generator を再構築し、新 metadata から自己生成へ戻す。

## 現段階の位置づけ

- 旧実装の本機能はまだ移植していない。
- いまあるのは、2 サイト分離、DB 分離、session 分離、最小認証、health check、最小 CRUD を揃えた「再構築の足場」。
- 本体機能はこの上に module 単位で積み上げる。
