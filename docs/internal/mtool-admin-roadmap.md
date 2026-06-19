# Mtool Admin Roadmap / Mtool Admin 再構築ロードマップ

English companion:
This roadmap maps old admin functionality to the new implementation. It covers pages, AJAX endpoints, helper includes, route placement, migration phases, and the gates for raising the new admin and lab routes into the primary line.

## 目的

- 旧 `original-codes/dev web/db/` 配下の機能を、新実装でどこに受けるかを明文化する。
- 画面だけでなく、`*_include.php`、`*_ajax.php`、ダウンロード endpoint、補助 include まで含めて対応づける。
- 実装順がぶれないように、旧ファイル群と新 route / service / job の関係を固定する。

## 対象範囲

- 基本対象は `original-codes/dev web/db/` 配下の active files 一式。
- 再帰的な総ファイル数は 160。
- active scope は `old/2017-10.zip` を除く 159 ファイル。
- 内訳は PHP 152、補助テンプレート / 補助設定テキスト 7。
- 旧 `PID` クエリ文字列はそのまま引き継がず、新実装では path parameter と stable key に置き換える。
- 旧テンプレート断片と `*_include.php` の分離は維持せず、新実装では route handler / page / service / job に整理する。

## 棚卸しサマリ

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

補足:

- `Build / Compare Output 22` には、`compare_output_template_*.txt` と `compare_ignore_dir_setting_regex.txt` を含む。
- `Archive 1` は `old/2017-10.zip` であり、再構築対象ではなく履歴参照用とする。
- ここでいう対象は「画面」だけではなく、`*_include.php`、`*_ajax.php`、補助 `lib`、実行テンプレート資産まで含む。

## サイト配置

### `admin` に置くもの

- canonical な設定を更新する UI
- metadata import / sync を起こす操作
- `Project`
- DB Table
- Data Class
- DB Access Class
- DB Access Function / query 設計
- Proxy target / Custom Proxy
- HTML
- Language Resource
- Source Output
- ProjectUser / page security
- Compare Output の設定
- 既定設定、履歴表示

### `lab` に置くもの

- Build 実行
- Compare Output 実行
- Endpoint Test のような実行系 API
- canonical 設定を読み込んで検証する run / experiment 画面
- 実行ログ、進捗、比較結果

## 移植完了の定義

「旧 Mtool の設定機能を新実装へ移植完了した」とみなす条件は、単に画面が存在することではなく、以下を満たすこととする。

| 観点 | 完了とみなす条件 | 補足 |
| --- | --- | --- |
| 設定画面 parity | 旧 `original-codes/dev web/db/` と関連設定画面で行えていた設定操作を、新 `admin` / `lab` の current route だけで実行できる。 | create / update / delete / reorder / assignment / preview まで含む。 |
| current source of truth | 設定値の正本が current canonical schema / file にあり、通常運用で旧画面、旧 DB row、legacy wrapper fallback を必要としない。 | 旧実装は reference / comparison / migration source としてのみ残す。 |
| Output parity | 新設定画面で保持した current metadata から生成した Output が、旧実装 Output と一致する。 | 一致は byte-level が理想だが、意図的 redesign がある場合は semantic parity を文書化する。 |
| 再現性 | 同じ current metadata から同じ Output を再生成できる。 | `work/` 依存の一時生成物や手動コピー前提にしない。 |
| 旧画面不要化 | 日常の設定変更、build、比較、検証を current route だけで回せる。 | 旧画面を見に行くのは調査・履歴参照・差分確認だけにする。 |

## フェーズ分割

この再構築は、完了までを 1 段で見ず、次の 2 フェーズに分けて扱う。

| フェーズ | 目的 | 完了条件 | この段階でまだ残っていてよいもの |
| --- | --- | --- | --- |
| Phase 1. 機能移植完了 | 旧 Mtool の設定機能を、新実装の current route / canonical metadata で一通り置き換える。 | 上の「移植完了の定義」を満たす。 | generated runtime が authoritative source ではないこと、legacy recovery copy / partial self-generation / legacy delegate が runtime 内部に残ること。 |
| Phase 2. self-host / runtime 置換完了 | Mtool 自身が出力した Output を Runtime 本体へそのまま置き換え、再編集なしで継続運用できる状態にする。 | generated Output を Runtime へ差し替えても current app がそのまま起動し、設定変更、再出力、再比較、再検証まで回る。 | PSR-4 や directory 最終整理のような cosmetic 整形だけ。 |

### Step 管理表

#### 2026-06-19 時点の読み替え

この表の `%` と「2026-05-15 時点」の記述は、5 月時点の broad rewrite / admin migration snapshot として読む。現在の active ordering は [2026-0619-plan-inventory.md](../reports/2026/2026-0619-plan-inventory.md) を正本にし、`sample01-26` tutorial lane、Mtool-side SQLite config store、user DB dialect first stop-line の完了状況を反映して判断する。

この roadmap 自体は、旧 admin 機能を new admin / lab route へどう対応づけるかの living map として維持する。ただし、次の実装順を決める active parent plan ではない。今後ここを更新する場合は、古い進捗率を細かく再見積もりするより、current route が daily operation の source of truth になっているか、残っている legacy fallback がどの ownership に属するかを確認する。

| Step | 意味 | 現在の目安 | 主な根拠 | 完了までの主な残り |
| --- | --- | --- | --- | --- |
| Step 1 / Phase 1 | 旧 Mtool の設定機能を current route / canonical metadata / file workflow へ移し、日常運用を current 側で回せる状態にする。 | 約 `80-82%` | `Project 1 = MTOOL` の `36/36 success`、admin/lab 主要 route、HTML canonical 化、LanguageResource の file-based source of truth 固定。 | page security の route policy 連携、host assignment の infra split、HTML/runtime reference dependency 圧縮、sample buildable output の選別。 |
| Step 2 / Phase 2 | Mtool 自身が出力した Output を Runtime 本体へ差し替え、generated runtime を authoritative source にする。 | 約 `70-75%` | self-generated artifact 入力と promoted default reference 入力の full self-loop green、default mode の self-generated reference 自動判定、全 `data-*` の wrapper/base 化、runtime から `original-codes/` 排除。 | `bounded full replacement` と dbclass/runtime output zero-copy goal の境界整理。`bootstrap_dbclasses.sh` は archive 済みで、`mtool` 実処理コードの historical copy はこの goal の対象外。`file/blob` contract や provenance rename は current live scope では optional track として別扱いにする。 |

補足:

- Step 1 の `%` は broad scope の主系進捗として読む。
- Step 2 の `%` は self-host loop と runtime self-generation の到達度を見た概算であり、運用は `simple form direct replacement` と `complex/new form の sample gate` の 2 段で読む。
- Step 2 の `full replacement` は historical contract の literal `100%` を意味しない。current planning では、`ApacheHostSetting*` のような明示除外、live row が無い `file/blob` contract、provenance rename のような別 migration task を除いた `bounded full replacement` を完了境界とする。
- Step 2 の運用では、`make test` / `make mtool-self-loop-check` で new artifact を出す verification run と、`make promote-runtime-reference` で default runtime reference を進める promote candidate run を分ける。`status=stale-reference` は verification-only run でも起こりうるため、latest artifact の採用判断を別に持つ。
- broad scope の最新読みは [2026-0515-progress-snapshot.md](<repo-root>/docs/reports/2026/2026-0515-progress-snapshot.md)、runtime 置換運用の最新読みは [2026-0520-runtime-replacement-two-stage-rollout.md](<repo-root>/docs/reports/2026/2026-0520-runtime-replacement-two-stage-rollout.md) を参照する。

### フェーズ間の考え方

- まずは Phase 1 を優先し、機能を current 側で綺麗に揃える。
- Phase 1 の途中では、self-host 都合のために current 実装を無理に generated runtime へ寄せすぎない。
- Phase 2 では、機能を壊さずに Runtime 本体とその周辺コードを generated Output の contract へ合わせていく。
- つまり、「機能を全部作ること」と「自身の出力に自分を置き換えること」は、別の完了条件として管理する。

### 完了判定の運用

上の定義に照らすと、次の 4 段階を区別する。

| 段階 | 意味 |
| --- | --- |
| route available | 新画面の入口がある。まだ未移植 field や legacy fallback が残っていてよい。 |
| parity partial | 新画面で主要操作はでき、出力比較も一部または代表ケースで一致している。 |
| functional migration done | 設定画面 parity、current source of truth、Output parity、再現性、旧画面不要化をすべて満たす。Phase 1 の完了。 |
| self-host done | generated Output を Runtime 自身へ差し替えても再編集なしで運用を継続できる。Phase 2 の完了。 |

現時点の `%` は file 数ではなく、この完了定義に対する機能マイルストーン重みで見た概算として読む。特に断りがない限り、現在の broad scope `%` は主に Phase 1 の進捗を指す。

## 2026-05-15 snapshot の主系

2026-05-15 時点では、主系は Phase 1 に固定していた。

- まずは「旧設定画面でできたことを current route で完結できる」状態を優先する。
- `LanguageResource` は旧 DB editor parity を追う slice ではなく、optional module + file-based source of truth + inspector-only current route を正本とする。
- self-host / runtime 置換は重要だが、missing setting 機能が残っている段階では主系へ上げない。
- Phase 1 の途中で self-host 側を触る場合も、目的は parity 維持、bridge debt 圧縮、または後段置換のための局所準備に限る。
- legacy-only な情報は、Phase 1 の進行を止めてまで canonical schema へ昇格させない。必要な間は `notes` や bridge で保持してよい。
- 2026-05-15 snapshot の broad scope 読みは [2026-0515-progress-snapshot.md](<repo-root>/docs/reports/2026/2026-0515-progress-snapshot.md) を参照する。2026-06-19 以降の active ordering は [2026-0619-plan-inventory.md](../reports/2026/2026-0619-plan-inventory.md) を参照する。

### Phase 2 を主系へ上げるゲート

次の条件が概ね揃った時点で、Phase 2 を主系へ上げる。

- 旧設定画面依存ではなく、current route で日常の設定変更・build・比較・検証が回る。
- `LanguageResource`、`Project Security / Host Assignment`、`HTML` / `Source Output` bridge debt のような Phase 1 blocker が、少なくとも current 側の landing zone を持っている。
- 残課題の中心が「未移植機能」ではなく、「runtime / loader / generated contract をどう寄せるか」に移っている。
- `Project 1 = MTOOL` の parity が current route 更新後も継続して再確認できる。
- current runtime scope の explicit exclusion と optional track が文書化され、`何を done と呼ぶか` が固定されている。

## 生成コード互換方針

- 旧 `original-codes/mtool_lib/dbclasses/` は、参照用ライブラリではなく「このツール自身が生成して自分で使う runtime artifact」として扱う。
- 新実装も同じ思想を維持する。
- ただし、DB 設計データ Export が未投入の間は、旧生成物コピーによる bootstrap を許容する。
- directory path は後で調整してよいが、basename は旧実装へ寄せる。
- 最低限そろえる basename 系統は以下。
  - `data-*.php`
  - `dbaccess-*.php`
  - `autoload_mtool.php`
- generated file の内部へ editable region は新設せず、generated/custom 分離と Base/Custom または collaborator 境界で拡張する。
- 最終的には PHP code も PSR-4 対応 namespace / directory layout へ寄せる前提だが、current migration / parity phase ではそれを先行条件にしない。
- 当面は parity と legacy 依存切り離しを優先し、file は pragmatic に配置してよい。後で PSR-4 へ寄せやすいよう、loader と依存境界だけを局所化する。

## 新 route の骨格

### `admin`

- `/projects`
- `/projects/{project_key}`
- `/projects/{project_key}/settings`
- `/projects/{project_key}/tables`
- `/projects/{project_key}/tables/import`
- `/projects/{project_key}/tables/{table_key}`
- `/projects/{project_key}/tables/{table_key}/columns`
- `/projects/{project_key}/data-classes`
- `/projects/{project_key}/data-classes/sync`
- `/projects/{project_key}/data-classes/{data_class_key}`
- `/projects/{project_key}/data-classes/{data_class_key}/source`
- `/projects/{project_key}/data-classes/{data_class_key}/fields`
- `/projects/{project_key}/db-access`
- `/projects/{project_key}/db-access/sync`
- `/projects/{project_key}/db-access/{db_access_key}`
- `/projects/{project_key}/db-access/{db_access_key}/edit`
- `/projects/{project_key}/db-access/{db_access_key}/functions`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/source`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/endpoint`
- `/projects/{project_key}/proxy/single`
- `/projects/{project_key}/proxy/custom`
- `/projects/{project_key}/proxy/custom/{custom_proxy_key}`
- `/projects/{project_key}/proxy/custom/{custom_proxy_key}/functions`
- `/projects/{project_key}/proxy/custom/{custom_proxy_key}/endpoint`
- `/projects/{project_key}/html`
- `/projects/{project_key}/html/{html_key}`
- `/projects/{project_key}/html/{html_key}/parameters`
- `/projects/{project_key}/language-resources`
- `/projects/{project_key}/language-resources/{resource_key}`
- `/projects/{project_key}/language-resources/groups`
- `/projects/{project_key}/source-outputs`
- `/projects/{project_key}/source-outputs/change-order`
- `/projects/{project_key}/source-outputs/new`
- `/projects/{project_key}/source-outputs/{source_output_key}`
- `/projects/{project_key}/source-outputs/{source_output_key}/edit`
- `/projects/{project_key}/source-outputs/artifacts/{artifact_key}/download`
- `/projects/{project_key}/security`
- `/projects/{project_key}/security/users`
- `/projects/{project_key}/security/pages`
- `/projects/{project_key}/host-assignments`
- `/projects/{project_key}/compare-output-settings`
- `/projects/{project_key}/compare-output-settings/additional-paths`
- `/default-settings`
- `/history`

### `lab`

- `/experiments`
- `/runs/builds/{project_key}`
- `/runs/builds/{job_key}`
- `/api/runs/builds/{job_key}`
- `/runs/compare-output/{project_key}`
- `/runs/compare-output/{job_key}`
- `/api/runs/compare-output/{job_key}`
- `/runs/endpoints/{project_key}`
- `/api/runs/endpoints/{job_key}`

## 対応表

### 1. Top / Project 基本設定

旧ファイル:

- `index.php`
- `project_edit.php`
- `project_edit_include.php`
- `create_project_group.php`
- `default_setting_show.php`
- `default_setting_download.php`
- `default_setting_lib.php`
- `update_history.php`

新 site:

- `admin`

新 route / module:

- `/projects`
- `/projects/{project_key}`
- `/projects/{project_key}/settings`
- `/project-groups/bootstrap`
- `/default-settings`
- `/history`

補足:

- 現在の新実装では `/projects/{project_key}/settings` が利用可能であり、`name`、`slug`、`lifecycle_status`、`description` を更新できる。
- `project_edit_include.php` が持っていた `DBType`、`DBUserPID`、`DBManagerURL`、`TokenForProxyAccess`、各種 option は、`Project` 詳細設定へ集約する。
- `index.php` の「プロジェクトごとのハブ」機能は、新実装では `/projects/{project_key}` に置く。

### 2. DB Table metadata

旧ファイル:

- `dbtables.php`
- `dbtable_edit.php`
- `dbtable_edit_include.php`
- `dbtable_columns.php`
- `dbtable_column_edit.php`
- `dbtable_column_edit_include.php`
- `dbtables_import.php`
- `dbtables_import_for_each.php`
- `dbtables_import_common.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/tables`
- `/projects/{project_key}/tables/{table_key}`
- `/projects/{project_key}/tables/{table_key}/columns`
- `/projects/{project_key}/tables/import`

補足:

- 現在の新実装では `/projects/{project_key}/tables` が利用可能であり、canonical `dbtable` / `dbtablecolumns` を優先表示し、未導入 project だけ runtime reference fallback を表示する。
- `/projects/{project_key}/tables/import` は preview だけでなく apply を持ち、`MTOOL` first slice では live schema から canonical import を実行できる。
- `/projects/{project_key}/tables/{table_key}` と `/projects/{project_key}/tables/{table_key}/columns` は canonical detail を表示し、必要なら runtime reference も辿れる。
- `dbtable` / `dbtablecolumns` は canonical metadata として `db-config` 側に持つ。

### 3. Data Class

旧ファイル:

- `dataclasses.php`
- `dataclass_edit.php`
- `dataclass_edit_include.php`
- `dataclass_fields.php`
- `dataclass_field_edit.php`
- `dataclass_field_edit_include.php`
- `dataclasses_sync.php`
- `dataclasses_sync_for_each.php`
- `dataclass_fields_sync_inherit.php`
- `dataclasses_source.php`
- `dataclasses_change_order.php`
- `dataclasses_change_order_include.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/data-classes`
- `/projects/{project_key}/data-classes/{data_class_key}`
- `/projects/{project_key}/data-classes/{data_class_key}/fields`
- `/projects/{project_key}/data-classes/sync`
- `/projects/{project_key}/data-classes/{data_class_key}/source`

補足:

- 現在の新実装では `/projects/{project_key}/data-classes` が利用可能であり、canonical `dataclass` / `dataclassfields` を優先表示し、未導入 project だけ runtime reference fallback を表示する。
- `/projects/{project_key}/data-classes/sync` は preview だけでなく apply を持ち、`MTOOL` first slice では table metadata から canonical sync を実行できる。
- `/projects/{project_key}/data-classes/{data_class_key}`、`/projects/{project_key}/data-classes/{data_class_key}/fields`、`/projects/{project_key}/data-classes/{data_class_key}/source` は canonical detail を表示し、runtime reference source があれば reference として併記する。
- sync は canonical metadata を更新する操作として `admin` に置く。
- 並び替えは個別画面ではなく、一覧上の order update として再設計する。

### 4. DB Access Class

旧ファイル:

- `da.php`
- `da_edit.php`
- `da_edit_include.php`
- `da_source.php`
- `da_sync.php`
- `da_table_include.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/db-access`
- `/projects/{project_key}/db-access/{db_access_key}`
- `/projects/{project_key}/db-access/{db_access_key}/edit`
- `/projects/{project_key}/db-access/{db_access_key}/source`
- `/projects/{project_key}/db-access/sync`

補足:

- 現在の新実装では `/projects/{project_key}/db-access`、`/projects/{project_key}/db-access/sync`、`/projects/{project_key}/db-access/{db_access_key}`、`/projects/{project_key}/db-access/{db_access_key}/edit`、`/projects/{project_key}/db-access/{db_access_key}/source` が利用可能であり、generated `dbaccess-*.php` を元にした preview と canonical sync を扱う。
- 一覧と detail では、保存済み `project_db_access_classes` row があれば canonical state を併記する。
- `/projects/{project_key}/db-access/sync` は preview-only ではなく、runtime reference 内の `dbaccess-*.php` から `project_db_access_classes` / `project_db_access_functions` を bulk sync できる。
- sync は現在 `MTOOL` のみ対応で、`manual` / `seed-legacy` row を保持しつつ `sync-bootstrap` row を更新する。
- `Project 1 (Mtool)` の class/function/designer baseline は `019_project_db_access_class_function_seed.sql` / `020_project_db_access_designer_seed.sql` と `022_backfill_runtime_legacy_selectlist_sort_order_columns.sql` として initdb に取り込み、`/db-access/sync` 自体は legacy import を持たない。
- `/projects/{project_key}/db-access/{db_access_key}/edit` は `data-da.php` / `dbaccess-da.php` を source of truth にした legacy metadata schema draft を表示しつつ、`project_db_access_classes` へ保存する。
- `da_edit_include.php` 相当は `DB Access Class` 基本設定画面として独立させる。
- `StoreBasePath`、`IsAutoload` などは `ProjectSourceOutput` との関係を意識して再設計する。
- `dbclasses` 相当の generated runtime layer では、`data-<Name>.php` / `dbaccess-<Name>.php` の basename を旧実装に寄せる。

### 5. DB Access Function / Query 設計

旧ファイル:

- `da_funcs.php`
- `da_funcs_table_include.php`
- `da_funcs_change_order.php`
- `da_funcs_change_order_include.php`
- `da_func_edit.php`
- `da_func_edit_include.php`
- `da_func_move.php`
- `da_func_sort_order_edit.php`
- `da_func_source.php`
- `da_func_endpoint.php`
- `da_func_common_for_blob.php`
- `da_func_explanation_for_alias_include.php`
- `da_func_select_alias_lib.php`
- `da_func_select_target_fields.php`
- `da_func_select_target_field_edit.php`
- `da_func_select_target_field_edit_include.php`
- `da_func_select_target_fields_sync.php`
- `da_func_select_target_fields_update_list_order_lib.php`
- `da_func_select_where.php`
- `da_func_select_where_edit.php`
- `da_func_select_where_edit_include.php`
- `da_func_select_where_change_order.php`
- `da_func_select_where_change_order_include.php`
- `da_func_select_where_input_aid.php`
- `da_func_select_where_table_include.php`
- `da_func_select_having.php`
- `da_func_select_having_edit.php`
- `da_func_select_having_edit_include.php`
- `da_func_select_having_table_include.php`
- `da_func_insert_target_fields.php`
- `da_func_insert_target_field_edit.php`
- `da_func_insert_target_field_edit_include.php`
- `da_func_insert_target_fields_sync.php`
- `da_func_insert_or_update_target_fields_update_list_order_lib.php`
- `da_func_update_target_fields.php`
- `da_func_update_target_field_edit.php`
- `da_func_update_target_field_edit_include.php`
- `da_func_update_target_fields_sync.php`
- `da_func_update_delete_where.php`
- `da_func_update_delete_where_edit.php`
- `da_func_update_delete_where_edit_include.php`
- `da_func_update_delete_where_change_order.php`
- `da_func_update_delete_where_change_order_include.php`
- `da_func_update_delete_where_input_aid.php`
- `da_func_update_delete_where_table_include.php`
- `da_func_target_fields_for_insert_and_update_common_include.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/db-access/{db_access_key}/functions`
- `/projects/{project_key}/db-access/{db_access_key}/functions/change-order`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/move`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-where`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-where/new`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-where/input-aid`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-where/change-order`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-where/{select_where_key}`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-target-fields`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-target-fields/new`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-target-fields/{select_target_field_key}`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-having`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-having/new`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-having/{select_having_key}`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/insert-target-fields`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/insert-target-fields/new`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/insert-target-fields/{insert_target_field_key}`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-target-fields`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-target-fields/new`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-target-fields/{update_target_field_key}`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-delete-where`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-delete-where/new`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-delete-where/input-aid`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-delete-where/change-order`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-delete-where/{update_delete_where_key}`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/source`
- `/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/endpoint`

補足:

- 現在の新実装では `/projects/{project_key}/db-access/{db_access_key}/functions`、`/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}`、`/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/source`、`/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/endpoint` が利用可能であり、generated method を元にした preview を表示する。
- function detail / endpoint では `data-dafunc.php` / `dbaccess-dafunc.php` を参照し、legacy ActionType と canonical field draft を併記している。
- `functions/{function_key}` は `project_db_access_functions` へ保存でき、functions 一覧と endpoint preview に反映される。
- `functions/change-order` も実装済みであり、保存済み canonical function row の一括順序更新と `RESET` を行える。
- `functions/{function_key}/move` も実装済みであり、generated dbaccess file に同名 method がある DB Access へ canonical function row を移せる。
- move は移動先に同名 canonical row が既にある場合は拒否し、child designer row は同じ function id に紐づいたまま追従する。
- `select-where` は先行実装済みであり、`project_db_access_function_select_wheres` へ list/create/update/delete を行える。
- `select-where/input-aid` も実装済みであり、generated property 候補と既存 row の対応を確認しつつ add/edit 導線を辿れる。
- `select-where/change-order` も実装済みであり、一括順序更新と `RESET` による 1..n 再採番を行える。
- `data-dafuncselectwhere.php` / `dbaccess-dafuncselectwhere.php` を参照し、legacy schema と canonical row を併記する。
- `select-target-fields` も実装済みであり、`project_db_access_function_select_target_fields` へ list/create/update/delete を行える。
- `data-dafuncselecttargetfields.php` / `dbaccess-dafuncselecttargetfields.php` を参照し、legacy schema と canonical row を併記する。
- `select-having` も実装済みであり、`project_db_access_function_select_havings` へ list/create/update/delete を行える。
- `data-dafuncselecthaving.php` / `dbaccess-dafuncselecthaving.php` を参照し、legacy schema と canonical row を併記する。
- `insert target fields` も実装済みであり、`project_db_access_function_insert_target_fields` へ list/create/update/delete を行える。
- `data-dafuncinserttargetfields.php` / `dbaccess-dafuncinserttargetfields.php` を参照し、legacy schema と canonical row を併記する。
- `update target fields` も実装済みであり、`project_db_access_function_update_target_fields` へ list/create/update/delete を行える。
- `data-dafuncupdatetargetfields.php` / `dbaccess-dafuncupdatetargetfields.php` を参照し、legacy schema と canonical row を併記する。
- `update-delete where` も実装済みであり、`project_db_access_function_update_delete_wheres` へ list/create/update/delete を行える。
- `update-delete-where/input-aid` も実装済みであり、generated property 候補と既存 row の対応を確認しつつ add/edit 導線を辿れる。
- `update-delete-where/change-order` も実装済みであり、一括順序更新と `RESET` による 1..n 再採番を行える。
- `data-dafuncupdatedeletewhere.php` / `dbaccess-dafuncupdatedeletewhere.php` を参照し、legacy schema と canonical row を併記する。
- `action` と `HTTP method` は、saved row が無い場合は method 名ベースの heuristic である。
- 旧実装の「SELECT 対象」「WHERE」「HAVING」「UPDATE/DELETE 条件」「INSERT 対象」「UPDATE 対象」は、1 つの query designer 配下の sub-resource として整理する。
- `select-where` / `update-delete where` の change-order は一括 order update と `RESET` で先行移植した。
- function move は先行移植できた。`Project 1 (Mtool)` の designer sub-resource は `020_project_db_access_designer_seed.sql` で canonical 初期値へ寄せたため、残るのは Project 1 以外をどう migration / refresh するかの再設計である。
- `shared/project_output_html_module_generator.php` の `da_func*` wrapper は preview entry currentization まで進み、`da_func_source.php` / `da_func_select_where.php` / `da_func_select_target_fields.php` / `da_func_select_having.php` / `da_func_update_delete_where.php` / `da_func_update_delete_where_input_aid.php` / `da_func_insert_target_fields.php` / `da_func_update_target_fields.php` は project mismatch / unknown `DAFuncPID` / unknown `DAPID` / unsupported verb でも nearest current function/list route へ縮退する。
- `da_func_move.php` / `da_func_sort_order_edit.php` / `da_func_select_where_edit.php` / `da_func_update_delete_where_edit.php` / `da_func_select_target_field_edit.php` / `da_func_select_having_edit.php` / `da_func_insert_target_field_edit.php` / `da_func_update_target_field_edit.php` は legacy POST/save semantics を温存しつつ、invalid GET deep link だけ current move/detail/designer list route へ寄せる。
- `da_func_select_where_input_aid.php` / `da_func_select_where_change_order.php` / `da_func_update_delete_where_change_order.php` は invalid GET / unsupported verb を current route へ寄せ、interactive filter state や `NewSortOrder` / `doReset` のような action semantics は `_legacy/` fallback として分離した。
- `da_func_edit.php` の blank add flow と designer item-level canonical mapping 不在は残課題であり、non-currentizable guard fallback 整理と item mapping slice に切り出す。
- query builder の state は正規化しすぎず、UI 編集しやすい aggregate にまとめ直す。

### 6. Proxy 設定

旧ファイル:

- `da_edit_proxy_single_target.php`
- `da_funcs_edit_proxy_single_target.php`
- `da_funcs_edit_proxy_single_setting.php`
- `da_funcs_edit_proxy_single_setting_edit.php`
- `da_funcs_edit_proxy_single_setting_edit_include.php`
- `da_proxy_custom.php`
- `da_proxy_custom_edit.php`
- `da_proxy_custom_edit_include.php`
- `da_proxy_custom_func.php`
- `da_proxy_custom_func_edit.php`
- `da_proxy_custom_func_edit_include.php`
- `da_proxy_custom_func_change_order.php`
- `da_proxy_custom_func_change_order_include.php`
- `da_proxy_custom_func_table_include.php`
- `da_proxy_custom_endpoint.php`
- `proxy_auth_common_include.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/proxy/single`
- `/projects/{project_key}/proxy/custom`
- `/projects/{project_key}/proxy/custom/{custom_proxy_key}`
- `/projects/{project_key}/proxy/custom/{custom_proxy_key}/functions`
- `/projects/{project_key}/proxy/custom/{custom_proxy_key}/endpoint`

補足:

- Simple Proxy と Custom Proxy は別 UI を維持しつつ、内部 service は共通化する。
- endpoint 表示は実行 API ではなく定義確認画面として `admin` に置く。
- 現時点では `admin:/projects/{project_key}/proxy/single`、`/proxy/custom`、`/proxy/custom/{custom_proxy_key}`、`/proxy/custom/{custom_proxy_key}/functions`、`/proxy/custom/{custom_proxy_key}/endpoint` が利用可能であり、`project_custom_proxies` / `project_custom_proxy_steps` / `project_custom_proxy_source_output_targets` に canonical metadata を保存する。
- route key は legacy PID ではなく新規 `custom_proxy_key` を使う。
- `Project 1 (Mtool)` 由来の主要 custom proxy は legacy seed 済みで、target source output も `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` へ再マップ済みである。
- custom endpoint preview は legacy `CUSTOMPROXYSERVER` semantics に合わせて server target source output のみを候補にする。
- `source-outputs/{source_output_key}` detail と `mtool/scripts/show_source_output_build_plan.php` では、この metadata を build plan preview として読める。
- actual build/source 生成は first-pass まで接続済みであり、proxy server/client artifact を生成できる。
- `GetFunc` / `LoginCookieToken` の具体実装は generated wrapper handler の hook で受ける。
- `da_funcs_edit_proxy_single_setting_edit.php` は legacy auth-only POST を current function detail save へ bridge できるようになり、unknown `DAFuncPID` POST は current single proxy page の bridge error に、unknown `SingleProxy_SingleGetFuncPID` は current function detail の bridge error に寄せる。
- `/projects/{project_key}/proxy/single` は独立 route として bulk target save まで受けられるようになり、legacy `da_funcs_edit_proxy_single_target.php` POST も current payload へ変換して bridge できる。unknown checkbox pair は legacy 同様に無視し、unknown `DAPID` POST は current validation に寄せる。
- single proxy 側の bulk target POST、custom proxy 側の `da_proxy_custom_edit.php` / `da_proxy_custom_func*.php` action semantics、`endpoint_test_json_ajax.php` known-project POST は current 化済みであり、GET/HEAD の unknown PID deep link も current list へ寄せた。POST/action unknown ID も main path では current-side validation / bridge error に寄り、endpoint helper include 群も current handoff shim へ置き換わった。さらに `endpoint_test_json_ajax.php` の malformed `ProjectPID` / shared bootstrap missing guard と proxy save/reorder の shared-root missing / unsupported verb / malformed POST guard も current handoff へ畳み込めたので、published `HTML-DB` の proxy / endpoint wrappers から `_legacy` 参照は外れた。次段は canonical item mapping / malformed guard の整理である。
- proxy current pages は query string の `bridge_errors` も受けられるようにし、wrapper が internal POST dispatch できない unknown / missing legacy PID error path でも current list/detail/functions page へ redirect して error を表示できるようにした。
- これにより shared-root lookup failure 系の残件は current page への redirect/handoff で吸収できるようになり、proxy / endpoint wrappers の `_legacy` 参照は main path から外れた。
- `project_source_output.php`, `da.php`, `da_source.php`, `compare_output_do.php`, `build_project.php` のような redirect-only wrapper も project mismatch / unsupported verb を current route 側へ縮退させ、guard fallback は edit/action bridge と shared-root lookup を持つ箇所へさらに寄った。
- `source-outputs` cluster では current `/source-outputs` / `/source-outputs/new` / `/source-outputs/{source_output_key}/edit` / `/source-outputs/change-order` が bridge error を受けられるようになり、existing row の update/delete POST、blank add-flow GET/POST、reorder/reset POST も current route へ bridge された。published `project_source_output_edit.php` / `project_source_output_change_order.php` の `_legacy` fallback は消えた。
- `build_project_ajax.php`, `build_project_ajax_check_if_completed.php`, `compare_output_do_ajax.php` も project mismatch で legacy worker に戻さず current notice / JSON handoff を返すようにし、run 系 wrapper の fallback をさらに縮めた。
- `dbtables.php`, `dbtable_columns.php`, `dataclasses.php`, `dataclass_fields.php`, `compare_output.php`, `compare_output_additional_path.php` も route-prefix/list wrapper として current list/base route へ縮退するようになり、main path の `_legacy/` fallback は edit/save semantics と non-currentizable guard へさらに寄った。
- `dbtable_edit.php`, `dbtable_column_edit.php`, `dataclass_edit.php`, `dataclass_field_edit.php`, `compare_output_edit.php`, `compare_output_additional_path_edit.php` は legacy POST/save を温存しつつ、invalid GET deep link だけ current list/base route へ寄せる段階まで進んだ。
- 2026-05-12 時点では default core seed に `PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` を持ち、legacy simple proxy row の non-`ApacheHostSetting` remap をここで扱う。
- `SAMPLE-SINGLE-PROXY-*` は default initdb から外し、`tests/scenarios/mtool-single-proxy/seed/` 配下の optional sample/test seed として別管理する。

### 7. HTML

旧ファイル:

- `htmls.php`
- `html_edit.php`
- `html_edit_include.php`
- `html_parameters.php`
- `html_parameter_edit.php`
- `html_parameter_edit_include.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/html`
- `/projects/{project_key}/html/{html_key}`
- `/projects/{project_key}/html/{html_key}/parameters`

補足:

- current route として `project_htmls_page.php` / `project_html_detail_page.php` / `project_html_parameters_page.php` を追加し、HTML list/detail/parameter CRUD を current admin route から扱えるようにした。
- `htmls.php` と `html_parameters.php` は current route へ handoff する。
- `html_edit.php` と `html_parameter_edit.php` は legacy GET deep link と create/update/delete POST を current route/action へ bridge し、copied legacy reference (`mtool/reference/mtool-legacy-html-catalog.json`) は既存 `html_key` 保持と parameter audit metadata に限定する。
- `project_html_source_bindings` を追加し、legacy `ProjectSourceOutputPID` ごとの current `source_output_key` / `module_source_ref` / `refresh_policy` を current DB に保持するようにした。`/projects/{project_key}/html` と `/{html_key}` はこの binding を優先して effective source root を表示する。
- `project_html_route_common.php` と `project_output_html_module_generator.php` もこの binding table を first lookup に寄せ、`project_source_outputs.notes` に埋めた bootstrap metadata は fallback へ下げた。
- `project_html_definitions` / `project_html_parameters` を導入し、live `html` / `htmlParameter` row は current canonical table を正本にした。MTOOL は copied legacy reference から初回 bootstrap し、public PID / `html_key` を維持する。
- `html_templates` / `html_template_parameters` を導入し、global template settings も current canonical table を正本にした。`/settings/html-templates`、`/{legacy_template_pid}`、`/{legacy_template_pid}/parameters` で list/detail/parameter CRUD を current admin route から扱える。
- `project_html_parameters_page.php` の expected parameter audit も copied reference ではなく canonical template metadata を優先して組み立てるようにした。
- `DataType=dbaccessclassname` の selector は引き続き canonical `project_db_access_classes` fallback を使う。
- 次段では generator/runtime 側に残る template bootstrap dependency を削り、HTML module 生成も current template metadata 正本へ寄せる。

### 8. Language Resource

旧ファイル:

- `lang_res.php`
- `lang_res_list.php`
- `lang_res_edit.php`
- `lang_res_edit_include.php`
- `lang_res_group_edit.php`
- `lang_res_group_edit_include.php`
- `lang_res_assign_additional_group.php`
- `lang_res_assign_additional_group_include.php`
- `lang_res_move.php`
- `lang_res_move_include.php`
- `lang_res_auto_translate_ajax.php`
- `lang_res_check_project_source_output_setting_lib.php`
- `lang_res_select_resource_group_lib.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/language-resources`
- `/projects/{project_key}/language-resources/{resource_key}`
- `/projects/{project_key}/language-resources/groups`

補足:

- current route として `project_language_resources_page.php` / `project_language_resource_groups_page.php` / `project_language_resource_detail_page.php` を追加し、`/projects/{project_key}/language-resources`、`/groups`、`/{resource_key}` を inspector-only の current admin 導線として扱えるようにした。
- copied legacy reference は `mtool/scripts/export_legacy_language_resource_reference.php --host-side` で host-side `original-codes/mtool.sql` dump から切り出し、`mtool/reference/mtool-legacy-language-resource-catalog.json` として保持する。現在の `MTOOL` catalog は `resource=1007`、`group=7`、`caption=20250`、`language=51` である。
- `MTOOL` を `mtool/reference/mtool-legacy-language-resource-catalog.json` と file-based catalog へ展開した。catalog root は `MTOOL -> mtool/resources/` とする。overlay seed parser は `INSERT ... SELECT ... UNION ALL ...` と `INSERT INTO ... VALUES (...)` の両方を扱う。
- runtime/generator/wrapper が読む catalog は `project_language_resource_catalog_loader.php` から `file-canonical -> reference -> empty` で解決し、DB canonical table は live read source に使わない。
- `project_language_resource_groups` / `project_language_resource_group_languages` / `project_language_resource_group_source_outputs` / `project_language_resource_languages` / `project_language_resources` / `project_language_resource_captions` / `project_language_resource_additional_groups` は migration/debug 用 canonical bridge に下げ、`mtool/scripts/debug/language_resource/lib/project_language_resource_sync_service.php` が `mtool/scripts/debug/language_resource/lib/project_language_resource_db_bridge.php` を直接使う。runtime/live path に `project_language_resource_repository.php` 互換 shim は残さない。
- `/projects/{project_key}/language-resources` は file/reference catalog の browse/search inspector を表示する。create/duplicate/save/delete は current admin では扱わない。
- `/projects/{project_key}/language-resources/groups` は group summary inspector と file path 案内を表示する。selected languages / target source outputs の current CRUD は残さない。
- `/projects/{project_key}/language-resources/{resource_key}` は resource detail inspector と file path 案内を表示する。caption update/delete、base group move、additional group assignment は `resource.json` 直接編集へ寄せる。
- current admin に `LanguageResource` auto-translate route は持たない。必要な翻訳は repo 配下の JSON file を AI / 人が直接更新する file workflow で扱う。
- generated `HTML-DB` 側では `lang_res.php` / `lang_res_list.php` を current groups/resources landing route へ、`lang_res_edit.php` / `lang_res_group_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` を read-only inspector bridge へ寄せる。`lang_res_auto_translate_ajax.php` も current endpoint へ handoff せず、legacy JSON 互換の NG response で file workflow へ案内する read-only bridge とする。`make mtool-html-db-lang-res-wrapper-check` は最新 `HTML-DB` artifact を rebuild/publish したうえで published docroot を smoke し、この挙動を再確認する。
- generated internal-dispatch wrapper の current app bootstrap 探索は `APP_APP_ROOT` を優先し、未設定時でも repo 同居 artifact と `work/source-outputs/...` publish root の両方から `mtool/app` と legacy `shared` を探索できるようにした。resource 系 wrapper は static `legacy_resource_pid -> resource_key` map miss 時に current catalog loader へ fallback lookup する。さらに local smoke router と `mtool/scripts/check_generated_html_db_language_resource_wrappers.php` を追加し、artifact docroot / `--publish` 済み docroot の両方で login cookie 付きの read-only checks と `--allow-mutate` 付きの legacy write-entry bridge check が end-to-end で通ることを確認した。`Makefile` には `make mtool-html-db-lang-res-wrapper-check` を追加した。legacy `lang_res_*_include.php` と helper (`lang_res_check_project_source_output_setting_lib.php`, `lang_res_select_resource_group_lib.php`) も generated root では薄い wrapper に置き換え、実体は `_legacy/` 配下へ退避した。
- current admin page と project hub では language resource module state を `file canonical available` / `reference fallback` / `optional module off` / `blocked` として明示し、read-only 状態では create/update/delete 導線を出さないようにした。project hub の `Language Resource` card も固定 `available-partial` ではなく実際の module state に応じて `available-partial` / `optional-readonly` / `optional-off` / `blocked` を出す。
- この slice は broad scope 上では cleanup と横展開を少し残すが、module status としては `available-partial` より `optional-readonly` と読む方が近い。generated wrapper の automation、publish 経路確認、legacy include/helper の `_legacy/` 隔離、optional read-only/off UX の固定に加え、`ProjectSourceOutput(ClassType=LanguageResource)` bootstrap と `LanguageResource*` DBAccess seed も core `config-seed/` から切り離した。default `01_mtool.compose.yaml` は LanguageResource source output / DBAccess metadata なしで起動し、旧 overlay compose / seed pack は current path から外して archive へ退避する。残りは compatibility wrapper / debug bridge の出口条件と、次の pilot への展開判断である。
- migration 期間は legacy 互換のため `admin` 配下に read-only inspector を残すが、default core の恒久必須 module とはみなさない。
- `LanguageResource` は optional module へ分離し、DB 中心管理ではなく AI/Git 向けの code-native / file-based 管理へ置き換える方針を 2026-05-15 時点で固定した。
- 詳細な段階移行は [language-resource-separation.md](language-resource-separation.md) を正本とする。

### 9. Source Output

旧ファイル:

- `project_source_output.php`
- `project_source_output_table_include.php`
- `project_source_output_edit.php`
- `project_source_output_edit_include.php`
- `project_source_output_change_order.php`
- `project_source_output_change_order_include.php`
- `source_comment_include.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/source-outputs`
- `/projects/{project_key}/source-outputs/change-order`
- `/projects/{project_key}/source-outputs/new`
- `/projects/{project_key}/source-outputs/{source_output_key}`
- `/projects/{project_key}/source-outputs/{source_output_key}/edit`
- `/projects/{project_key}/source-outputs/artifacts/{artifact_key}/download`

補足:

- 現時点の新実装では、`project_source_outputs` を canonical definition table として導入済みである。
- default core seed は `MTOOL / RUNTIME-DBCLASSES` に加え、Custom Proxy target 用の `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` と、single proxy core 用の `PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` を持つ。
- `SAMPLE-SINGLE-PROXY-SERVER` / `SAMPLE-SINGLE-PROXY-CLIENT` は `tests/scenarios/mtool-single-proxy/seed/` 側へ移し、fresh initdb の default state には含めない。
- `RUNTIME-DBCLASSES` は UI / CLI から artifact 生成でき、runtime reference source を staging したうえで sync 済み canonical DB Access metadata があれば root `dbaccess-*` を wrapper 再生成する。
- 現在の runtime mode は `canonical-dbaccess-partial-sql-regenerated` であり、2026-05-19 時点の current baseline では `sql_regenerated_dbaccess_count=98` / `sql_regenerated_function_count=505` / `canonical_helper_function_count=7` / `canonical_data_class_count=99` / `data_entity_count=99` / `plain_data_candidate_count=63` / `non_plain_data_candidate_count=36` / `bootstrap_data_class_count=0` / `legacy_delegate_function_count=0` を確認している。simple CRUD / first-pass joined select に加えて zero-arg の fixed/raw cleanup DELETE、same-table OR group を含む select/update/delete、helper-style method、plain DTO として一致確認が通った `data-*` も canonical 側へ再生成し、legacy input の empty constructor は generated no-op constructor へ置き換え済みである。`default_property_value` を持つ `TestPattern` と `TestConditionSelection`、`da` / `dataclass` の method-only `ADDITIONAL CLASS DEFINITION`、`dbtablecolumns` の wrapper-property + helper-method、`Project` / `ProjectSourceOutput` / `Req` / `da*` / `htmlTemplateParameter` の method+enum lane、さらに `ProjectUser` / `SpecContent` / `htmlTemplate` の top-level declaration lane まで wrapper/base 形式へ吸収した。final runtime bundle では全 `data-*` が `root wrapper + base/data-*Base.php` になっており、`ApacheHostSetting` / `ApacheHostSettingTemplate` は Apache config template / host assignment infra 用のため runtime reference scope から明示除外している。full self-loop は self-generated artifact 入力と promoted default reference 入力の両方で通っており、current default mode は `self-generated-reference:canonical-dbaccess-partial-sql-regenerated` で自動判定される。
- `data-*` の transition-state 残件は解消済みであり、次段の主対象は file/blob parameter を含む query 再生成ギャップと、legacy SQL support に残る fallback 縮退整理である。legacy blob target は `prepare()` + `bind_param("b")` + `send_long_data()` を伴うため、current generator では `is_blob_target=1` を明示 delegate とし、通常の SQL 文字列再生成へは混ぜない。`select_having` は canonical `select_target_fields` を参照する argument / fixed / field 比較まで、`parameter_type=anotherfield` を含む join ON grouping は non-empty `or_group` / `or_group_type=andorand` まで current generator で扱える。
- proxy 向け 2 definition は `custom-proxy-server` / `custom-proxy-client` strategy で actual artifact を生成できる。
- proxy 向け 2 definition の detail では、target custom proxy の build plan preview も表示する。
- `compare_output` の初期 seed は `MTOOL / MAIN` と `MTOOL / CLIENTCOMMON` を持ち、旧 `Project 1 (Mtool)` の Compare Output を local placeholder path に寄せている。
- 現時点では `/projects/{project_key}/source-outputs`、`/source-outputs/new`、`/source-outputs/change-order`、`/source-outputs/{source_output_key}`、`/source-outputs/{source_output_key}/edit` が利用可能であり、canonical definition の list/create/update/delete/reorder を扱える。
- `project_source_output_edit.php` wrapper は existing row の update/delete POST を current `/edit` action へ bridge し、blank add-flow GET/POST は current `/new` handoff へ切り替わった。`project_source_output_change_order.php` wrapper は legacy reorder/reset POST を current `/change-order` へ bridge する。wrapper 由来の `bridge_errors` と delete success も current page 側で表示する。
- published `project_source_output_edit.php` / `project_source_output_change_order.php` の `_legacy` fallback は消えた。current `/new` page は `ClassType` / `ProxyBaseURL` / mapped target server source output から proxy strategy / binding を初期推定し、legacy dir / URL から読める key / name は `safe-prefill` / `warning-candidate` / `manual-only` に分類して扱う。legacy-only field 群は current schema に足さず、wrapper handoff / create / edit / detail で `notes` structured block に退避する。
- current schema で canonical 保存しているのは `ProgramLanguage`、`ClassType`、`ReleaseTargetType`、`SourceTemplateDir`、`SourceOutputDir`、`SourceTempOutputDir`、`ProxyBaseURL`、`AutoloadFilenameSuffix`、`SourceTextCharCode` が中心であり、`CustomFileExtention`、`DropboxBaseFolderPID`、`UnitTestTemplateDir`、`UnitTestOutputDir`、`TargetServerProjectSourceOutputPID`、`CSNameSpace`、`JavaPackageName`、`AutoLoadFilePathForPHP`、`JavaFunctionType`、`DotNetLanguageResourceType` は未移植のまま `notes` block へ保持する。
- `/source-outputs` では definition 一覧、definition 追加、artifact 一覧を扱う。
- `/source-outputs/new` では advanced create form と legacy add-flow handoff を扱う。
- `/source-outputs/{source_output_key}` では detail と artifact 生成を扱う。
- `/source-outputs/{source_output_key}/edit` では canonical metadata を保存し、保存後は `source_of_truth=manual` に寄せる。
- `/source-outputs/change-order` では canonical `source_output_list_order` の一括更新と `RESET` を扱う。
- artifact 本体は `work/artifacts/source-outputs/{project_key}/{artifact_key}/` に保存し、`manifest.json` と `tar.gz` archive を持つ。
- UI と CLI は `project_output_service.php` を共用し、host CLI の既定動作は DB なしの local default definition を許容する。
- `ProgramLanguage`、`ClassType`、`ReleaseTargetType`、template dir、output dir、proxy base URL などはここで扱う。
- source comment や template preview は detail 画面の一部へ吸収する。
- `dbclasses` を含む generated artifact の legacy recovery copy / partial self-generation / full self-generation 切り替え方針は、ここに紐づく cross-cutting concern として扱う。

### 10. Project Security / User

旧ファイル:

- `project_security.php`
- `project_security_detail.php`
- `project_security_detail_edit.php`
- `project_security_detail_edit_include.php`
- `project_security_user_edit.php`
- `project_security_user_edit_include.php`
- `project_user_default_permission_lib.php`
- `project_host_assignment.php`
- `project_host_assignment_edit.php`
- `project_host_assignment_edit_include.php`

新 site:

- `admin`

新 route / module:

- `/projects/{project_key}/security`
- `/projects/{project_key}/security/users`
- `/projects/{project_key}/security/pages`
- `/projects/{project_key}/host-assignments`

補足:

- 旧 `ProjectUser` の read/write flag と page security は、route policy と project membership policy に分解して再設計する。
- 2026-05-14 時点で `admin:/projects/{project_key}/security`、`/security/users`、`/security/pages`、`/host-assignments` の current route は存在する。
- `security/users` は `project_memberships` を canonical source of truth にした first slice として利用可能であり、`owner / admin / member` の membership 更新を扱う。
- この first slice では旧 `ProjectUser` の 16 個の read/write bit は schema へ戻さず、page security / capability policy 側へ後段で吸収する前提にする。
- `security/pages` は `project_page_security_policies` + `project_page_security_policy_capabilities` を current landing zone にした second slice まで入っており、`SERVER_NAME + SCRIPT_NAME + SecurityType` を normalized capability list として編集できる。
- `host-assignments` は `project_host_assignments` を current landing zone にした second slice まで入っており、旧画面の visible 4 列を denormalized row として編集できる。
- ただし page security の最終 route policy 連携と、host assignment の infra catalog (`ApacheHostSetting` / `ApacheSetting` / `Server`) への split は後段に残る。

### 11. Build / Compare Output

旧ファイル:

- `build_project.php`
- `build_project_for_each.php`
- `build_project_common_include.php`
- `build_project_ajax.php`
- `build_project_ajax_check_if_completed.php`
- `compare_output.php`
- `compare_output_edit.php`
- `compare_output_edit_include.php`
- `compare_output_table_include.php`
- `compare_output_additional_path.php`
- `compare_output_additional_path_edit.php`
- `compare_output_additional_path_edit_include.php`
- `compare_output_additional_path_table_include.php`
- `compare_output_do.php`
- `compare_output_do_ajax.php`
- `compare_output_template_for_mac_command.txt`
- `compare_output_template_for_mac_command_line.txt`
- `compare_output_template_for_text.txt`
- `compare_output_template_for_text_line.txt`
- `compare_output_template_for_windows_batch.txt`
- `compare_output_template_for_windows_batch_line.txt`
- `compare_ignore_dir_setting_regex.txt`

新 site:

- 設定系は `admin`
- 実行系は `lab`

新 route / module:

- `admin:/projects/{project_key}/compare-output-settings`
- `admin:/projects/{project_key}/compare-output-settings/additional-paths`
- `lab:/runs/builds/{project_key}`
- `lab:/runs/builds/{job_key}`
- `lab:/api/runs/builds/{job_key}`
- `lab:/runs/compare-output/{project_key}`
- `lab:/runs/compare-output/{job_key}`
- `lab:/api/runs/compare-output/{job_key}`

補足:

- build の選択肢、target 定義、compare setting は `admin` に置く。
- 長時間実行、進捗確認、差分確認結果は `lab` に置く。
- `*_ajax.php` は job progress API として再設計する。
- `compare_output_template_*.txt` は command / diff 出力テンプレート資産として扱い、route ではなく compare service の管理対象にする。
- `compare_ignore_dir_setting_regex.txt` は compare rule asset として扱い、必要に応じて `admin` の設定画面から更新できるようにする。
- 現時点の新実装では `admin:/projects/{project_key}/compare-output-settings` と `/additional-paths` が利用可能であり、`project_compare_outputs` / `project_compare_output_additional_paths` に canonical metadata を保存する。
- project 単位の template asset / ignore rule asset は `work/compare-output-assets/{project_key}/` に file-based override として保存し、`admin:/projects/{project_key}/compare-output-settings` から編集できる。
- 現時点では selected definition から local filesystem 向けの compare output file を admin UI / lab UI / CLI のいずれからでも生成できる。
- `lab:/runs/builds/{project_key}` / `{job_key}` / `/api/runs/builds/{job_key}` も利用可能で、selected `ProjectSourceOutput` definition を current generator から `generate + write output` し、`work/job-history/build/{project_key}/{job_key}/manifest.json` へ結果を残す。
- `build_project.php` / `build_project_for_each.php` は current build screen へ redirect し、`build_project_ajax.php` / `build_project_ajax_check_if_completed.php` は current build flow への handoff notice / JSON を返す wrapper に切り替えた。
- `lab:/runs/compare-output/{project_key}` は利用可能で、`db-config` の canonical definition を read-only 参照して実行する。
- compare 実行 job 履歴と結果レビューは、`work/job-history/compare-output/{project_key}/{job_key}/manifest.json` を使う file-based 実装として `lab` 側に入った。
- 残件は job history を DB 化したい場合の境界設計である。

### 12. Endpoint Test / 実行補助

旧ファイル:

- `endpoint_test_json_ajax.php`
- `endpoint_common_include.php`
- `endpoint_lib_include.php`
- `endpoint_test_json_client_include.php`

新 site:

- `lab`

新 route / module:

- `/runs/endpoints/{project_key}`
- `/api/runs/endpoints/{job_key}`

補足:

- 実行確認 API は canonical 設定を変更しないため `lab` に置く。
- current route は実装済みで、project-scoped page から manual absolute URL と single-function proxy candidate の両方を試せる。
- `endpoint_test_json_ajax.php` は GET/HEAD を current route へ handoff し、known-project POST も current endpoint-test job service へ bridge する。`endpoint_common_include.php` / `endpoint_lib_include.php` / `endpoint_test_json_client_include.php` も current handoff shim へ置き換わり、malformed `ProjectPID` / shared bootstrap missing guard では endpoint URL と短い request JSON を prefill した current handoff notice を返すようになった。proxy 系の主残件は endpoint-test 以外の non-currentizable guard fallback 整理になった。

### 13. Archive / 参考退避物

旧ファイル:

- `old/2017-10.zip`

新 site:

- なし

新 route / module:

- なし

補足:

- これは履歴参照用の退避物であり、再構築対象ではない。
- ただし、将来「失われたテンプレートや include の補完」が必要になった場合の調査ソースとして保持する。

## 補助ファイルの扱い

以下は単独画面ではなく、新実装では service / helper / component に吸収する。

- `*_table_include.php`
- `*_common_include.php`
- `*_lib.php`
- `source_comment_include.php`
- `proxy_auth_common_include.php`

## 実装優先順位

1. `Project` 詳細ハブ
2. `project_edit` 相当
3. `dbtables`
4. `dataclasses`
5. `da`
6. `da_func`
7. `project_source_output`
8. `project_security`
9. `build` / `compare_output`
10. `html` / `lang_res` / `proxy`

## 現時点の判断

- `original-codes/dev web/db/` は、基本的に全て再構築対象として扱う。
- ただし、新実装では「旧ファイル 1 枚 = 新 route 1 本」ではなく、aggregate 単位でまとめ直す。
- いらないものが後から見つかったとしても、最初の planning では de-scope 前提にせず、まず受け皿を定義してから削る。
