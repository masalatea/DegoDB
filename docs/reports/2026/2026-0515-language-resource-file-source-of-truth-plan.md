# 2026-05-15 Language Resource File Source of Truth Plan

## Status

- core cutover: `DONE`
- status updated at: `2026-05-27`
- completion basis:
  - `mtool/resources/` file tree
  - runtime / admin / generator の file-canonical read path
  - `project_language_resource_*` table retirement
  - readiness check `ready=true`
- note:
  - source-of-truth 切替と tableless 化は完了した。
  - 残るのは migration/debug bridge をどこまで削るかという cleanup であり、この plan の core DONE とは分けて扱う。

## 方針決定

- `LanguageResource` の最終 source of truth は repo 配下の JSON file tree とする。
- 最終状態では `LanguageResource` 用 DB table は持たない。永続データも持たない。
- `project_language_resource_*` と旧 `LanguageResource*` は移行ブリッジであり、最終設計には含めない。
- AI と人が同じ data を編集できるよう、code と data を分離した UTF-8 JSON を正本にする。
- 旧ツールの `Lang` 編集画面は再実装しない。`LanguageResource` 編集は AI / 人が file を直接編集する前提に置く。

## 現在地

- `mtool/resources/MTOOL/` に file tree を配置済み。
- `manifest.json` / `groups/*/group.json` / `groups/*/resources/*.json` の schema draft は実装済み。
- export / validate / sync script は追加済み。
- file tree の validate は通る状態にある。
- runtime と current admin の read path は `file-canonical -> reference -> empty` の loader に切り替わり、DB canonical を通らず file tree を primary source として読む。
- current admin の `LanguageResource` 画面は inspector-only 化済みで、create / update / move / delete / additional-group edit / group edit の current write path は削除済み。
- runtime/generator/wrapper が読む catalog helper を `mtool/app/project_language_resource_catalog_loader.php` へ分離し、DB canonical bridge helper は `mtool/scripts/debug/language_resource/lib/project_language_resource_db_bridge.php` へ寄せた。
- generated `HTML-DB` の legacy `lang_res*.php` write entry は、current write route へ流さず `bridge_errors` 付きで inspector へ着地させ、`group.json` / `resource.json` の直接編集へ案内する形に切り替えた。
- `mtool/app/project_output_html_module_generator.php` と generated wrapper の LanguageResource lookup も `app_fetch_project_language_resource_catalog()` 経由に統一されており、runtime loader の file-only cutover 後は DB canonical を読まない。
- 旧 `mtool/app/project_language_resource_repository.php` から未使用の DB runtime reader / bootstrap helper と DB write CRUD 群を削除し、残る DB bridge 実装は `mtool/scripts/debug/language_resource/lib/project_language_resource_db_bridge.php` へ退避した。その後、互換 shim 自体も削除した。
- DB canonical sync は `mtool/scripts/debug/language_resource/lib/project_language_resource_sync_service.php` と `mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php` に隔離し、migration / debug 用の bridge に下げた。旧 top-level path は compatibility wrapper としてだけ残す。
- `mtool/scripts/check_language_resource_db_retirement_readiness.php` を追加し、`MTOOL` の file catalog / runtime loader / generator / published `HTML-DB` wrapper / DB row 残数を一括で確認できるようにした。
- 2026-05-15 の初回 readiness check では code path 側は pass し、blocking failure は DB row 残存だけだった。`config_app` の `project_language_resource_*` 7 table に合計 21616 row が残っており、内訳は `bootstrap-reference` 21613 row と `manual` 3 row (`resources` 1 / `captions` 2) である。
- `mtool/scripts/debug/language_resource/inspect_language_resource_db_residual_rows.php` で `manual` 3 row を棚卸しし、`ACTION_ADDED_APACHE_HOST_SETTING` の resource 1 件と caption 2 件だけが残っていることを確認した。caption 2 件は file canonical と exact-match、resource 1 件の差分は dead field とみなせる `key_for_update` だけだった。
- `mtool/scripts/debug/language_resource/retire_project_language_resource_db_rows.php` を追加し、dry-run で `safe_to_clear_project_rows=true` を確認後、`MTOOL` の project-scoped canonical row を全削除した。削除件数は `captions=20233`, `group_source_outputs=10`, `group_languages=308`, `resources=1007`, `groups=7`, `languages=51` で、削除後 row count は全 table 0 になった。
- `mtool/scripts/debug/language_resource/drop_project_language_resource_db_tables.php` を追加し、all-empty dry-run の後に local `config_app` から `project_language_resource_*` 7 table 自体を drop した。drop 後の確認では `all_tables_absent=true` になっている。
- `docker/mariadb/config-initdb/027_language_resource_metadata.sql` は no-op placeholder に置き換え、新規 DB bootstrap でも `project_language_resource_*` table を作らない状態にした。
- 未使用だった `mtool/app/project_language_resource_repository.php` 互換 shim は削除し、DB canonical sync helper も `mtool/app/project_language_resource_sync_service.php` から `mtool/scripts/debug/language_resource/lib/project_language_resource_sync_service.php` へ移して、LanguageResource DB bridge 参照を scripts/debug path に閉じた。
- allowlist を更新した readiness check を再実行し、2026-05-15 時点で `ready=true` を確認した。現在の DB 側残存物はなく、data も schema も `LanguageResource` 専用 table は 0 である。
- `MTOOL` に加えて `SAMPLE2` / `SAMPLE4` / `SAMPLE6` も legacy reference JSON と file tree へ展開した。reference は `mtool/reference/sample{2,4,6}-legacy-language-resource-catalog.json`、正本 file tree は `mtool/resources/SAMPLE{2,4,6}/` にある。
- overlay seed parser は `MTOOL` の `INSERT ... SELECT ... UNION ALL ...` 形式だけでなく、sample pack の `INSERT INTO ... VALUES (...)` 形式も解釈できるようにした。`notes` に含まれる legacy `ProjectSourceOutputPID` 表記も `ProjectSourceOutput.PID = 123` と `ProjectSourceOutput PID 123` の両方を受ける。
- sample export の検証では `SAMPLE4` / `SAMPLE6` は warning 0 で、`resolved_source_output_key_count=2` になった。`SAMPLE2` も seed に不足していた legacy `ProjectSourceOutputPID` `273` / `331` / `339` / `360` / `368` を current metadata へ起こしたことで warning 0 になり、`resolved_source_output_key_count=9` になった。
- `SAMPLE2` では従来どおり caption orphan 60 row を normalization で落としている。理由はすべて `missing_resource` であり、raw reference count `23907` に対して canonical caption count は `23847` である。
- `mtool/scripts/validate_language_resource_file_tree.php --all` を追加し、`mtool/resources/*` 配下の全 file tree を一括 validate できるようにした。`make mtool-lang-res-file-tree-check` もこの入口である。
- `mtool/scripts/export_language_resource_file_tree.php --all --clean` を追加し、legacy reference を持つ全 project (`MTOOL` / `SAMPLE2` / `SAMPLE4` / `SAMPLE6`) の file tree を一括再生成できるようにした。`make mtool-lang-res-file-tree-export` もこの入口である。
- `make mtool-lang-res-file-tree-export` と `make mtool-lang-res-file-tree-check` を 2026-05-15 に順番に再実行し、4 project 全件 success を確認した。直前に出ていた missing-file / invalid-json は `export --clean` と validate の並列実行 race であり、canonical file tree の不整合ではなかった。
- `mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php` の help と JSON 出力は `debug-db-bridge` 専用であることを明示する形に寄せた。tableless な通常運用では validate/export が主系であり、DB sync CLI は migration/debug 専用である。旧 top-level path は compatibility wrapper としてだけ残す。
- `lang_res_auto_translate_ajax.php` generated wrapper は current auto-translate endpoint へ handoff せず、legacy JSON 互換の NG response で file workflow を案内する read-only bridge に寄せた。current app の `/projects/{project_key}/language-resources/auto-translate` route と translation service は削除した。
- `make mtool-html-db-lang-res-wrapper-check` を再実行し、最新 `HTML-DB` artifact を rebuild/publish した直後の published docroot smoke が pass することを確認した。`lang_res_auto_translate_ajax.php` は `status=NG` / 空の `Provider` / `ProviderCaption` を返しつつ、file workflow 案内メッセージを返す。

## 結論

- 方向性としては、DB から `LanguageResource` を完全に外す方針でよい。
- `MTOOL` については data retirement に加えて local DB の table retirement まで完了した。runtime / current admin / generator / wrapper の source of truth は file tree に揃っている。
- sample pack のうち実データを持つ `SAMPLE2` / `SAMPLE4` / `SAMPLE6` も file tree export まで完了し、source output binding warning も 0 にできた。次の実装 slice は `DB cache を維持すること` ではなく、`migration/debug bridge をどこまで残すか` と `次の pilot project へ同じ tableless 方針を広げること` に置く。

## 最終形

- 正本は `mtool/resources/<PROJECT_KEY>/` 配下の JSON file tree。
- runtime read は file catalog を直接読む。
- `LanguageResource` 編集は current admin では行わず、AI / 人が JSON file を直接更新する。
- auto translate が必要なら file 編集 workflow の中で扱い、current admin の Lang editor は前提にしない。
- generator / artifact build は file catalog を直接入力にする。
- 旧 consumer 向け artifact は file source of truth から生成する。
- DB には `LanguageResource` 系 table / cache / snapshot を残さない。
- current admin に残すなら read-only inspector のみとし、writer は持たない。

## JSON を正本にする理由

- diff が読みやすい。
- validator を書きやすい。
- AI edit で壊れたときに parse error と schema error を拾いやすい。
- YAML より曖昧さが少ない。
- PHP array のように code execution と data が混ざらない。

## file contract

### `manifest.json`

- schema version
- project key
- default locale
- locale metadata
- enabled output packs
- current file-tree counts
- normalization metadata

### `groups/<group_key>/group.json`

- stable `group_key`
- legacy pid
- display name
- function / filename hints
- enabled locale list
- source output bindings
- sort order

### `groups/<group_key>/resources/<resource_key>.json`

- stable `resource_key`
- legacy pid
- base group
- additional groups
- sort metadata
- per-locale captions
- auto-translated draft

## canonical key rule

- resource は既存 `resource_key` を canonical key にする。
- group は display name ではなく immutable `group_key` を持つ。
- 初期移行では `grp-<legacy_group_pid>-<slug>` を使う。
- language は locale を canonical key にし、旧 `lang_for_google` / `lang_for_ios` などは locale metadata に寄せる。

## normalization rule

- legacy DB にある orphan row は file canonical に持ち込まない。
- `manifest.normalization` に dropped 件数を保持し、正規化の結果を明示する。
- file tree counts を canonical counts とみなし、raw legacy row count は参考情報に下げる。

## 移行中の DB の扱い

- `project_language_resource_*` は source of truth ではない。
- 使うとしても一時的な比較対象、退避先、削除前確認用に限る。
- 新規機能を DB canonical 前提で増やさない。
- `manual` row は最終的に file に吸い上げて解消する。
- 「DB にしかない編集結果」を将来に残さない。
- `LanguageResource` 用の新しい admin write path は作らない。

## 実装フェーズ

### [DONE] Phase 0: Freeze

- file-only を最終設計として固定する。
- DB canonical 拡張を止める。
- overlay 分離は維持し、`LanguageResource` を core default 必須に戻さない。

### [DONE] Phase 1: File tree bootstrap

- JSON layout を固定する。
- `MTOOL` catalog を file tree 化する。
- validator / export を整備する。
- orphan row の扱いを normalization metadata に落とす。

### [DONE] Phase 2: Read cutover

- runtime の `app_fetch_project_language_resource_catalog()` を `file-canonical -> reference -> empty` に切り替える。
- DB table が存在しても current admin / runtime の表示は DB canonical を読まない。
- UI 上の source 表示も `file-canonical` / `reference` / `empty` を主表示にする。
- DB compare は sync CLI / migration preview に限定する。

### [DONE] Phase 3: Editor retirement

- current admin の `LanguageResource` write 前提を外す。
- create / update / move / delete / group edit / additional-group edit の導線は read-only 化または撤去対象にする。
- `LanguageResource` 編集は repo file 直接更新へ切り替える。
- この段階で新規 `manual` DB row の発生を止める。

### [DONE] Phase 4: Generator cutover

- generator / output build を file catalog 直読に切り替える。
- legacy artifact は file tree から再生成する。
- pilot pack で snapshot diff を取り、出力 parity を確認する。

### [DONE] Phase 5: DB retirement

- sync script をメイン導線から外す。
- `project_language_resource_*` 依存コードを削除する。
- 旧 `LanguageResource*` import/export 依存を必要最小限まで縮小する。
- 最後に `LanguageResource` 系 table と data を drop する。

## table drop の判定条件

- 以下が揃うまでは DB table を消さない。
- read path が file-first ではなく、file-only になっている。
- current admin の編集系 route が不要化または無効化されている。
- generator / artifact build が file catalog 直読になっている。
- `manual` DB row が file へ吸収済みである。
- sync script を止めても運用が回る。

## 2026-05-15 readiness check

- 実行コマンド:
  `php mtool/scripts/check_language_resource_db_retirement_readiness.php --project-key=MTOOL --docroot=<repo-root>/work/source-outputs/MTOOL/HTML-DB --db-host=127.0.0.1 --db-port=33061 --db-name=config_app --db-user=config_app --db-password=config_app_local_2026 --config-db-host=127.0.0.1 --config-db-port=33061 --config-db-name=config_app --config-db-user=config_app --config-db-password=config_app_local_2026`
- pass:
  - `file_catalog_exists`
  - `file_catalog_loads`
  - `runtime_loader_source_allowed`
  - `runtime_loader_source_file_canonical`
  - `current_admin_editor_retired`
  - `loader_free_of_db_runtime`
  - `expected_live_loader_consumers_present`
  - `db_bridge_references_isolated`
  - `repository_shim_unused`
  - `generator_uses_loader`
  - `runtime_wrappers_use_loader_bridge`
- 含意:
  - 2026-05-15 の最終再実行では blocking fail は 0 件となり、`ready=true` になった。
  - `db_tables_absent_or_empty` も pass し、local `config_app` では `project_language_resource_*` table 自体が存在しない状態でも問題ないことを確認した。
  - readiness CLI 上では、file canonical / inspector-only / generator / wrapper / tableless DB の条件を満たした。

## 直近の作業順

1. `MTOOL` と `SAMPLE2` / `SAMPLE4` / `SAMPLE6` に続く pilot project へ同じ file-only cutover と tableless retirement を適用する。
2. `mtool/scripts/debug/language_resource/` に寄せた compatibility wrapper を最終的にどこまで残すか整理する。
3. `project_language_resource_db_bridge.php` 依存 script を最終的にどこまで削るか整理する。
4. migration/debug 期間が終わったら `project_language_resource_*` 前提コードと script を削る。

## 「今 DB から消せるか」の判断

- 最終方針としては「消す」で正しい。
- data については、2026-05-15 時点で `MTOOL` の canonical row を全削除済みであり、もう空である。
- schema についても、local `config_app` では `project_language_resource_*` table を drop 済みであり、core initdb でも新規作成しない。
- したがって判断としては「DB data も table も、もう消せた。残る論点は bridge/debug code をいつ消すか」である。

## スコープ

- migration 対象はまず `MTOOL` と sample に限定する。
- 既存 `LanguageResource` を多言語向け file tree に変換できれば、移行タスクとしては一区切りとする。
- 各 user project の多言語対応は、その user が file を自由に編集する前提にする。
- そのため generic な `LanguageResource` admin editor を current tool に残す理由は薄い。

## open questions

- read-only inspector をどこまで残すか。
- key order を formatter でどこまで固定するか。
- caption を今の resource file 内包で固定するか、将来 locale 分割を許すか。
- `project_language_resource_db_bridge.php` と関連 migration/debug script をいつ完全削除するか。
