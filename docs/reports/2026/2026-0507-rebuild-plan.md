# 新規再構築 Plan

- 2026-05-08 時点の最新進捗は [2026-0508-rebuild-status.md](2026-0508-rebuild-status.md) を参照。
- 2026-05-11 時点の upstream import / sync loop の詳細計画は [2026-0511-self-host-import-loop-plan.md](2026-0511-self-host-import-loop-plan.md) を参照。

## 目的

- `original-codes/` 配下の旧実装を参照しながら、新しい実装を別系統で作り直す。
- 旧サーバ前提の実行環境に依存せず、ローカルでも Docker / Docker Compose で起動できる構成にする。
- 旧コードは原則変更せず、新コード側へ必要な知見・構造・資産を移しながら段階的に置き換える。
- Web の責務は、`設定変更用サイト` と `実験用サイト` の 2 系統に分けて再設計する。
- 旧 `dev web/db/` が持っていた設定系機能を、新実装の `admin` / `lab` に責務分離した上で再構築する。

## 前提

- 現時点では、旧実装に必要なファイルや外部依存がすべて揃っているわけではない。
- `original-codes/` は参照用の保管領域として扱い、直接の改修対象にはしない。
- 今回の文書は「再構築の方針と初期計画」を定義するものであり、まだ実装方式の詳細を確定する段階ではない。

## 進捗ステータス

2026-05-07 作業終了時点の最新状態は以下。

- Phase 1. 旧実装の棚卸し
  - 完了
- Phase 2. 新実装の土台決め
  - 完了
- Phase 3. 開発環境の先行整備
  - 完了
- Phase 4. 基盤機能の再構築
  - 進行中
- Phase 5. 旧機能の段階移植
  - 進行中
- Phase 6. 検証と整理
  - 進行中

ドキュメント計画としては、旧 `original-codes/dev web/db/` 全体の棚卸し、新実装への対応づけ、2026-05-07 終了時点の停止点反映までは完了している。

## 現在の到達点

- 旧実装の調査資料を `original-codes/docs/` に整理済み。
- 新実装はリポジトリ直下の `admin/`、`lab/`、`shared/`、`docs/` を起点にする構成で固定済み。
- Docker 構成は `ubuntu:24.04` ベース、Apache + PHP 8.4、MariaDB 2 系統で固定済み。
- `compose.yaml` で `web-admin`、`web-lab`、`db-config`、`db-lab`、任意の `maildev` を起動できる。
- `.gitignore`、`.env.example`、`Makefile` を整備済み。
  - `make env`
  - `make bootstrap-dbclasses`
  - `make up`
  - `make ps`
  - `make health`
  - `make db-config-migrate`
  - `make db-lab-migrate`
- `admin` / `lab` の 2 サイトは、`/`、`/health`、`/login`、`/dashboard`、`/logout` の最小ルートを実装済み。
- `shared/` に session、CSRF、スタブ認証、保護ルート、例外ハンドリングの骨格を追加済み。
- `db-config` に `projects` / `project_memberships`、`db-lab` に `lab_experiments` の最小スキーマと seed を追加済み。
- ただし `db-config` の初期 seed project はまだ仮置きであり、次の更新では旧 `Project 1 (Mtool)` を canonical な初期 seed の基準へ寄せる方針を採用する。
  - 旧 `Project 16 (Mtool Work)` は初期 seed へ含めない。
  - 理由は、現段階の bootstrap 対象が `mtool_lib/dbclasses` を中心とする Mtool 本体であり、`mtool_work_lib` / `proxy_auth` / `ProjectSourceOutput` はまだ新実装の初期 seed scope に含めないため。
- `admin` の `/projects`、`lab` の `/experiments` で最小データモデルを表示できる。
- `admin` の `/projects` と `lab` の `/experiments` に最小の追加フォームを実装済み。
- `admin` に `/projects/{project_key}` の Project 詳細ハブを追加済み。
- `admin` に `/projects/{project_key}/settings` の Project 基本設定画面を追加済み。
  - 現時点では `project_edit.php` のうち project identity / slug / lifecycle / description を更新可能
  - `StorageType`、`DBType`、`DBUserPID`、`TokenForProxyAccess`、`option_*` 群は未移植
- `admin` に `/projects/{project_key}/tables` の DB Table metadata 入口を追加済み。
  - 現時点では canonical `dbtable` import ではなく、generated runtime filename を逆引きした bootstrap candidate view を表示
  - `/tables/import`、`/tables/{table_key}`、`/tables/{table_key}/columns` の preview route まで利用可能
  - ただし canonical import 実行と `dbtable` / `dbtablecolumns` 保存は未実装
- `admin` に `/projects/{project_key}/data-classes` の Data Class 入口を追加済み。
  - 現時点では canonical metadata ではなく、`data-*.php` / `dbaccess-*.php` catalog を表示
  - `/data-classes/sync`、`/data-classes/{data_class_key}`、`/data-classes/{data_class_key}/fields`、`/data-classes/{data_class_key}/source` の preview route まで利用可能
  - ただし canonical save / field metadata / source cache は未実装
- `admin` に `/projects/{project_key}/db-access` の DB Access 入口を追加済み。
  - generated `dbaccess-*.php` の class / method preview を基準にしつつ、保存済み canonical metadata を併記
  - `/db-access/sync`、`/db-access/{db_access_key}`、`/db-access/{db_access_key}/edit`、`/db-access/{db_access_key}/source` の preview route まで利用可能
  - `edit` は `data-da.php` / `dbaccess-da.php` を使った legacy metadata schema draft を表示しつつ、`project_db_access_classes` へ class metadata を保存できる
  - `/db-access/{db_access_key}/functions`、`/db-access/{db_access_key}/functions/{function_key}`、`/source`、`/endpoint` の preview route まで利用可能
  - function detail / endpoint は `data-dafunc.php` を参照し、legacy ActionType や canonical field draft を併記する
  - `functions/{function_key}` は `project_db_access_functions` へ function metadata を保存でき、一覧 / endpoint に反映される
  - `/db-access/{db_access_key}/functions/change-order` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/move` が利用可能
  - action type と endpoint method は、saved row が無い場合は method 名ベースの heuristic 表示になる
  - `select-where`、`select-target-fields`、`select-having`、`insert target fields`、`update target fields`、`update-delete where` designer を追加済み。
  - `/db-access/{db_access_key}/functions/{function_key}/select-where`、`/select-where/new`、`/select-where/{select_where_key}` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/select-where/input-aid` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/select-where/change-order` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/select-target-fields`、`/select-target-fields/new`、`/select-target-fields/{select_target_field_key}` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/select-having`、`/select-having/new`、`/select-having/{select_having_key}` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/insert-target-fields`、`/insert-target-fields/new`、`/insert-target-fields/{insert_target_field_key}` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/update-target-fields`、`/update-target-fields/new`、`/update-target-fields/{update_target_field_key}` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/update-delete-where`、`/update-delete-where/new`、`/update-delete-where/{update_delete_where_key}` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/update-delete-where/input-aid` が利用可能
  - `/db-access/{db_access_key}/functions/{function_key}/update-delete-where/change-order` が利用可能
  - `project_db_access_function_select_wheres`、`project_db_access_function_select_target_fields`、`project_db_access_function_select_havings`、`project_db_access_function_insert_target_fields`、`project_db_access_function_update_target_fields`、`project_db_access_function_update_delete_wheres` へ list/create/update/delete を行える
  - `select-where` と `update-delete where` では `change-order` 画面を追加し、一括順序更新と `RESET` による 1..n 再採番を行える
  - `select-where` でも generated property 候補ベースの `input-aid` を実装し、候補一覧から add/edit 導線を辿れる
  - `update-delete where` では generated property 候補ベースの `input-aid` を先行実装し、候補一覧から add/edit 導線を辿れる
  - `GetProject` / `InsertProject` / `UpdateProject` / `DeleteProject` を使った往復確認まで完了
- session principal の `roles` を使った最小 role 認可を実装済み。
- 旧 `original-codes/dev web/db/` 配下 152 PHP の新実装対応表を `docs/internal/mtool-admin-roadmap.md` に整理済み。
- 旧 `original-codes/dev web/db/` 配下の非 PHP 資産も棚卸し済み。
  - `compare_output_template_*.txt`
  - `compare_ignore_dir_setting_regex.txt`
  - `old/2017-10.zip`
- 旧 `original-codes/mtool_lib/dbclasses/` を、新実装でも「自己生成して自己利用する runtime artifact」として扱う方針を固定済み。
  - 現時点の bootstrap は旧生成物コピーを許容
  - file basename は旧 `dbclasses` とおおむね揃える
  - bootstrap copy の配置先は `generated/mtool/dbclasses/`
  - 再投入コマンドは `make bootstrap-dbclasses`
  - DB 設計データ Export 投入後に、新実装 metadata から再生成する
- `projects` / `lab_experiments` の page 層は repository adapter 経由へ切り替え済み。
  - 既定 driver は `pdo`
  - `legacy-dbclasses-bootstrap` は schema 整合後に差し替えるための placeholder
  - そのため、既に作成済みの `/projects`、`/projects/{project_key}`、`/experiments` を直ちに全面書き換えする必要はない
- `db_access_repository.php` / `db_access_repository_pdo.php` を追加済み。
  - `project_db_access_classes`
  - `project_db_access_functions`
  - `function_list_order` を追加し、保存済み canonical function row の並び順を保持
  - `project_db_access_function_select_wheres`
  - `project_db_access_function_select_target_fields`
  - `project_db_access_function_select_havings`
  - `project_db_access_function_insert_target_fields`
  - `project_db_access_function_update_target_fields`
  - `project_db_access_function_update_delete_wheres`
  - class/function metadata の fetch catalog / single fetch / upsert と query designer CRUD を実装
- `admin:/projects/{project_key}` では generated runtime の bootstrap 状態を確認できる。
  - mode
  - loader path と existence
  - file count
  - repository driver
- ただし、旧 `dev web/db/` にあった `dbtables` は preview route までであり、canonical import / edit / save は未実装である。
- `dataclasses` は preview route までであり、canonical save / sync 実行は未実装である。
- `da` / `da_func` は class save / function save と `function change-order` / `function move` / `select-where` / `select-target-fields` / `select-having` / `insert target fields` / `update target fields` / `update-delete where` に加え、`select-where & update-delete where input-aid` / `select-where & update-delete where change-order` までは実装済みだが、sync と他の query designer sub-resource は未実装である。
- `proxy`、`html`、`lang_res`、`build`、`project_security` は未実装である。
- `project_source_output` は最小の definition / artifact 生成まで実装済みである。
- `compare_output` は admin 側 definition 管理と lab 側 local compare output file 生成まで実装済みである。
- 恒久ドキュメントとして以下を追加済み。
  - `docs/internal/site-boundaries.md`
  - `docs/internal/runtime-architecture.md`
  - `docs/internal/auth-architecture.md`
  - `docs/internal/data-model.md`
  - `docs/internal/mtool-admin-roadmap.md`
  - `docs/internal/generated-code-strategy.md`
- 履歴 plan と恒久ドキュメントへ、2026-05-07 終了時点のステータスを反映済み。
- `docker compose up -d` 後の基本疎通確認は完了済み。
  - `admin` / `lab` ともに `/` と `/health` が 200
  - 未認証 `/dashboard` は `/login` へ 302
  - ログイン後 `/dashboard` は 200
  - ログアウト後は再び `/login` へ 302
  - `admin` の `/projects` は 200
  - `admin` の `/projects/{project_key}/settings` は 200
  - `admin` の `/projects/{project_key}/tables` は 200
  - `admin` の `/projects/{project_key}/tables/import` は 200
  - `admin` の `/projects/{project_key}/tables/{table_key}` は 200
  - `admin` の `/projects/{project_key}/tables/{table_key}/columns` は 200
  - `admin` の `/projects/{project_key}/data-classes` は 200
  - `admin` の `/projects/{project_key}/data-classes/sync` は 200
  - `admin` の `/projects/{project_key}/data-classes/{data_class_key}` は 200
  - `admin` の `/projects/{project_key}/data-classes/{data_class_key}/fields` は 200
  - `admin` の `/projects/{project_key}/data-classes/{data_class_key}/source` は 200
  - `admin` の `/projects/{project_key}/db-access` は 200
  - `admin` の `/projects/{project_key}/db-access/sync` は 200
  - `admin` の `/projects/{project_key}/db-access/{db_access_key}` は 200
  - `admin` の `/projects/{project_key}/db-access/{db_access_key}/edit` は 200
  - `admin` の `/projects/{project_key}/db-access/{db_access_key}/source` は 200
  - `admin` の `/projects/{project_key}/db-access/{db_access_key}/functions` は 200
  - `admin` の `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}` は 200
  - `admin` の `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/source` は 200
  - `admin` の `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/endpoint` は 200
  - `lab` の `/experiments` は 200
  - 逆サイトの route は 403
  - `Project` / `Experiment` の create POST は 302 で完了
  - `Project` の settings update POST は 302 で完了
  - `Experiment` の edit GET は 200 で完了
  - `Experiment` の update POST は 302 で完了
- 2026-05-07 の追加確認も完了済み。
  - 変更後の PHP 構文チェックは対象ファイルすべて `No syntax errors`
  - `docker compose ps` で `web-admin`、`web-lab`、`db-config`、`db-lab` は healthy
  - `web-admin` 内から `/var/www/generated/mtool/dbclasses/autoload_mtool.php` の存在を確認
  - generated runtime summary は `legacy-copy-bootstrap`、`204 total / 101 data / 101 dbaccess`、repository driver=`pdo`
  - ログイン後の `admin:/projects/MTOOL-110739` は 200
  - `003_db_access_metadata.sql` を既存 `db-config` volume に手動適用済み
  - `004_db_access_select_where_metadata.sql` を既存 `db-config` volume に手動適用済み
  - `005_db_access_select_target_field_metadata.sql` を既存 `db-config` volume に手動適用済み
  - `006_db_access_select_having_metadata.sql` を既存 `db-config` volume に手動適用済み
  - `007_db_access_update_delete_where_metadata.sql` を既存 `db-config` volume に手動適用済み
  - `008_db_access_insert_target_field_metadata.sql` を既存 `db-config` volume に手動適用済み
  - `009_db_access_update_target_field_metadata.sql` を既存 `db-config` volume に手動適用済み
  - `admin:/projects/MTOOL-110739/db-access/Project/edit` の POST で class metadata 保存を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject` の POST で function metadata 保存を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/InsertProject` の canonical function metadata 保存を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject` の canonical function metadata 保存を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/DeleteProject` の canonical function metadata 保存を確認
  - `/db-access`、`/db-access/{db_access_key}`、`/functions`、`/endpoint` で canonical state の反映を確認
  - `010_db_access_function_order_metadata.sql` を既存 `db-config` volume に手動適用済み
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/change-order` は 200
  - `function change-order` の save / reset POST は 302 で完了
  - `admin:/projects/MTOOL-110739/db-access/SpecialHoliday/functions/GetAllList/move` は 200
  - `admin:/projects/MTOOL-110739/db-access/ApacheHostSetting/functions/GetAllList/move` は 200
  - `function move` の POST は `SpecialHoliday -> ApacheHostSetting -> SpecialHoliday` の往復で 302 を確認
  - move の往復後も `GetAllList` の function id は同一のままで、`select_where_count=1` の child row が追従することを確認
  - move 検証用に作成した canonical row と child row は cleanup 済み
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-where` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-where/new` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-where/input-aid` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-where/change-order` は 200
  - `select-where` の create / update / delete POST は 302 で完了
  - `select-where change-order` の save / reset POST は 302 で完了
  - `select-where input-aid` から `new` への query prefill が反映されることを確認
  - 検証用 row 作成後に削除し、`project_db_access_function_select_wheres` は 0 row を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-target-fields` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-target-fields/new` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-target-fields/{id}` は 200
  - `select-target-fields` の repository create / update / delete は CLI で完了
  - 検証用 row 作成後に削除し、`project_db_access_function_select_target_fields` は 0 row を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-having` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-having/new` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/GetProject/select-having/{id}` は 200
  - `select-having` の repository create / update / delete は CLI で完了
  - 検証用 row 作成後に削除し、`project_db_access_function_select_havings` は 0 row を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/InsertProject/insert-target-fields` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/InsertProject/insert-target-fields/new` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/InsertProject/insert-target-fields/{id}` は 200
  - `insert target fields` の repository create / update / delete は CLI で完了
  - 検証用 row 作成後に削除し、`project_db_access_function_insert_target_fields` は 0 row を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject/update-target-fields` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject/update-target-fields/new` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject/update-target-fields/{id}` は 200
  - `update target fields` の repository create / update / delete は CLI で完了
  - 検証用 row 作成後に削除し、`project_db_access_function_update_target_fields` は 0 row を確認
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject/update-delete-where` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject/update-delete-where/new` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject/update-delete-where/input-aid` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject/update-delete-where/change-order` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/UpdateProject/update-delete-where/{id}` は 200
  - `admin:/projects/MTOOL-110739/db-access/Project/functions/DeleteProject/update-delete-where` は 200
  - `update-delete where` の repository create / update / delete は CLI で完了
  - `update-delete where change-order` の save / reset POST は 302 で完了
  - `update-delete where input-aid` から `new` への query prefill が反映されることを確認
  - 検証用 row 作成後に削除し、`project_db_access_function_update_delete_wheres` は 0 row を確認

## `original-codes/dev web/db/` 棚卸し結果

- 再帰的に見た対象総数は 160 ファイル。
- 実際の再構築対象は、`old/2017-10.zip` を除く 159 ファイル。
- 内訳は以下。
  - PHP 152
  - 補助テンプレート / 補助設定テキスト 7
  - 参考アーカイブ 1
- モジュール別の件数は以下。
  - Top / Project 基本設定 8
  - DB Table metadata 9
  - Data Class 12
  - DB Access Class 6
  - DB Access Function / Query 設計 50
  - Proxy 設定 12
  - HTML 6
  - Language Resource 13
  - Source Output 7
  - Project Security / Host Assignment 10
  - Build / Compare Output 22
  - Endpoint Test / 実行補助 4
  - Archive 1
- 上記 159 ファイルの受け皿は `docs/internal/mtool-admin-roadmap.md` に定義済み。
- したがって、現時点の未完了は「調査」ではなく「新実装への具体的な route / schema / UI 反映」である。

## スコープ再定義

今回の再確認により、新実装の対象は「最小 Project / Experiment 管理」ではなく、旧 `dev web/db/` が担っていた Mtool の設定系一式であることを明確化した。

- 基本対象は `original-codes/dev web/db/` 配下の active files 一式とする。
- 実装計画上の active scope は、`old/2017-10.zip` を除く 159 ファイルとする。
- うち PHP は 152、補助テンプレート / 補助設定は 7。
- テンプレート画面、`*_include.php`、`*_ajax.php`、ダウンロード endpoint、補助 include を含めて新実装側の route / service / job に対応づける。
- `compare_output_template_*.txt` と `compare_ignore_dir_setting_regex.txt` は route ではなく、template asset / compare rule asset として受ける。
- 旧 `mtool_lib/dbclasses/` は、新実装でも runtime dependency として使う前提で扱う。
- ただし初期段階では「新 DB 設計からの完全再生成」ではなく、「旧生成物コピーによる bootstrap」を許容する。
- filename は少なくとも以下の系統を旧実装と揃える。
  - `data-*.php`
  - `dbaccess-*.php`
  - `autoload_mtool.php`
- directory path は後で調整可能だが、basename compatibility を先に固定する。
- DB 設計データの Export 未投入は、現時点の既知制約として扱う。

- `project_edit.php`
  - Project 基本設定、DB 種別、接続先、Storage、Proxy token、各種 option
- `dbtables*.php`
  - DB テーブル一覧、編集、外部 DB からの import
- `dataclasses*.php`
  - Data Class と field の同期、編集、ソース確認
- `da*.php`, `da_func*.php`
  - DB Access Class と query / function 設計
- `da_edit_proxy_single_target.php`, `da_proxy_custom*.php`
  - Proxy 対象と Custom Proxy 設定
- `html*.php`, `lang_res*.php`
  - HTML / Language Resource 設定
- `project_source_output*.php`
  - Source Output 設定
- `build_project*.php`, `compare_output*.php`
  - ビルド、差分比較、補助的な実行系機能
- `project_security*.php`
  - ProjectUser とページ単位権限

この対応関係と新 route の候補は `docs/internal/mtool-admin-roadmap.md` に恒久化する。

## 基本方針

### 1. 旧コードは触らない

- `original-codes/` は読み取り専用のリファレンスとして扱う。
- 調査結果、仕様抽出、データモデル把握、画面・機能の棚卸しに使う。
- 修正が必要な場合でも、原則として新実装側で吸収する。

### 2. 新コードはトップレベルで管理する

- 新実装は `original-codes/` の外側に、リポジトリ直下で管理する。
- 旧コードの上書きや混在を避け、比較・切り戻し・棚卸しをしやすくする。
- 必要なファイルは新実装側へコピーまたは再生成して取り込む。

### 3. ローカル起動は Docker / Docker Compose 前提にする

- 既存サーバの絶対パスや既存ミドルウェア設定に依存しない。
- リポジトリを clone すれば、ローカルで同じ手順で起動できる状態を目指す。
- アプリ、Web、DB、必要に応じて補助サービスを Compose でまとめて起動できるようにする。

### 4. Web は 2 サイト構成にする

- `設定変更用サイト`
  - 設定管理、設計編集、権限管理、公開操作を担う。
- `実験用サイト`
  - 実行確認、試験、比較、実験的な処理を担う。
- 画面を分けるだけでなく、役割と更新対象データも分ける。
- ただし、リポジトリや共通コードは二重化せず、同一プロジェクト内で管理する。

### 5. 生成コードは self-hosting 前提にする

- 新実装も、ツール自身が使う runtime code の一部を自分で生成する前提で設計する。
- 旧 `original-codes/mtool_lib/dbclasses/` は、その典型例として扱う。
- 当面は既存生成物をコピーして runtime へ取り込み、後で新しい metadata export を投入した段階で生成系を差し替える。
- 完全互換よりも、まず basename compatibility を優先する。
- 一部テンプレート編集領域のカスタム保持があるため、将来の generator でも「完全上書きのみ」には寄せない。

## 目標状態

- 新実装は `docker compose up` 系の単純な操作で起動できる。
- ローカルに専用サーバ設定がなくても、開発・動作確認・初期セットアップができる。
- 旧実装で把握した主要機能を、新実装側で段階的に再構築できる。
- ディレクトリ構成、設定ファイル、環境変数、起動方法が整理されている。
- `設定変更用サイト` と `実験用サイト` を同時に起動できる。
- 設定の正本と実験用データの境界が明確になっている。

## 想定する構成方針

現時点では詳細未確定だが、少なくとも次のような構成を目標にする。

- `original-codes/`
  - 旧実装の参照用保管領域
- `admin/`
  - 設定変更用サイト
- `lab/`
  - 実験用サイト
- `shared/`
  - 共通ドメイン、共通ライブラリ、共通 UI 資産
- `generated/`
  - bootstrap copy した generated runtime artifact
- `docs/`
  - 新実装の設計書、構成書、移行メモ
- `scripts/`
  - bootstrap / 補助 script
- `docker/`
  - Dockerfile、初期化スクリプト、補助設定
- `compose.yaml` または `docker-compose.yml`
  - ローカル開発環境の統合起動定義

## サイト分離方針

### 設定変更用サイト

- 主用途は control plane とする。
- 設定、設計情報、ビルド定義、権限、公開対象の管理を行う。
- 正本となる設定データを扱う。

### 実験用サイト

- 主用途は runtime / lab とする。
- 実行確認、試験、差分比較、実験機能の検証を行う。
- 一時データや実験用データを扱う。

### データ境界

- `設定変更用サイト` が設定の正本を更新する。
- `実験用サイト` は、原則として設定の正本を直接更新しない。
- 可能であれば `設定変更用サイト -> publish -> 実験用サイト` の流れを採用する。
- 共有が必要な場合も、参照系と更新系の責務は分ける。

## Docker / Docker Compose 方針

### 最低限そろえるもの

- ルーティング用または受け口用コンテナ
- `設定変更用サイト` の Web コンテナ
- `実験用サイト` の Web コンテナ
- 設定系 DB コンテナ
- 実験系 DB コンテナ

### 必要に応じて追加するもの

- バッチ実行用コンテナ
- Mail 捕捉用の開発補助コンテナ
- キャッシュやキューなどの補助ミドルウェア

### 運用方針

- ローカル用設定は環境変数または `.env` 系で切り替える。
- 初期セットアップは Compose 起動後に自動または少数コマンドで完了する形を目指す。
- 開発者ごとの差異が出やすいローカルサーバ設定は、できるだけ Compose 側へ寄せる。
- サイトごとの URL、DB、volume、環境変数は明示的に分ける。
- 実験用サイトは壊して再作成しやすい構成を優先する。

## 想定する Compose 上の役割

- `reverse-proxy`
  - ローカルの入口
- `web-admin`
  - 設定変更用サイト
- `web-lab`
  - 実験用サイト
- `db-config`
  - 設定の正本を保持する DB
- `db-lab`
  - 実験・試験用 DB
- `generated volume`
  - `generated/mtool/dbclasses/` を runtime 入力として各 Web から参照しつつ、artifact / compare output の生成先として Web から書き込めるようにする

初期ブートストラップでは、まず `web-admin` / `web-lab` / `db-config` / `db-lab` を優先し、必要になった時点で `reverse-proxy` を追加する。

必要に応じて以下も追加する。

- `worker`
  - バッチやビルド処理
- `maildev`
  - メール確認
- `storage-mock`
  - 外部依存をローカルで代替するための補助

## 再構築の進め方

### Phase 1. 旧実装の棚卸し

- ステータス
  - 完了

- 既存 docs を基に、旧実装の主要機能を整理する。
- 優先度の高いモジュールを抽出する。
- 外部依存と未入手ファイルを一覧化する。

### Phase 2. 新実装の土台決め

- ステータス
  - 完了

- 新実装の技術スタックを決める。
- ディレクトリ構成と命名規約を決める。
- Docker / Docker Compose の基本構成を決める。
- `設定変更用サイト` と `実験用サイト` の責務境界を決める。

### Phase 3. 開発環境の先行整備

- ステータス
  - 完了

- 新実装用のトップレベル構成を整える。
- Dockerfile と Compose 定義を作る。
- 最小構成でアプリ起動確認ができる状態にする。
- `admin` と `lab` の 2 サイトをローカルで同時に立ち上げられるようにする。
- `.gitignore`、`.env.example`、`Makefile` を整える。
- `make bootstrap-dbclasses` / `make db-config-migrate` / `make db-lab-migrate` を整える。

### Phase 4. 基盤機能の再構築

- ステータス
  - 進行中
- 現在までに完了したもの
  - DB 接続確認
  - session 分離
  - スタブ認証
  - CSRF
  - 保護ルート
  - 共通 HTTP 入口と例外ハンドリングの骨格
  - router / middleware / response / request の共通化
  - repository adapter の `pdo` driver 化
  - `Project` の最小スキーマ
  - `lab_experiments` の最小スキーマ
  - seed による初期データ投入
  - `admin` / `lab` の責務差を出す一覧画面
  - `Project` / `Experiment` の最小 create UI
  - `Project` / `Experiment` の最小 update UI
  - session role による最小認可
  - `Project` 詳細ハブから settings / tables / data-classes / db-access へ進む基本導線
  - generated runtime bootstrap 状態の可視化
- まだ残っているもの
  - 差分 migration / backfill の運用固定化
  - `ProjectMembership` と read / write を分ける認可層
  - 未移植モジュールへ伸ばす Project 導線
  - 設定管理 UI の membership / publish 系土台
  - 公開フローまたは同期フローの設計
  - 中央認証連携を見据えた認証方式の再設計
  - `ProjectUser` 相当の本格的な role / permission 設計

- 設定管理
- 認証方式の再設計
- DB 接続
- 差分 migration / backfill 運用
- 共通レイアウトや API 基盤
- 設定系データと実験系データの分離
- 公開フローまたは同期フローの設計

### Phase 5. 旧機能の段階移植

- ステータス
  - 進行中
- 現在までに完了したもの
  - `admin:/projects/{project_key}` の Project 詳細ハブ
  - `admin:/projects/{project_key}/settings` の Project 基本設定
  - `admin:/projects/{project_key}/tables*` の bootstrap preview route 群
  - `admin:/projects/{project_key}/data-classes*` の bootstrap preview route 群
  - `admin:/projects/{project_key}/db-access*` の bootstrap preview route 群
  - DB Access class metadata の save / source preview
  - DB Access function metadata の save / source preview / endpoint preview
  - function change-order
  - function move
  - select-where list / edit / input-aid / change-order
  - select-target-fields list / edit
  - select-having list / edit
  - insert target fields list / edit
  - update target fields list / edit
  - update-delete where list / edit / input-aid / change-order
- まだ残っているもの
  - `project_edit.php` 由来の DB / Proxy / option 群
  - `dbtables` の canonical import / save
  - `dataclasses` の canonical save / field metadata / sync
  - `da` の class designer 拡張と `db-access/sync`
  - `da_func` の残り sub-resource と sync job
  - `project_source_output` の strategy 拡張
  - `project_security`
  - `build`
  - `compare_output` の job 履歴 / review / template asset 管理
  - `html` / `lang_res` / `proxy`

- 旧 `dev web/db/` を、新実装では少なくとも次のモジュール単位で再構築する。
  - `Project` 基本設定
  - DB Table import / metadata
  - Data Class
  - DB Access Class
  - DB Access Function / query designer
  - Proxy target / custom proxy
  - HTML / Language Resource
  - Source Output
  - Build / Compare Output
  - ProjectUser / page security
- 旧コードを直接流用する場合も、新実装側へコピーしたものだけを編集対象にする。
- 旧 `PID` ベース query string はそのまま持ち込まず、新実装では route と stable key に置き換える。
- 画面だけでなく、補助 include、AJAX endpoint、テンプレート資産も module 単位で受け皿を作る。

### Phase 6. 検証と整理

- ステータス
  - 進行中
- 現在までに完了したもの
  - 起動手順の初版整理
  - `.env.example` / `README` / `Makefile` の初版整理
  - Compose 起動確認
  - PHP 構文確認
  - ランタイム / 認証ドキュメント整備
  - データモデル文書整備
  - DB 初期化 SQL の Compose 組み込み
  - 既存 volume 向け migration 手順の初版整理
  - create / update の HTTP 疎通確認
  - function change-order の動作確認
  - function move の往復確認と cleanup
  - query designer CRUD / input-aid / change-order の動作確認
- まだ残っているもの
  - 自動テスト整備
  - 既存 volume 向け migration の自動検証固定化
  - 残モジュールを含む E2E 検証
  - 不要依存の棚卸し
  - 運用手順の固定化

- ローカル起動手順の確立
- テスト整備
- 不要な依存の削減
- ドキュメント整備

## 移行時のルール

- 旧コードは参照専用とし、改修しない。
- 新実装で使うコードは、新実装側へコピーしたものだけを修正する。
- どの旧機能をどの新機能へ置き換えるか、対応表を残す。
- 外部依存が強い箇所は、モックや代替実装でローカル起動可能性を優先する。
- `設定変更用サイト` と `実験用サイト` の責務を混在させない。
- 実験用サイトから設定の正本を直接編集しない設計を優先する。

## 主要リスク

- 旧実装に不足ファイルがあり、完全再現ができない可能性がある。
- 認証、ストレージ、チャット、外部連携は旧サーバ依存が強い。
- 旧実装は絶対パスや複数サービス共存を前提にしており、そのままでは Compose 化しにくい。
- DB 設計データ Export がまだ入っていないため、`dbclasses` 相当の再生成ロジックを直ちに再現できない。
- すべてを一括移行しようとすると、初期構築が重くなりやすい。
- 2 サイトに分けても、裏側の DB や状態管理を分けないと責務分離が崩れる。
- サイトだけ分離してコードや設定が二重化すると、保守コストが増える。

## リスク対応の考え方

- まずは「ローカルで起動できる最小新実装」を優先する。
- 外部連携は後回しにし、境界を切って差し替えやすくする。
- 旧機能の完全再現より、再構築しやすい構造を優先する。
- 2 サイト構成でも、共通コードは共有し、責務だけを分離する。
- 生成 runtime code は、最初はコピー bootstrap を許容し、その後 metadata export 投入後に自己生成へ戻す。
- file basename を旧実装に寄せ、path や loader だけを段階的に差し替える。

## 明日再開時の優先

2026-05-07 の停止点からは、次の順で再開する。

1. `db-config` の初期 seed を見直し、旧 `Project 1 (Mtool)` を基準にした canonical project seed へ差し替える。
   - 旧 `Project 16 (Mtool Work)` はこの段階では seed に含めず、`mtool_work` 相当の再設計タスクとして後続へ分離する。
2. `dbtables` の canonical import / table detail / column detail を実装し、bootstrap preview から metadata 管理へ進める。
3. `dataclasses` の canonical detail / fields / sync を実装し、source preview から metadata 管理へ進める。
4. `da` と `da_func` の残りの query designer と sync を実装し、未移植 sub-resource へ広げる。
5. `project_edit.php` 由来の DB / Proxy / option 群を `admin:/projects/{project_key}/settings` 配下へ段階的に戻す。
6. DB 設計データ Export 投入後に、`dbclasses` 相当の generated runtime layer を新 metadata から再生成できるようにし、repository driver を `pdo` から段階的に置き換える。

## 2026-05-11 追記: upstream import / sync loop の優先

- 2026-05-11 時点で確認したところ、Mtool の self-host loop は downstream half のみ動いている。
  - `project_db_access_classes` / `project_db_access_functions` と `RUNTIME-DBCLASSES` は動作している。
  - 一方で `tables/import` と `data-classes/sync` は preview only のままであり、`config_app` に `dbtable` / `dbtablecolumns` / `dataclass` / `dataclassfields` はまだ存在しない。
- したがって、現在の最優先は `DB 構造 -> import -> Data Class` の upstream ループを実装することである。
- first slice の実装順は次の通り。
  1. `dbtable` / `dbtablecolumns` / `dataclass` / `dataclassfields` の canonical schema を追加する。
  2. `tables/import` の preview / apply を実装する。
  3. `data-classes/sync` の preview / apply を実装する。
  4. MTOOL 自身の DB 設計を import し、`dataclass` / `dataclassfields` まで同期する。
  5. その canonical slice を `RUNTIME-DBCLASSES` の data-side supplement 置き換えに接続する。
- 詳細は [2026-0511-self-host-import-loop-plan.md](2026-0511-self-host-import-loop-plan.md) に分離した。

## 今回ここで固定したこと

- 新実装は `new-system/` を作らず、リポジトリ直下の `admin/`、`lab/`、`shared/`、`docs/` で管理する。
- 初期 Compose 構成は次を起点にする。
  - `web-admin`
  - `web-lab`
  - `db-config`
  - `db-lab`
- `reverse-proxy` は将来追加候補とし、最初の起動確認は 2 ポート構成で進める。
- Web コンテナは `ubuntu:24.04` ベース、Apache + PHP 8.4 で構成する。
- `admin` と `lab` は別 session 名、別認証情報を持てるようにする。
- 現段階の認証は本番連携ではなく、ローカル Docker 用のスタブ認証を採用する。
- 最小データモデルは `db-config` の canonical project と `db-lab` の experiment に分ける。
- `admin:/projects` と `lab:/experiments` を、最初の責務差が見える保護ルートとして固定する。
- `admin:/projects/{project_key}` を、旧 `index.php` 相当の Project 詳細ハブとして追加する。
- `admin:/projects/{project_key}/settings` を、旧 `project_edit.php` 相当の canonical settings route として追加する。
- `db-config` の canonical initial project seed は、旧 `Project 1 (Mtool)` を基準に再構成する。
- compare output 実行時は `lab` が `db-config` の canonical definition を read-only 参照し、生成ファイルは `generated/` 配下へ出力する。
- 旧 `Project 16 (Mtool Work)` は初期 seed に含めず、`mtool_work` / `proxy_auth` / support runtime を再設計する段階で別タスクとして扱う。
- 旧 `dev web/db/` の設定系は `admin` に寄せ、Build / Compare / Endpoint Test などの実行系は `lab` に寄せる前提で再設計する。
- 旧 `mtool_lib/dbclasses/` は、新実装でも自己生成・自己利用する runtime artifact とみなし、当面はコピー bootstrap を許容する。
- `data-*.php`、`dbaccess-*.php`、`autoload_mtool.php` の basename compatibility を優先する。
- 既存の page 層は repository adapter 経由へ寄せ、schema と export が揃うまでは `pdo` driver を使い続ける。

## 今回の結論

- 旧実装は `original-codes/` に固定し、触らない。
- 新実装はリポジトリ直下で再構築する。
- ローカル実行環境は Docker / Docker Compose を前提にする。
- Web は `設定変更用サイト` と `実験用サイト` の 2 系統に分ける。
- 新実装の土台づくりと最小 CRUD は揃いつつあるが、目標は旧 Mtool の設定系一式の再構築であり、次は Project 詳細、DB metadata、DA/query 設定、Source Output、Build 系まで含めた本体機能移植へ進む段階に入っている。
