# Legacy/New DB Mapping / 旧・新 DB 対応リスト

English companion:
This mapping list tracks how legacy runtime sources relate to the current canonical tables and future destinations. Use it when deciding whether a legacy source already has an upstream match, still needs classification, or should stay outside the current canonical model.

## 目的

- 旧 runtime source 名と、現行 canonical table / 将来の受け先を対応付ける。
- 段階移行の過程で、どの source をどこへ持っていくかを固定する。
- 「まだ未実装」なのか、「意図的に derived として残す」のかを明確にし、機能漏れを防ぐ。

## 判断原則

- この表は downstream 差分を直接直すための表ではない。
- まず `DB Import -> Data Class -> DB Access` の順で upstream 一致を取る。
- そのあとも残る差分だけを `cache` / `run-state` / `derived` として分類する。
- したがって、Section 2 以降の source をいきなり generator 側で局所補修しない。
- 同じ設計を同じ層で比較している限り、説明不能な差分は残らない前提で扱う。
- 一致の基準は旧実装の無条件コピーではない。新しい canonical 設計へ寄せた方がよい箇所は寄せる。
- ただし、その場合も `mode` と `notes` で「何を split / merge / normalize したか」「どう旧機能を吸収したか」を説明できるようにする。

## 記号

- `legacy kind`
  - `physical`: legacy physical table 起点
  - `cache`: cache / runtime state table
  - `derived`: join DTO / helper DTO / import helper
- `mode`
  - `direct`: ほぼそのまま対応
  - `renamed`: 名前や key は変わるが意味は近い
  - `split`: 旧 1 table の責務を複数の新 table / module へ分ける
  - `merged`: 旧複数の責務を 1 つの新 table へまとめる
  - `derived`: physical table にはせず query / helper で表す
  - `pending`: まだ受け先未実装
- `status`
  - `done`: 受け先があり、現行 canonical で扱えている
  - `partial`: 受け先はあるが field / semantic 差分が残る
  - `missing`: 受け先未実装
  - `intentional`: physical table にはしない前提

補足:

- `partial` は必ずしも悪い状態ではない。新設計への intentional redesign が入っている場合も含む。
- ただし `partial` のまま放置せず、差分理由が metadata 不足なのか intentional improvement なのかを明記する。

## 1. 上流一致を先に取る領域

| Legacy source | Legacy kind | Current canonical target | Mode | Status | Notes |
| --- | --- | --- | --- | --- | --- |
| `Project` | physical | `projects` | split | partial | 旧 `StorageType` / `DBType` / `DBUserPID` / host / security 系は将来別 module へ分離。現行は `project_key` / `slug` / `lifecycle_status` を追加済み。 |
| `ProjectUser` | physical | `project_memberships` | merged | partial | 旧 per-feature 権限 bit は未再現。現行は `role_code` + `can_administer` へ正規化。 |
| `ProjectSecurityForEachPage` | physical | `project_page_security_policies` | renamed | partial | `SERVER_NAME + SCRIPT_NAME` の row を project 配下の landing zone として保持。最終的な route policy 連携は後段。 |
| `ProjectSecurityForEachPageDetails` | physical | `project_page_security_policy_capabilities` | renamed | partial | 旧 `SecurityType` は 16 列へ戻さず normalized capability list として保持。 |
| `ProjectHostSetting` | physical | `project_host_assignments` | renamed | partial | Phase 1 では visible 4 列を denormalized row として保持し、後段で infra catalog へ split する。 |
| `html` | physical | `project_html_definitions` | renamed | partial | project-scoped live HTML row は current canonical table を正本にした。MTOOL は copied legacy reference から bootstrap し、`legacy_html_pid` / `html_key` を保持する。 |
| `htmlParameter` | physical | `project_html_parameters` | renamed | partial | project-scoped live HTML parameter row は current canonical table を正本にした。project HTML detail/parameter editor も current route で更新できる。 |
| `htmlTemplate` | physical | `html_templates` | renamed | partial | global template metadata は current canonical table を正本にした。legacy table が無い環境でも copied MTOOL reference から bootstrap できる。 |
| `htmlTemplateParameter` | physical | `html_template_parameters` | renamed | partial | global template parameter metadata は current canonical table を正本にした。project HTML parameter audit もこの canonical metadata を優先して使う。 |
| `dbtable` | physical | `dbtable` | direct | done | import/apply が動作。 |
| `dbtablecolumns` | physical | `dbtablecolumns` | direct | done | import/apply が動作。 |
| `dataclass` | physical | `dataclass` | direct | partial | table 自体はあるが、現行 sync は first-pass のため旧 semantic layer と完全一致していない。 Phase 2 の主対象。 |
| `dataclassfields` | physical | `dataclassfields` | direct | partial | 上記と同じ。 |
| `da` | physical | `project_db_access_classes` | renamed | partial | `PID` ベースから normalized `id` + `source_name` 管理へ変更。 Phase 3 の主対象。 |
| `dafunc` | physical | `project_db_access_functions` | renamed | partial | current promoted self-generated reference では `canonical_function_count=611`、`sql_regenerated_function_count=505`。 Phase 3 の主対象。 |
| `dafuncselectwhere` | physical | `project_db_access_function_select_wheres` | renamed | partial | normalized relation へ変更。 |
| `dafuncselecttargetfields` | physical | `project_db_access_function_select_target_fields` | renamed | partial | normalized relation へ変更。 |
| `dafuncselecthaving` | physical | `project_db_access_function_select_havings` | renamed | partial | target field 参照を normalized relation へ変更。 |
| `dafuncinserttargetfields` | physical | `project_db_access_function_insert_target_fields` | renamed | partial | normalized relation へ変更。 |
| `dafuncupdatetargetfields` | physical | `project_db_access_function_update_target_fields` | renamed | partial | normalized relation へ変更。 |
| `dafuncupdatedeletewhere` | physical | `project_db_access_function_update_delete_wheres` | renamed | partial | normalized relation へ変更。 |
| `ProjectSourceOutput` | physical | `project_source_outputs` | renamed | partial | `source_output_key` / `artifact_strategy` / `target_binding_type` を追加。`target_binding_type` は source output の用途区分を explicit metadata にした列で、未設定時だけ `artifact_strategy` / `class_type` heuristic に fallback する。旧 unit test / output path / client namespace 系の全 field は未吸収。上流一致後に split 先を詰める。 |
| `CompareOutput` | physical | `project_compare_outputs` | renamed | partial | compare 定義はあるが、旧 `DropboxBaseFolderPID` など周辺 infra との結合は未再現。まずは DB import / data class / db access 完了後に再評価する。 |
| `CompareOutputAdditionalPath` | physical | `project_compare_output_additional_paths` | renamed | partial | path key / order はあるが、旧 infra 依存は未再現。 |
| `daCustomProxy` | physical | `project_custom_proxies` | renamed | partial | auth / transaction / continue policy は保持。 `SingleGetFuncPID` は function name ベースへ移行中。 |
| `daCustomProxyFunc` | physical | `project_custom_proxy_steps` | renamed | partial | step 順序と list は保持。 `AddIndentCount` / `AddIndentType` は intentional に持ち込まない。 |
| `daCustomProxySourceOutputTarget` | physical | `project_custom_proxy_source_output_targets` | renamed | partial | 旧 `ProjectSourceOutputPID` は `source_output_key` ベースへ移行。 |

Section 1 の source は、個別 class の差分を見る前に upstream 一致を取る対象である。

## 2. 上流一致後に分類する領域

| Legacy source | Legacy kind | Current / future target | Mode | Status | Notes |
| --- | --- | --- | --- | --- | --- |
| `CompareOutputSearchCache` | cache | compare-output cache module | pending | partial | `legacy-reference-build-run-state` で `dbtable` / `dbtablecolumns` へ import 済み。`CheckedDT` 差分は downstream 補修ではなく upstream module 追加で吸う。 |
| `CompareOutputSearchCacheHint` | cache | compare-output cache module | pending | partial | `CompareOutputSearchCache` と同じ slice で `dbtable` / `dbtablecolumns` へ import 済み。 |
| `ProjectSourceOutputSavedFiles` | cache | build artifact / saved-file module | pending | partial | `legacy-reference-build-run-state` で `dbtable` / `dbtablecolumns` へ import 済み。 project definition table と同居させない可能性が高い。 upstream 整合後に run/job domain かどうかを判断する。 |
| `UploadDropboxPathCache` | cache | upload cache module | pending | partial | `legacy-reference-build-run-state` で `dbtable` / `dbtablecolumns` へ import 済み。`PID` / `CreatedTimestamp` を含む cache 管理で、upstream 整合後に扱う。 |
| `UploadDropboxPathCacheItems` | cache | upload cache module | pending | partial | 上記 parent cache の child。`legacy-reference-build-run-state` で import 済み。 |
| `TestCondition` | physical | future `test_conditions` 相当 | pending | partial | `legacy-reference-test-module` で `dbtable` / `dbtablecolumns` へ import 済み。現状の runtime mismatch に見える `ConditionOrder` は first-pass canonical 側だけのノイズで、test module を Data Class / DB Access へ通した後も残るなら metadata 不足と判断する。 |
| `TestPatternSelection` | physical | future `test_pattern_selections` 相当 | pending | partial | `legacy-reference-test-module` で `dbtable` / `dbtablecolumns` へ import 済み。`TestConditionPID` を持つため、Test module ごと canonical 化する。 |
| `dafuncSimpleProxySourceOutputTarget` | physical | `project_db_access_function_source_output_targets` | renamed | partial | 旧 `ProjectSourceOutputPID` は `source_output_key` ベースへ移行。metadata / UI / generator は canonical 化済みで、2026-05-12 時点の default `MTOOL` source outputs は `runtime=1` / `custom-proxy=2` / `single-function-proxy=2` として扱う。この `single-function-proxy` 2 件は `PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` の core definition で、legacy row のうち non-`ApacheHostSetting` な `Project` 6 / `PaypalSubscription` 1 / `DropboxUploadToken` 1 を remap 済みである。sample/test 用の `SAMPLE-SINGLE-PROXY-*` は `tests/scenarios/mtool-single-proxy/seed/` 配下で別管理し、default initdb には含めない。`DBIMPORT-PROXY-*` は引き続き simple proxy target 候補へ出さず、`ApacheHostSetting` 8 件は後段へ残す。 |
| `daCustomProxyFunc_leftouterjoin_dafunc_and_da` | derived | `project_custom_proxy_steps` + `project_db_access_functions` + `project_db_access_classes` から導出 | derived | intentional | `legacy-reference` import 対象ではなく、table import / data class sync の外側にある derived DTO だと確認済み。physical table に戻さず、query/helper/wrapper 側で表現する。legacy `AuthType` / `SingleGetFuncPID` は custom proxy 本体 metadata の責務として扱い、join DTO では保持しない。 |
| `dafuncselecthaving_leftouterjoin_targetfields` | derived | `project_db_access_function_select_havings` + `project_db_access_function_select_target_fields` から導出 | derived | intentional | join DTO。 physical import 対象ではない。 |

## 3. まだ current canonical に無い legacy physical source 群

以下は、今後も取り込み対象とみなす legacy physical source である。  
`missing` のまま放置せず、どの module へ受けるかを slice ごとに決める。

| Future canonical area | Legacy physical sources | Notes |
| --- | --- | --- |
| System / infra settings | `ApacheHostSetting`, `ApacheHostSettingTemplate`, `ApacheSetting`, `DBBackup`, `DBBackupUser`, `DBConnection`, `DBUser`, `DBUserClientHost`, `DropboxBaseFolder`, `DropboxBaseFolderUser`, `DropboxOauth2StatusHash`, `DropboxSetting`, `DropboxUploadToken`, `InternalUser`, `ProjectGroup`, `ProjectGroupTemplate`, `Server`, `SettingGroup`, `SettingGroupUser` | global 設定、DB 接続、Dropbox、host assignment、backup 系。 `Project` と `ProjectSourceOutput` の旧 field を受ける先にもなる。host assignment 自体の first-pass landing zone は `project_host_assignments` にあるが、infra catalog は未実装。 |
| Language Resource | `LanguageResource`, `LanguageResourceAdditionalGroupAssignment`, `LanguageResourceCaption`, `LanguageResourceGroup`, `LanguageResourceGroupLang`, `LanguageResourceGroupProjectSourceOutput`, `LanguageResourceLang` | 旧機能の大きな塊。 project/source-output との関連も持つ。 |
| Req / Spec / Minutes / Chat | `Req`, `Spec`, `SpecContent`, `minutes`, `chattopic`, `chattopicAttachment` | 現在の新実装には未着手。 feature module として別 slice が必要。 |
| Test | `Test`, `TestCondition`, `TestConditionSelection`, `TestGroup`, `TestPattern`, `TestPatternExecuteResult`, `TestPatternSelection` | `legacy-reference-test-module` により `dbtable` / `dbtablecolumns` への import は完了。次は Data Class / DB Access へ通す。 |
| Upload / deploy | `UploadGroup`, `UploadGroupAssignedServerPath`, `UploadGroupAssignedUser`, `UploadServer`, `UploadServerPath` | infra settings と密接に結び付く。 |
| Monitoring / status | `LastBuild`, `LiveCheckResult`, `LiveCheckResultSummaryForEachHour`, `LiveCheckTarget` | canonical project metadata ではなく run/status domain へ寄せる可能性が高い。 |
| Build / run state | `BuildLog`, `BuildSourceCache`, `BuildSourceFuncCache`, `BuildToken`, `BuildTokenCompletedItem`, `BuildTokenProjectSourceOutput`, `BuildTokenTemplateCache`, `ProjectSourceOutputSavedFiles`, `CompareOutputSearchCache`, `CompareOutputSearchCacheHint`, `UploadDropboxPathCache`, `UploadDropboxPathCacheItems` | `legacy-reference-build-run-state` により `dbtable` / `dbtablecolumns` への import は完了。 project definition とは別に `lab` / job / artifact domain として持つ候補。 |
| Misc feature | `PaypalSubscription`, `SpecialHoliday` | feature 単位で別 slice 化。 |

## 4. legacy derived / helper source 群

以下は「存在を消してよい」ではなく、「physical table としては持たない」前提の source である。

| Legacy source | Legacy kind | Future handling | Status |
| --- | --- | --- | --- |
| `Req_and_Project` | derived | `Req` + `Project` から query / view model で導出 | intentional |
| `TestGroup_leftouterjoin_Project` | derived | `TestGroup` + `Project` から query / view model で導出 | intentional |
| `Test_leftouterjoin_Project` | derived | `Test` + `Project` から query / view model で導出 | intentional |
| `chattopic_and_Project` | derived | `chattopic` + `Project` から query / view model で導出 | intentional |
| `html_leftouterjoin_htmlTemplate` | derived | `html` + `htmlTemplate` から query / view model で導出 | intentional |
| `htmlTemplate_leftouterjoin_ParentHtmlTemplate` | derived | `htmlTemplate` self join を query / helper で表現 | intentional |
| `htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate` | derived | `htmlTemplateParameter` + `htmlTemplate` から query / helper で表現 | intentional |
| `minutes_and_RelatedTables` | derived | `minutes` と関連 table 群から query / view model で導出 | intentional |
| `MySQLShowColumn` | derived | importer / connector collaborator として持つ | intentional |

## 5. 当面の実装順

1. Section 1 のうち `dbtable` / `dbtablecolumns` を旧設計と一致させる
2. Section 1 のうち `dataclass` / `dataclassfields` を import 結果から再構成する
3. Section 1 のうち `da` / `dafunc` / child metadata を canonical から再構成する
4. それでも残る Section 2 の source を `cache` / `run-state` / `module` として分類する
5. Section 3 の physical source を module 単位で取り込む
6. Section 4 の derived source は、physical table を増やさず query/helper/wrapper で再構成する

## 6. 運用ルール

- 新しい slice に着手する前に、この表を更新する
- 実装後は `status` を更新する
- `missing` の source を削除扱いにしない
- `intentional` は「不要」ではなく、「別レイヤーで表現する」と解釈する
- 上流一致が済む前に downstream generator へ暫定差分補修を積み増さない
