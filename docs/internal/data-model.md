# Data Model / 最小データモデル

English companion:
This schema guide explains the current minimum data model for the rewrite. It maps the durable tables in `db-config`, the experimental tables in `db-lab`, and the remaining legacy-to-new gaps so contributors can reason about what is canonical today.

## 目的

- 新実装で最初に持つ DB スキーマを、旧実装の `Project` 中心設計と対応づけながら整理する。
- `db-config` と `db-lab` の責務分離をテーブル名レベルで明確にする。

## 方針

- 旧実装の巨大な `Project` を、そのまま再現しない。
- 最初は「設定の正本」と「実験レコード」を分けた最小形だけ持つ。
- 旧実装の複雑な自動プロビジョニングや外部依存は入れない。
- ただし、`mtool_lib/dbclasses` 相当の generated runtime layer は、新実装でも将来的に自己生成・自己利用する前提で扱う。

## `db-config`

`db-config` は canonical な設定系 DB として扱う。

### `projects`

- 新実装の中心集約
- 旧実装の `Project` から、まず次だけを切り出す
  - `project_key`
  - `name`
  - `slug`
  - `lifecycle_status`
  - `owner_login_id`
  - `description`

これは「何を管理対象の project とみなすか」を定義する最小テーブル。

現在の `admin:/projects` では、このテーブルに対して一覧、追加、更新を行う。

### `project_memberships`

- `projects` に紐づくメンバー情報
- 旧実装の `ProjectUser` を単純化した入口
- 現段階では以下だけを持つ
  - `login_id`
  - `role_code`
  - `can_administer`

モジュール別 read/write フラグまではまだ再現しない。
新規 `Project` 作成時は、作成者を `owner` role として自動追加する。
現在の `admin:/projects/{project_key}/security/users` では、このテーブルに対して `owner / admin / member` の編集を行う。

### `project_page_security_policies`

- `projects` に紐づく page security の current landing zone
- 旧 `ProjectSecurityForEachPage` の first-pass canonical table
- 現段階では以下を持つ
  - `server_name`
  - `script_name`
  - `notes`
  - `source_of_truth`

旧実装の `SERVER_NAME + SCRIPT_NAME` row を、そのまま current 側の policy row として持つ。
最終的には current route / service policy へ寄せる想定だが、Phase 1 ではまずこの table を source of truth にして page security を編集できるようにする。
現在の `admin:/projects/{project_key}/security/pages` では、この table と child capability table を使って一覧、追加、更新、削除を行う。

### `project_page_security_policy_capabilities`

- `project_page_security_policies` に紐づく capability 一覧
- 旧 `ProjectSecurityForEachPageDetails` の first-pass canonical table
- 現段階では以下だけを持つ
  - `security_type`

旧 `SecurityType` は 16 個の列へ戻さず、normalized capability list として保持する。
値は `CHATREAD`、`CHATWRITE`、`REQREAD`、`REQWRITE`、`SPECTOOLREAD`、`SPECTOOLWRITE`、`DBTOOLREAD`、`DBTOOLWRITE`、`HTMLREAD`、`HTMLWRITE`、`TESTTOOLREAD`、`TESTTOOLWRITE`、`MINUTESREAD`、`MINUTESWRITE`、`UPLOADREAD`、`UPLOADWRITE` を取り、現行 UI は category ごとの read / write checkbox として編集する。

### `project_host_assignments`

- `projects` に紐づく host assignment の current landing zone
- 旧 `ProjectHostSetting` の first-pass canonical table
- 現段階では以下を持つ
  - `apache_setting_name`
  - `server_local_name`
  - `virtual_host_name`
  - `template_name`
  - `notes`
  - `source_of_truth`

旧画面の visible 4 列を、まずは denormalized row として current 側へ保持する。
最終的には `Server` / `ApacheSetting` / `ApacheHostSettingTemplate` などの infra catalog へ split する想定だが、Phase 1 では host assignment 自体を current route で編集できることを優先する。
このため、Apache config template 専用の `ApacheHostSetting` / `ApacheHostSettingTemplate` を runtime reference / self-loop scope に戻すことはしない。current 側では host assignment に必要な visible field だけをこの landing zone に保持し、template 展開や infra catalog 化が必要になった時点で別モジュールとして扱う。
現在の `admin:/projects/{project_key}/host-assignments` では、この table に対して一覧、追加、更新、削除を行う。

### `project_source_outputs`

- `projects` に紐づく Source Output の canonical definition
- 現段階では以下を持つ
  - `source_output_key`
  - `name`
  - `program_language`
  - `class_type`
  - `release_target_type`
  - `source_template_dir`
  - `source_output_dir`
  - `source_temp_output_dir`
  - `proxy_base_url`
  - `autoload_filename_suffix`
  - `source_text_char_code`
  - `runtime_source_relative_path`
  - `artifact_strategy`
  - `target_binding_type`
  - `output_archive_format`
  - `source_output_list_order`
  - `notes`
  - `source_of_truth`

旧 `ProjectSourceOutput` の全項目を一度に再現せず、まず runtime reference source を staging した runtime/proxy artifact を扱うために必要な metadata をここへ切り出す。
`source_output_dir` は current raw output を置く path として扱い、default は全 project 共通で `work/source-outputs/{project_key}/{source_output_key}` を使う。
`source_temp_output_dir` は disposable staging path metadata として別に持ち、default は `work/staging/source-outputs/{project_key}/{source_output_key}` とする。
`html` bridge の `source_template_dir` は実 path ではなく `catalog://html-module/{project_key}/{source_output_key}` を持てるようにし、resolver が `mtool/reference/html-modules/` -> `legacy-source-snapshots/` -> `legacy-source-placeholders/` の順で curated source tree を解決する。
現在の `admin:/projects/{project_key}/source-outputs`、`/{source_output_key}`、`/{source_output_key}/edit` では、このテーブルに対して一覧、追加、更新を行う。
`target_binding_type` は source output の用途区分を explicit metadata として持つ列であり、`runtime` / `custom-proxy` / `single-function-proxy` / `proxy-metadata-only` / `metadata-only` を取る。空の場合だけ、互換のため `artifact_strategy` / `class_type` から effective scope を fallback 判定する。
ここで重要なのは、`custom-proxy` と `single-function-proxy` を別 binding domain として扱うことである。
`single-function-proxy` は function 直結の単純公開先、`custom-proxy` は multi-step custom proxy の公開先であり、同じ source output definition を canonical には兼用しない。

初期 core seed では次の 7 件を持つ。

- `MTOOL / RUNTIME-DBCLASSES`
  - `mtool/reference/dbclasses` を runtime reference にし、artifact 生成時に root `dbaccess-*` を canonical metadata から差し替える既定 definition
- `MTOOL / DBIMPORT-PROXY-SERVER`
  - legacy `ProjectSourceOutput.PID=300` を canonical key へ寄せた PHP proxy server definition
- `MTOOL / DBIMPORT-PROXY-CLIENT`
  - legacy `ProjectSourceOutput.PID=301` を canonical key へ寄せた C# proxy client definition
- `MTOOL / PAYPAL-PROXY-SERVER`
  - legacy `ProjectSourceOutput.PID=28` (`/proxy_paypal`) を canonical key へ寄せた PHP single-function proxy server definition
- `MTOOL / UPLOADER-PROXY-SERVER`
  - legacy `ProjectSourceOutput.PID=117` (`/proxy_uploader`) を canonical key へ寄せた PHP single-function proxy server definition
- `MTOOL / DBTABLE-PROXY-SERVER`
  - current generic PHP single-function proxy server definition。`dbtable` smoke と imported canonical-bootstrap table の publish / relay target として使う
- `MTOOL / OPENAPI-JSON`
  - current generic OpenAPI JSON definition。single-function proxy target assignment から minimal `openapi.json` を生成し、Lab Swagger viewer の既定 spec に使う

`DBIMPORT-PROXY-*` 2 件は `custom-proxy-server` / `custom-proxy-client` strategy を取り、Custom Proxy build plan と runtime dbclasses reference を入力に actual artifact を生成する。
`PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` 2 件は `single-proxy-server` strategy を取り、legacy simple proxy row を canonical function name へ remap した target assignment を direct artifact build に流す。
`DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` は current-only generic single-function proxy lane の core seed で、fresh initdb 後も `dbtable` smoke と imported table の Swagger lane を再現できるようにする。
`runtime_source_relative_path` には logical runtime source key を保持し、resolver が `mtool/reference/` または `work/runtime-sources/` へ解決した上で server/client とも `tar.gz` artifact を出力する。
ただし生成済み artifact 履歴自体はまだ DB に持たず、`work/artifacts/source-outputs/{project_key}/{artifact_key}/manifest.json` を file-based source of truth として扱う。
current raw output は `work/source-outputs/` に集約し、`mtool/extensions/{project_key}/{source_output_key}` とは混在させない。repo に残す durable sample asset が必要な場合だけ、対応する sample pack の `sample/<category>/<pack>/reference/<source_output_key>/` に別管理する。
2026-05-25 時点の default `MTOOL` row では `RUNTIME-DBCLASSES=runtime`、`DBIMPORT-PROXY-SERVER/CLIENT=custom-proxy`、`PAYPAL-PROXY-SERVER/UPLOADER-PROXY-SERVER/DBTABLE-PROXY-SERVER/OPENAPI-JSON=single-function-proxy` を explicit に保持する。
`single-function-proxy` の sample/test definition である `SAMPLE-SINGLE-PROXY-SERVER` / `SAMPLE-SINGLE-PROXY-CLIENT` は default initdb には含めず、`tests/scenarios/mtool-single-proxy/seed/` 配下で別管理する。
ただし Project 1 の self-loop 本流 source output は引き続き `RUNTIME-DBCLASSES` と `DBIMPORT-PROXY-*` を主系とし、`single-function-proxy` は `custom-proxy` へ吸収せず別 strategy / 別 binding として維持する。

### `project_html_source_bindings`

- `projects` に紐づく legacy html bucket (`ProjectSourceOutputPID`) と current html source output の binding
- 現段階では以下を持つ
  - `legacy_project_source_output_pid`
  - `source_output_key`
  - `module_source_ref`
  - `refresh_policy`
  - `notes`
  - `source_of_truth`

`legacy ProjectSourceOutputPID -> current source_output_key` の対応と html module source root の正本はこの table に置く。
`module_source_ref` は `catalog://html-module/{project_key}/{source_output_key}` 形式の curated ref を保持し、resolver は `project_source_outputs.source_template_dir` と同じ catalog policy で current source tree を解決する。
`refresh_policy` は `follow-source-output` と `manual` を取り、前者は selected `source_output_key` から effective ref を再計算し、後者は `module_source_ref` を固定値として扱う。
この table に row がない bucket は、当面 `project_source_outputs.notes` に埋め込んだ legacy PID bootstrap metadata から candidate を導出するが、current source of truth とみなすのは persist された binding row のみである。
現在の `admin:/projects/{project_key}/html` では binding 一覧、upsert、delete を行い、detail 画面でも effective ref / resolved source root を参照できる。

### `project_html_definitions`

- `projects` に紐づく HTML 定義の canonical table
- 現段階では以下を持つ
  - `legacy_html_pid`
  - `html_key`
  - `name`
  - `legacy_project_source_output_pid`
  - `legacy_html_template_pid`
  - `html_list_order`
  - `last_modified_dt`
  - `notes`
  - `source_of_truth`

旧 `html` の visible row を current 側へ寄せる landing zone であり、`admin:/projects/{project_key}/html` と `/{html_key}` の正本になる。
`legacy_html_pid` は bridge / generated wrapper 互換の public PID として保持し、legacy reference 由来 row は元 PID をそのまま継承する。新規 row は project 内の次番号を採番する。
`html_key` も canonical row に保持し、MTOOL は copied legacy reference から初回 bootstrap した key をそのまま維持する。これにより old deep link / generated wrapper の route 解決を崩さない。

### `project_html_parameters`

- `project_html_definitions` に紐づく HTML parameter の canonical table
- 現段階では以下を持つ
  - `legacy_parameter_pid`
  - `parameter_name`
  - `parameter_value`
  - `parameter_list_order`
  - `notes`
  - `source_of_truth`

旧 `htmlParameter` の visible row を current 側へ寄せる landing zone であり、`admin:/projects/{project_key}/html/{html_key}/parameters` の正本になる。
`legacy_parameter_pid` も bridge 互換の public PID として保持し、legacy reference 由来 row は元 PID を継承する。新規 row は project 内の次番号を採番する。
`project_html_definitions.last_modified_dt` は parameter create/update/delete 時にも current 側で更新する。

### HTML template metadata

#### `html_templates`

- global HTML template metadata の canonical table
- 現段階では以下を持つ
  - `legacy_html_template_pid`
  - `target_type`
  - `parent_legacy_html_template_pid`
  - `name`
  - `program_language`
  - `file_name`
  - `comment`
  - `notes`
  - `source_of_truth`

旧 `htmlTemplate` の visible row を current 側へ寄せる global source of truth であり、`admin:/settings/html-templates` と `/{legacy_template_pid}` の正本になる。
`legacy_html_template_pid` は bridge / generated wrapper 互換の public PID として保持する。canonical table 初回作成時は legacy `htmlTemplate` table があればそこから、無ければ copied MTOOL reference から bootstrap する。

#### `html_template_parameters`

- `html_templates` に紐づく global template parameter の canonical table
- 現段階では以下を持つ
  - `legacy_template_parameter_pid`
  - `legacy_html_template_pid`
  - `parameter_name`
  - `target_value_type`
  - `target_variable_or_class_object`
  - `target_property_of_class_object`
  - `another_template_pid`
  - `trim_last_space`
  - `trim_last_return`
  - `data_type`
  - `notes`
  - `source_of_truth`

旧 `htmlTemplateParameter` の visible row を current 側へ寄せる global source of truth であり、`admin:/settings/html-templates/{legacy_template_pid}/parameters` の正本になる。
`project_html_parameters` の audit でもこの table を参照し、project HTML parameter row の expected shape を current 側で組み立てる。

### `project_compare_outputs`

- `projects` に紐づく Compare Output の canonical definition
- 現段階では以下を持つ
  - `compare_output_key`
  - `name`
  - `storage_base_path`
  - `output_file_path`
  - `output_file_type`
  - `compare_path`
  - `compare_tool_file_path`
  - `compare_output_list_order`
  - `notes`
  - `source_of_truth`

旧 `CompareOutput` の Dropbox PID や asset 周辺までをそのまま再現せず、まず compare 実行に必要な path metadata をここへ切り出す。
現在の `admin:/projects/{project_key}/compare-output-settings` では、このテーブルに対して一覧、追加、更新、削除を行い、選択中 definition から local compare output file の生成も行う。
初期 seed では `MTOOL / MAIN` と `MTOOL / CLIENTCOMMON` を持ち、旧 `Project 1 (Mtool)` の `CompareOutput.PID=1,2` を local placeholder path 向けに寄せた既定 definition として扱う。

### `project_compare_output_additional_paths`

- `project_compare_outputs` に紐づく additional path definition
- 現段階では以下を持つ
  - `additional_path_key`
  - `path_a_base_path`
  - `path_a`
  - `path_b_base_path`
  - `path_b`
  - `is_same_filename_only`
  - `additional_path_list_order`
  - `notes`
  - `source_of_truth`

旧 `CompareOutputAdditionalPath` の PID 参照をやめ、比較元と比較先の path 組をそのまま保持する。
現在の `admin:/projects/{project_key}/compare-output-settings/additional-paths?compare_output_key=...` では、このテーブルに対して一覧、追加、更新、削除を行う。
compare 実行ジョブや比較結果履歴は `db-lab` テーブルにはまだ切り出さず、`work/job-history/compare-output/{project_key}/{job_key}/manifest.json` を file-based source of truth として扱う。
初期 seed では `MTOOL / CLIENTCOMMON` 配下に `PROXYCLIENT` と `LANGRESOURCE` を持ち、旧 `CompareOutputAdditionalPath.PID=6,9` を相対 path 化して保持する。

### Compare Output assets

- Compare Output の template asset と ignore rule asset は、現時点では DB table を持たない。
- `admin:/projects/{project_key}/compare-output-settings` から project 単位で編集し、`work/compare-output-assets/{project_key}/` 配下へ file-based override として保存する。
- 未保存時は built-in default を使い、保存済み asset がある場合だけ project override を優先する。
- 現時点で持つ asset は次の 7 個である。
  - `compare_output_template_for_text.txt`
  - `compare_output_template_for_text_line.txt`
  - `compare_output_template_for_windows_batch.txt`
  - `compare_output_template_for_windows_batch_line.txt`
  - `compare_output_template_for_mac_command.txt`
  - `compare_output_template_for_mac_command_line.txt`
  - `compare_ignore_dir_setting_regex.txt`

### `project_db_access_classes`

- `projects` に紐づく DB Access Class の canonical metadata
- 現段階では以下を持つ
  - `source_name`
  - `store_base_path`
  - `is_autoload`
  - `notes`
  - `source_of_truth`
  - `last_detected_dbaccess_file`
  - `last_detected_data_file`

旧 `da` の全項目をそのまま再現せず、まず class 単位で必要な保存項目だけを分離して持つ。
現在の `admin:/projects/{project_key}/db-access/{db_access_key}/edit` では、このテーブルに対して upsert を行う。
`admin:/projects/{project_key}/db-access/sync` と `scripts/sync_project_db_access.php` では、runtime reference 内の `dbaccess-*.php` から `source_of_truth=sync-bootstrap` row を bulk sync できる。
`019_project_db_access_class_function_seed.sql` では `Project 1 (Mtool)` の canonical baseline として class 101 row を持つ。
`RUNTIME-DBCLASSES` の runtime generator は、この class row と function row を参照して root `dbaccess-*` の overlay 可否を判定する。

### `project_db_access_functions`

- `project_db_access_classes` に紐づく DB Access Function の canonical metadata
- 現段階では以下を持つ
  - `function_name`
  - `function_list_order`
  - `function_suffix`
  - `action_type`
  - `data_class_base_name`
  - `target_table_name`
  - `parameter_type`
  - `select_by_distinct`
  - `sort_order_columns`
  - `memo`
  - `limit_parameter_type`
  - `limit_fixed_parameter`
  - `or_group_type`
  - `single_proxy_auth_type`
  - `single_proxy_single_get_function_name`
  - `is_blob_target`
  - `detected_signature`
  - `detected_line`
  - `source_of_truth`

旧 `dafunc` は function 単位の canonical metadata と query designer sub-resource を分けて保持する。
`single_proxy_auth_type` は `''` / `ProjectToken` / `GetFunc` / `ProjectTokenOrGetFunc` / `NoSecurity` / `Manual` / `LoginCookieToken` を許容する。
blank は legacy default として `ProjectToken` へ解決し、`GetFunc` / `ProjectTokenOrGetFunc` では `single_proxy_single_get_function_name` を必須にする。
この 2 項目は single-function proxy / endpoint preview 用の metadata であり、multi-step custom proxy の auth policy source of truth にはしない。
新実装でも、`single_proxy_*` は「direct API 公開」のための first-class metadata として扱う。
use-case を 1 function で素直に公開できるなら、まずこの metadata で表す。
現在の `admin:/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}` では、このテーブルに対して upsert を行い、`admin:/projects/{project_key}/db-access/{db_access_key}/functions/change-order` では保存済み row の `function_list_order` を一括更新する。
`admin:/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/move` では row を作り直さず、親 class を指す `db_access_class_id` だけを付け替えるため、query designer sub-resource は同じ function id に紐づいたまま残る。
bootstrap sync は `manual` / `seed-legacy` row を保持し、`detected_signature` / `detected_line` を追従させる。
`019_project_db_access_class_function_seed.sql` では `Project 1 (Mtool)` の canonical baseline として function 628 row を持つ。
`022_backfill_runtime_legacy_selectlist_sort_order_columns.sql` では `Project 1 (Mtool)` の legacy baseline に明示 order がある `SELECTLIST` だけを `sort_order_columns` として補完し、対象 function を `seed-legacy` へ寄せる。

### `project_db_access_function_source_output_targets`

- `project_db_access_functions` に紐づく single-function proxy target source output key 一覧
- 現段階では以下だけを持つ
  - `source_output_key`

旧 `dafuncSimpleProxySourceOutputTarget` の `ProjectSourceOutputPID` 参照を、そのまま FK では引き継がず、`source_output_key` 文字列として保持する。
これは source output 側の canonical key へ段階的に寄せるためであり、現時点では function detail 画面の checkbox 選択結果をこの形式で保存する。
この table は single-function proxy target assignment だけを持ち、multi-step custom proxy の target 保存先には使わない。
これは temporary table ではなく、`single` を `custom` と分けるための first-class binding table である。
UI も function detail / function setting 配下でこの table を編集する前提にする。
現行 artifact generator は `single-proxy-server` / `single-proxy-client` / `openapi-json` strategy でこの table を読み、default core seed の `PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` / `DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` について actual build まで接続済みである。
2026-05-25 時点の default `MTOOL` source output catalog は `runtime=1` / `custom-proxy=2` / `single-function-proxy=4` として扱う。function detail 画面では `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` を single-function target 候補として出さず、legacy simple proxy row を誤って current custom proxy output へ backfill しないようにしている。optional sample/test seed を追加適用した場合だけ、`single-function-proxy` はさらに `SAMPLE-SINGLE-PROXY-*` の 2 件が増えて合計 6 件になる。
`sync_project_db_access.php` は imported canonical-bootstrap function を初回 insert するとき、generic single-function outputs が存在すれば `DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` へ default assignment する。
`Project 1 (Mtool)` の legacy row は `17` 件あり、内訳は `ProjectSourceOutput.PID=28` (`/proxy_paypal`) が `16` 件、`PID=117` (`/proxy_uploader`) が `1` 件である。
ただし今回の core scope では `ApacheHostSetting` 8 件は out of scope とし、残りの `Project` 6 件、`PaypalSubscription` 1 件、`DropboxUploadToken` 1 件を canonical function name へ remap して `PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` へ seed backfill する。
`Project.GetProjectbyOwnerOrUserSecurityList` は project discovery 用の custom proxy `DB-GETPROJECTLIST` でも `DBIMPORT-PROXY-*` へ載るが、legacy simple proxy endpoint surface も保持するため single-function proxy 側にも残す。

### `project_custom_proxies`

- `projects` に紐づく multi-step Custom Proxy の canonical metadata
- 現段階では以下を持つ
  - `custom_proxy_key`
  - `basename`
  - `name`
  - `in_transaction`
  - `auth_type`
  - `single_get_function_name`
  - `continue_even_if_failed_to_insert`
  - `notes`
  - `source_of_truth`

旧 `daCustomProxy` をそのまま PID 参照で再現せず、route と保存キーには `custom_proxy_key` を使う。
これは legacy `basename` / `name` が project 内で一意キーにならない可能性があるためで、表示名は別に保持する。
`auth_type` / `single_get_function_name` は multi-step custom proxy 自体の auth policy source of truth であり、`project_db_access_functions.single_proxy_*` とは同じ enum を共有するが責務は分ける。
この table は `single-function proxy` の代替物ではなく、あくまで advanced composition model の保存先である。
1 function をそのまま公開したいだけなら、こちらではなく `project_db_access_functions` と `project_db_access_function_source_output_targets` を使う。
現在の `admin:/projects/{project_key}/proxy/custom` と `/{custom_proxy_key}` では、このテーブルに対して一覧、追加、更新、削除を行う。
初期 seed では `Project 1 (Mtool)` 由来の `DB::Import`、`DB::GetTableDefinition`、`DB::GetColumnDefinition`、`DB::GetProjectList` を取り込む。

### `project_custom_proxy_steps`

- `project_custom_proxies` に紐づく custom proxy step definition
- 現段階では以下を持つ
  - `db_access_source_name`
  - `db_access_function_name`
  - `is_list`
  - `step_order`
  - `notes`
  - `source_of_truth`

旧 `daCustomProxyFunc` のうち、build / runtime が意味として使う最小項目だけを保持する。
`AddIndentCount` と `AddIndentType` は、生成テキストのレイアウト情報なので新実装へは持ち込まない。
technical には 1 step の custom proxy も表現できるが、それを `single-function proxy` の置き換えとして常用しない。
`single` と `custom` は user-facing model と metadata の段階で分けておく。
現在の `admin:/projects/{project_key}/proxy/custom/{custom_proxy_key}/functions` では、このテーブルに対して一覧、追加、更新、削除を行う。
初期 seed では `Project 1 (Mtool)` 由来の step を、legacy `dafuncPID` から `db_access_source_name` / `db_access_function_name` へ解決して取り込む。

### `project_custom_proxy_source_output_targets`

- `project_custom_proxies` に紐づく target source output key 一覧
- 現段階では以下だけを持つ
  - `source_output_key`

旧 `daCustomProxySourceOutputTarget` の `ProjectSourceOutputPID` 参照を、そのまま FK では引き継がず、`source_output_key` 文字列として保持する。
これは source output 側の canonical key へ段階的に寄せるためであり、現時点では detail 画面の checkbox 選択結果と初期 seed をこの形式で保存する。
`Project 1 (Mtool)` の legacy target PID 300/301 は、`DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` へ再マップして取り込み済みであり、seed 済みの 4 proxy はそれぞれ両方の target を持つ。

DB Access query designer の初期値は `020_project_db_access_designer_seed.sql` で投入する。
この seed は `Project 1 (Mtool)` の legacy row を canonical `source_name` / `function_name` へ解決した結果だけを `source_of_truth=seed-legacy` で保存し、runtime は canonical table だけを見る。
旧 dump は seed export 時の temporary input としてだけ使い、product / runtime からは参照しない。

### `project_db_access_function_select_wheres`

- `project_db_access_functions` に紐づく select where designer の canonical metadata
- 現段階では以下を持つ
  - `target_table_name`
  - `target_table_alias_name`
  - `target_table_column_name`
  - `parameter_type`
  - `parameter_data_type`
  - `fixed_parameter`
  - `another_table_name`
  - `another_table_alias_name`
  - `another_field_name`
  - `join_type`
  - `or_group`
  - `relational_operator`
  - `where_order`
  - `source_of_truth`

旧 `dafuncselectwhere` の item 単位 metadata を保存する。
現在の `admin:/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-where` では、このテーブルに対して一覧、追加、更新、削除を行う。
`Project 1 (Mtool)` では `020_project_db_access_designer_seed.sql` により 609 row が初期投入される。

### `project_db_access_function_select_target_fields`

- `project_db_access_functions` に紐づく select target fields designer の canonical metadata
- 現段階では以下を持つ
  - `target_table_name`
  - `target_table_alias_name`
  - `target_table_column_name`
  - `target_table_column_prefix`
  - `target_table_column_suffix`
  - `store_class_field_name`
  - `group_by_target`
  - `field_list_order`
  - `source_of_truth`

旧 `dafuncselecttargetfields` の item 単位 metadata を保存する。
現在の `admin:/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-target-fields` では、このテーブルに対して一覧、追加、更新、削除を行う。
`Project 1 (Mtool)` では `020_project_db_access_designer_seed.sql` により 2110 row が初期投入される。

### `project_db_access_function_select_havings`

- `project_db_access_functions` に紐づく select having designer の canonical metadata
- 現段階では以下を持つ
  - `left_target_prefix`
  - `left_target_field_id`
  - `left_target_suffix`
  - `relational_operator`
  - `right_target_prefix`
  - `right_parameter_type`
  - `right_parameter_data_type`
  - `right_fixed_parameter`
  - `right_target_field_id`
  - `right_target_suffix`
  - `having_order`
  - `source_of_truth`

旧 `dafuncselecthaving` の item 単位 metadata を保存する。
現在の `admin:/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/select-having` では、このテーブルに対して一覧、追加、更新、削除を行う。
`Project 1 (Mtool)` では legacy row が 0 件だったため、seed file は stale `seed-legacy` cleanup だけを行う。

### `project_db_access_function_insert_target_fields`

- `project_db_access_functions` に紐づく insert target fields designer の canonical metadata
- 現段階では以下を持つ
  - `target_table_column_name`
  - `parameter_type`
  - `parameter_data_type`
  - `fixed_parameter`
  - `field_list_order`
  - `source_of_truth`

旧 `dafuncinserttargetfields` の item 単位 metadata を保存する。
現在の `admin:/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/insert-target-fields` では、このテーブルに対して一覧、追加、更新、削除を行う。
`Project 1 (Mtool)` では `020_project_db_access_designer_seed.sql` により 567 row が初期投入される。

### `project_db_access_function_update_target_fields`

- `project_db_access_functions` に紐づく update target fields designer の canonical metadata
- 現段階では以下を持つ
  - `target_table_column_name`
  - `parameter_type`
  - `parameter_data_type`
  - `fixed_parameter`
  - `field_list_order`
  - `source_of_truth`

旧 `dafuncupdatetargetfields` の item 単位 metadata を保存する。
現在の `admin:/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-target-fields` では、このテーブルに対して一覧、追加、更新、削除を行う。
`Project 1 (Mtool)` では `020_project_db_access_designer_seed.sql` により 460 row が初期投入される。

### `project_db_access_function_update_delete_wheres`

- `project_db_access_functions` に紐づく update/delete where designer の canonical metadata
- 現段階では以下を持つ
  - `target_table_column_name`
  - `parameter_type`
  - `parameter_data_type`
  - `fixed_parameter`
  - `or_group`
  - `relational_operator`
  - `where_order`
  - `source_of_truth`

旧 `dafuncupdatedeletewhere` の item 単位 metadata を保存する。
現在の `admin:/projects/{project_key}/db-access/{db_access_key}/functions/{function_key}/update-delete-where` では、このテーブルに対して一覧、追加、更新、削除を行う。
`Project 1 (Mtool)` では `020_project_db_access_designer_seed.sql` により 453 row が初期投入される。

## `db-lab`

`db-lab` は experiment / runtime 用 DB として扱う。

### `lab_experiments`

- canonical な `Project` 自体は持たず、`project_key` で参照する
- 実験や比較の単位を表す
- 現段階では以下を持つ
  - `experiment_key`
  - `project_key`
  - `name`
  - `execution_status`
  - `runtime_target`
  - `executed_by`
  - `notes`

新規 `Experiment` 作成時は、`executed_by` に現在のログインユーザーを入れる。
現在の `lab:/experiments` では、このテーブルに対して一覧、追加、更新を行う。

### Compare Output job history

- Compare Output の実行ジョブ履歴は、現時点では `db-lab` のテーブルを持たない。
- `lab:/runs/compare-output/{project_key}` の実行時、および `mtool/scripts/create_compare_output.php` 実行時に file-based job を生成する。
- 保存先は `work/job-history/compare-output/{project_key}/{job_key}/` で、少なくとも次を持つ。
  - `manifest.json`
  - `output/` 配下の snapshot file
- `lab:/runs/compare-output/{job_key}` と `/api/runs/compare-output/{job_key}` は、この file-based manifest を read model として扱う。

## 旧実装との対応

### いま取り込んだもの

- `Project`
  - 最小の設定系 project 定義として `projects` に反映
- `ProjectUser`
  - 最小の membership として `project_memberships` に反映
- `da`
  - class 単位の一部 metadata を `project_db_access_classes` に反映
- `dafunc`
  - function 単位の一部 metadata を `project_db_access_functions` に反映
- `daCustomProxy`
  - custom proxy 単位の metadata を `project_custom_proxies` に反映
- `daCustomProxyFunc`
  - custom proxy step 単位の metadata を `project_custom_proxy_steps` に反映
- `daCustomProxySourceOutputTarget`
  - target source output key の受け皿として `project_custom_proxy_source_output_targets` を用意
  - `Project 1 (Mtool)` の legacy target PID 300/301 は `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` へ再マップ済み
- `dafuncselectwhere`
  - select where 単位の metadata を `project_db_access_function_select_wheres` に反映
- `dafuncselecttargetfields`
  - select target fields 単位の metadata を `project_db_access_function_select_target_fields` に反映
- `dafuncselecthaving`
  - select having 単位の metadata を `project_db_access_function_select_havings` に反映
- `dafuncinserttargetfields`
  - insert target fields 単位の metadata を `project_db_access_function_insert_target_fields` に反映
- `dafuncupdatetargetfields`
  - update target fields 単位の metadata を `project_db_access_function_update_target_fields` に反映
- `dafuncupdatedeletewhere`
  - update/delete where 単位の metadata を `project_db_access_function_update_delete_wheres` に反映
- `ProjectSourceOutput`
  - canonical definition を `project_source_outputs` に反映
  - ただし saved files / build token などの周辺テーブルはまだ未反映
- `CompareOutput`
  - canonical definition を `project_compare_outputs` に反映
- `CompareOutputAdditionalPath`
  - canonical definition を `project_compare_output_additional_paths` に反映
- Compare Output template / ignore rule asset
  - file-based asset override として `work/compare-output-assets/` に反映
- Compare Output 実行ジョブ履歴
  - file-based manifest と snapshot として `work/job-history/compare-output/` に反映

### まだ取り込んでいないもの

- DB 接続詳細
- Project 1 single proxy remap の `ApacheHostSetting` 8 件
- project 固有 auth 実装を wrapper / collaborator 境界へ寄せる作業
- Build 実行設定と結果履歴
- Compare Output job history の DB 化
- Source Output の saved files / build token 周辺
- generated runtime layer の source metadata 全量
- Project 1 以外の query designer migration / refresh 手順
- ページ単位セキュリティ
- 自動プロビジョニング状態
- 外部ストレージやアップロード先

## 初期化方法

- 初回 `docker compose up` 時は `docker/mariadb/config-initdb/` と `docker/mariadb/lab-initdb/` の SQL が自動適用される。
- sample/test 用の config seed は `sample/<category>/<pack>/seed/` 配下で別管理し、default initdb では自動適用しない。
- 既存 volume がある場合は自動適用されないため、必要に応じて手動反映または volume 再作成が必要になる。

## 次段で追加するもの

- `project_memberships` に対する role / permission 評価
- `project_memberships` の管理 UI
- `projects` / `lab_experiments` の read / write を分ける認可
- `ProjectSourceOutput` artifact の publish / snapshot 境界
- `dbclasses` 相当の generated runtime layer を支える metadata の拡張
- 旧 `data-*.php` / `dbaccess-*.php` basename を踏襲した naming rule

## generated runtime layer の扱い

- 旧実装では `original-codes/mtool_lib/dbclasses/` の generated class 群を、アプリ自身が runtime dependency として読み込んでいた。
- 新実装でも、この考え方は維持する。
- ただし現時点では DB 設計データ Export が未投入のため、canonical metadata からの再生成はまだ始められない。
- そのため初期段階では、旧 `dbclasses` のコピー利用を bootstrap として許容する。
- この段階で固定するのは path ではなく basename 互換である。
  - `data-<Entity>.php`
  - `dbaccess-<Entity>.php`
  - `autoload_mtool.php`
- metadata export が揃った後に、`ProjectSourceOutput` と generator を拡張し、新システムが自分の runtime code を再び自分で作る状態へ戻す。
- 現時点では `project_source_outputs` に canonical definition を置き、artifact 本体は `work/artifacts/source-outputs/` に保存する hybrid 段階である。
