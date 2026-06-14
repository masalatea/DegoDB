# 2026-05-15 Language Resource HTML-DB Wrapper Currentization

## Status Note

- この report の後続変更で current auto-translate endpoint は削除され、`lang_res_auto_translate_ajax.php` は current route へ handoff せず file workflow を案内する read-only `NG` response を返すようになった。
- `make mtool-html-db-lang-res-wrapper-check` も現在は「最新 `HTML-DB` artifact を rebuild/publish してから published docroot を smoke」する target に更新済みである。
- 現在の確定方針は [2026-0515-language-resource-file-source-of-truth-plan.md](<repo-root>/docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md) を参照する。

## 概要

- generated `HTML-DB` の `LanguageResource` entry 群として、`lang_res.php` / `lang_res_list.php` / `lang_res_edit.php` / `lang_res_group_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` / `lang_res_auto_translate_ajax.php` を current route へ bridge する wrapper を追加した。
- `lang_res.php` は current groups summary へ、`lang_res_list.php` は current resources list へ landing させる。
- `lang_res_edit.php` は current detail inspector へ寄せ、legacy write entry は `bridge_errors` 付きで read-only 案内へ切り替えた。
- `lang_res_group_edit.php` は current groups inspector へ寄せ、legacy write entry は `group.json` 直接編集案内へ切り替えた。
- `lang_res_move.php` は current detail inspector へ寄せ、move 自体は `resource.json` 直接編集案内へ切り替えた。
- `lang_res_assign_additional_group.php` は current detail inspector へ寄せ、additional group 更新は `resource.json` 直接編集案内へ切り替えた。
- `lang_res_auto_translate_ajax.php` は current `POST /projects/{project_key}/language-resources/auto-translate` を内部実行し、response だけ legacy JSON 形式へ詰め替える。
- resource 系 wrapper (`lang_res_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php`) は、生成時 snapshot の静的 `legacy_resource_pid -> resource_key` map だけでなく、map miss 時に current catalog へ fallback lookup するようにした。current catalog loader 自体も後段で `file-canonical -> reference -> empty` に切り替わり、wrapper dynamic lookup は DB canonical を読まなくなった。

## 変更点

- generator
  - `mtool/app/project_output_html_module_generator.php`
  - `legacy_resource_pid -> resource_key` の context map を追加した。
  - `key_name -> { legacy_resource_pid, resource_key }` の context map も追加し、`PID_BY_KEYNAME` を current route 解決に使えるようにした。
  - runtime/generator 用の catalog helper を `mtool/app/project_language_resource_catalog_loader.php` へ分離し、generated wrapper の dynamic lookup も `project_language_resource_repository.php` ではなく loader を require するようにした。
  - generated entry wrapper target に `lang_res.php` / `lang_res_list.php` / `lang_res_edit.php` / `lang_res_group_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` / `lang_res_auto_translate_ajax.php` を追加した。
  - groups landing / resource list landing / resource edit / group edit / move / additional-group page / auto-translate ajax の wrapper text を追加した。
  - internal dispatch を持つ generated wrapper の current app bootstrap 探索は `APP_APP_ROOT` を優先し、未設定時でも `app/bootstrap.php` / `mtool/app/bootstrap.php` / legacy `shared/bootstrap.php` を順に探すようにした。
  - resource 系 wrapper は static map miss 時に current catalog (`app_fetch_project_language_resource_catalog`) を参照して `legacy_resource_pid` / `key_name` を引き直すようにした。
  - `lang_res_edit_include.php` / `lang_res_group_edit_include.php` / `lang_res_move_include.php` / `lang_res_assign_additional_group_include.php` / `lang_res_check_project_source_output_setting_lib.php` / `lang_res_select_resource_group_lib.php` も generated entry wrapper target に加え、generated root では `_legacy/` へ require する薄い wrapper に置き換えた。
- local smoke helper
  - `mtool/scripts/generated_html_db_dev_router.php`
  - generated HTML-DB docroot 配下の existing file request は generated wrapper/static file を返し、non-file request は current `mtool/app/http.php` へ委譲する local smoke 用 router を追加した。
  - `mtool/scripts/check_generated_html_db_language_resource_wrappers.php`
  - latest `HTML-DB` artifact 解決、host DB env default の補完、built-in server 起動、stub login、redirect follow、legacy JSON shape 検証、`--allow-mutate` 付きの group/resource create/update/move/additional-group/delete、repository cleanup をまとめた自動 smoke CLI を追加した。
  - `--publish` と `--docroot` を追加し、target artifact を current `source_output_dir` へ publish したうえで `work/source-outputs/...` docroot でも同じ smoke を再利用できるようにした。
- check entrypoint
  - `Makefile`
  - `mtool-html-db-lang-res-wrapper-check` target を追加し、`docker compose exec -T web-admin php /var/www/mtool/scripts/check_generated_html_db_language_resource_wrappers.php --publish` を 1 コマンドで再実行できるようにした。
- current page support
  - `mtool/app/project_language_resource_route_common.php`
  - `mtool/app/project_language_resources_page.php`
  - `mtool/app/project_language_resource_detail_page.php`
  - `mtool/app/project_language_resource_groups_page.php`
  - wrapper redirect 時の `bridge_errors` を current page で表示できるようにした。
  - language resource module state helper を追加し、catalog source から `file canonical available` / `reference fallback` / `optional module off` / `blocked` を導出できるようにした。後段の file-only cutover で live UI から `canonical enabled` 分岐も外した。
  - resources / groups / detail page では module state を summary card と warning に反映し、read-only 時は `Create Resource In Selected Group` / `Create Group` の導線を出さないようにした。
  - `mtool/app/project_detail_page.php` でも `Language Resource` card を固定 `available-partial` ではなく実際の module state に応じて `available-partial` / `optional-readonly` / `optional-off` / `blocked` で表示するようにした。
  - `mtool/app/dashboard_page.php` の `/language-resources` 説明も、旧 CRUD 前提から file canonical inspector + JSON 直接編集前提へ更新した。
- docs
  - `docs/internal/mtool-admin-roadmap.md`
  - `LanguageResource` の current route 記述を更新し、`/assignments` を standalone route としては持たないこと、generated wrapper seam を追加したことを明記した。

## wrapper behavior

- `lang_res.php`
  - current groups summary route へ redirect する。
- `lang_res_list.php`
  - `LanguageResourceGroupPID` を current `group_pid` query へ詰め替えて resource list へ redirect する。
- `lang_res_edit.php`
  - GET
    - blank add flow は current resources list の `intent=create` へ寄せる。
    - existing `PID` deep link は current detail route へ寄せる。
    - duplicate flow は `intent=duplicate&source_resource_key=...` へ寄せる。
  - POST
    - create / update / delete / duplicate は current admin では処理せず、inspector へ redirect したうえで `bridge_errors` に repo 配下 JSON の直接編集案内を載せる。
- `lang_res_group_edit.php`
  - GET
    - blank add flow は current groups route の `intent=create` へ寄せる。
    - existing `PID` deep link は current groups route の `group_pid=...` へ寄せる。
  - POST
    - create / update / delete は current admin では処理せず、groups inspector へ redirect したうえで `group.json` 直接編集案内を返す。
- `lang_res_move.php`
  - GET
    - known `PID` は current detail route へ redirect する。
    - unknown PID や project mismatch は language-resources list へ redirect し、`bridge_errors` を付ける。
  - POST
    - move 自体は current admin では処理せず、current detail inspector へ redirect したうえで `resource.json` 直接編集案内を返す。
- `lang_res_assign_additional_group.php`
  - GET
    - known `LanguageResourcePID` は current detail route へ redirect する。
    - unknown PID や project mismatch は language-resources list へ redirect し、`bridge_errors` を付ける。
  - POST
    - additional group 更新は current admin では処理せず、current detail inspector へ redirect したうえで `resource.json` 直接編集案内を返す。
- `lang_res_auto_translate_ajax.php`
  - GET/HEAD
    - language-resources list へ redirect する。
  - POST
    - current auto-translate endpoint を内部 dispatch する。
    - legacy caller が `success` callback で `_status` を見る前提を壊さないよう、HTTP status は常に `200` で返し、`TranslatedText` / `_status` / `Message` を legacy 互換で返す。

## verification

- `php -l`
  - `mtool/app/project_output_html_module_generator.php`
  - `mtool/app/project_language_resource_route_common.php`
  - `mtool/app/project_language_resources_page.php`
  - `mtool/app/project_language_resource_detail_page.php`
  - `mtool/app/project_language_resource_groups_page.php`
- generator smoke
  - `app_project_output_html_module_generated_lang_res_edit_wrapper_text()` / `app_project_output_html_module_generated_lang_res_group_edit_wrapper_text()` を含む wrapper 出力に、expected handoff token が含まれることを確認した。
  - `app_project_output_html_module_generated_entry_wrapper_text()` から `lang_res.php` / `lang_res_list.php` / `lang_res_edit.php` / `lang_res_group_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` / `lang_res_auto_translate_ajax.php` の wrapper case が解決されることを確認した。
- artifact regeneration
  - host 側で `APP_DB_*` / `APP_CONFIG_DB_*` を `127.0.0.1:33061` に上書きし、`php mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex` を実行して、real HTML-DB artifact 生成が成功した。
  - artifact key は `20260515-000832-8eba03cb` で、bundle runtime source は `work/artifacts/source-outputs/MTOOL/20260515-000832-8eba03cb/bundle/mtool-source-output-html-db-20260515-000832-8eba03cb/mtool/html-source-outputs/MTOOL/HTML-DB` に出力された。
  - generated runtime source に `lang_res.php` / `lang_res_list.php` / `lang_res_edit.php` / `lang_res_group_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` / `lang_res_auto_translate_ajax.php` が materialize されていることを確認した。
  - 上記 generated wrapper files への `php -l` もすべて成功した。
- artifact regeneration after app-root fallback fix
  - generator の app-root fallback 更新後、再度 `php mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex` を実行し、artifact `20260515-002348-f5ff5f2e` を生成した。
  - 再生成後の `lang_res_edit.php` / `lang_res_group_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` / `lang_res_auto_translate_ajax.php` に `app/bootstrap.php` / `mtool/app/bootstrap.php` / `shared/bootstrap.php` fallback が materialize されていることを確認した。
  - bundle root で `php -S 127.0.0.1:18080` を起動し、`APP_APP_ROOT` を与えずに `POST /lang_res_auto_translate_ajax.php` を叩いたところ、bootstrap not found ではなく `{"IsCompleted":false,"_status":"NG","Message":"認証が必要です。"}` が返り、repo 配下の current app bootstrap 解決まで到達することを確認した。
- artifact regeneration after dynamic catalog fallback
  - resource 系 wrapper の current catalog fallback 追加後、再度 `php mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex` を実行し、artifact `20260515-011029-f65378e0` を生成した。
  - 再生成後の `lang_res_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` に `app_fetch_project_language_resource_catalog` を使う dynamic pid/key-name lookup helper が materialize されていること、`php -l` も通ることを確認した。
- artifact regeneration after legacy include/helper isolation
  - generated entry wrapper target に legacy include/helper を追加した後、`docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex` を実行し、artifact `20260515-012145-b081b64e` を生成した。
  - 再生成後の `lang_res_edit_include.php` / `lang_res_group_edit_include.php` / `lang_res_move_include.php` / `lang_res_assign_additional_group_include.php` / `lang_res_check_project_source_output_setting_lib.php` / `lang_res_select_resource_group_lib.php` は、generated root では `_legacy/...` を読む wrapper になり、実体が `_legacy/` 側へ退避していることを確認した。
- logged-in end-to-end smoke
  - `generated_html_db_dev_router.php` を使って generated wrapper file と current app route を同じ origin で束ね、`APP_SITE=admin` と host DB port override (`127.0.0.1:33061`) 付きの local server を起動した。
  - stub login (`admin` / `change-me-admin`) 後に `lang_res.php` を follow し、`Language Resource Groups` page へ 200 で着地することを確認した。
  - `lang_res_list.php?ProjectPID=1&LanguageResourceGroupPID=5` を follow し、group PID 5 (`Common Lib`) の current `Language Resources` page へ 200 で着地することを確認した。
  - `lang_res_group_edit.php?ProjectPID=1&PID=5` を follow し、current groups page の edit form に `legacy_group_pid=5` / `Common Lib` が prefill されることを確認した。
  - `lang_res_edit.php?ProjectPID=1&PID_BY_KEYNAME=ACTION_LOGIN_CAPTION` を follow し、current detail page で `ACTION_LOGIN_CAPTION` の resource detail が描画されることを確認した。
  - login 済み cookie で `POST /lang_res_auto_translate_ajax.php` を実行すると、auth error ではなく `{"IsCompleted":false,"_status":"NG","Message":"auto-translate provider が設定されていません。","Provider":"disabled","ProviderCaption":"disabled"}` が返り、generated wrapper -> current endpoint -> legacy JSON remap が end-to-end で動作することを確認した。
- automated smoke
  - `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-011029-f65378e0` を host DB/loopback へ接続できる権限付きで実行し、stub login、`lang_res.php` / `lang_res_list.php` / `lang_res_group_edit.php` / `lang_res_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` / `lang_res_auto_translate_ajax.php` の read-only checks がすべて JSON `ok=true` で通ることを確認した。
  - `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-011029-f65378e0 --allow-mutate` を実行し、temp group A/B create/update/delete、temp resource create/update/additional-group/move/delete が wrapper 経由で通ること、cleanup でも残骸が残らないことを確認した。
  - mutation check の temp resource key は `CODEX_SMOKE_20260515011307_825DBE68`、legacy resource pid は `3998` で、create 直後の resource に対しても dynamic fallback 付き wrapper が update/move/additional-group/delete を継続できることを確認した。
  - `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-011029-f65378e0 --publish` を実行し、published docroot `work/source-outputs/MTOOL/HTML-DB` に対する read-only smoke でも `lang_res.php` / `lang_res_list.php` / `lang_res_group_edit.php` / `lang_res_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` / `lang_res_auto_translate_ajax.php` がすべて `ok=true` で通ることを確認した。
  - `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-011029-f65378e0 --publish --allow-mutate` を実行し、published docroot 上でも temp group A/B create/update/delete、temp resource create/update/additional-group/move/delete が wrapper 経由で通ること、cleanup まで `ok=true` で終わることを確認した。mutation check の temp resource key は `CODEX_SMOKE_20260515011905_8545469C`、legacy resource pid は `3998` であった。
  - `docker compose exec -T web-admin php /var/www/mtool/scripts/check_generated_html_db_language_resource_wrappers.php --publish` を実行し、container 内の `work/source-outputs/MTOOL/HTML-DB` docroot でも read-only publish smoke が通ることを確認した。`make mtool-html-db-lang-res-wrapper-check` の実体もこの command である。
  - `docker compose exec -T web-admin php /var/www/mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-012145-b081b64e --publish --allow-mutate` を実行し、legacy include/helper を `_legacy/` へ退避した新 artifact でも published docroot 上の mutate smoke が継続して通ることを確認した。mutation check の temp resource key は `CODEX_SMOKE_20260515012209_D4AADDE4`、legacy resource pid は `3998` であった。
  - `docker compose exec -T web-admin php -r 'require "/var/www/mtool/app/bootstrap.php"; require "/var/www/mtool/app/project_detail_page.php"; ...'` で `app_project_language_resource_module_state_for_project()` と `app_project_admin_modules()` を呼び、当時の current `MTOOL` では `canonical enabled` / `available-partial` が project hub card に反映されることを確認した。
  - page rendering 変更後も `docker compose exec -T web-admin php /var/www/mtool/scripts/check_generated_html_db_language_resource_wrappers.php --publish` の read-only smoke が引き続き成功し、groups/resources/detail route の wrapper landing が壊れていないことを確認した。
  - inspector-only cutover 後、`lang_res_group_edit.php` / `lang_res_edit.php` の read-only smoke は旧 form prefill 前提から、current inspector に表示される `group PID` / `legacy PID` / name / key 表示を検証する形へ更新した。
  - 旧 artifact `20260515-012145-b081b64e` の generated `lang_res_group_edit.php` はまだ current POST route へ handoff する版だったため、`legacy-write-bridge:group-create` で `405` を返した。generator 変更だけでは既存 artifact には反映されないため、artifact 再生成が必要であることを確認した。
  - host 側の `APP_DB_*` / `APP_CONFIG_DB_*` を `127.0.0.1:33061` に上書きして `php mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex` を実行し、新 artifact `20260515-043451-616ec192` を生成した。
  - smoke CLI の `--allow-mutate` は temp row を DB に作る mutate check ではなく、legacy write entry が `bridge_errors` 付きで current inspector へ遷移し、`group.json` / `resource.json` の直接編集へ案内する non-mutating bridge check に切り替えた。
  - `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-043451-616ec192 --allow-mutate` を実行し、artifact docroot 上で read-only landing と legacy write-entry bridge checks がすべて `ok=true` で通ることを確認した。
  - `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-043451-616ec192 --publish --allow-mutate` を実行し、published docroot `work/source-outputs/MTOOL/HTML-DB` でも同じ bridge checks が `ok=true` で通ることを確認した。published root は `242 files / 3238783 bytes`、`published_at=2026-05-15T04:35:56+00:00` である。
  - runtime loader の file-only cutover 後、`php -r 'require "mtool/app/bootstrap.php"; require "mtool/app/project_language_resource_catalog_loader.php"; ...'` で `app_fetch_project_language_resource_catalog($app, "MTOOL", 1)` を確認し、`source = file-canonical` / `group_source_output_count = 10` / `caption_count = 20233` を確認した。
  - 同 cutover 後、`php -r 'require "mtool/app/bootstrap.php"; require "mtool/app/project_detail_page.php"; ...'` で `app_project_language_resource_module_state_for_project($app, "MTOOL", 1)` を確認し、`state = file-canonical` / `module_status = available-partial` / `title = file canonical available` を確認した。
  - 同 cutover 後も `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-043451-616ec192` と `--allow-mutate` を再実行し、artifact docroot 上の read-only landing / legacy write-entry bridge checks が引き続き `ok=true` で通ることを確認した。
  - 同 cutover 後に `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-043451-616ec192 --publish --allow-mutate` も再実行し、published docroot `work/source-outputs/MTOOL/HTML-DB` でも bridge checks が `ok=true` で通ること、published root は `242 files / 3238783 bytes`、`published_at=2026-05-15T04:49:46+00:00` であることを確認した。
  - runtime/generator loader 分離後、host 側 DB override 付きで `php mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex` を再実行し、新 artifact `20260515-052542-fc61c54c` を生成した。
  - 再生成後の `lang_res_edit.php` / `lang_res_move.php` / `lang_res_assign_additional_group.php` には `project_language_resource_catalog_loader.php` を使う dynamic lookup helper が materialize され、`php -l` も通ることを確認した。
  - `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-052542-fc61c54c --allow-mutate` を実行し、artifact docroot 上の read-only landing / legacy write-entry bridge checks が `ok=true` で通ることを確認した。
  - `php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-052542-fc61c54c --publish --allow-mutate` を実行し、published docroot `work/source-outputs/MTOOL/HTML-DB` でも bridge checks が `ok=true` で通ること、published root は `242 files / 3238807 bytes`、`published_at=2026-05-15T05:26:10+00:00` であることを確認した。
  - さらに `project_language_resource_db_bridge.php` へ DB canonical bridge helper を分離し、`project_language_resource_repository.php` は互換 shim に縮退した。`project_language_resource_sync_service.php` は直接 `project_language_resource_db_bridge.php` を require するようにした。
  - 上記整理後に `php mtool/scripts/sync_project_language_resource_from_file_tree.php --project-key=MTOOL` を実行し、dry-run が引き続き成功し、DB 未到達環境では preview warning のみ返すことを確認した。
  - live UI から `canonical enabled` 分岐を外した後も、`php mtool/scripts/check_generated_html_db_language_resource_wrappers.php --artifact-key=20260515-052542-fc61c54c` の read-only smoke が `ok=true` で通ることを確認した。

## 残り

- generated wrapper の read-only / mutating smoke は artifact docroot と published docroot の両方で automation まで通り、legacy include/helper も generated root から `_legacy/` へ隔離した。current admin / project hub の optional read-only/off UX も固定した。残りは CI へどう組み込むか、optional module 用 seed / scenario を core からどう切り離すか、helper contract 自体を current page / route 側へさらに吸収するかの判断である。
- `lang_res_check_project_source_output_setting_lib.php` / `lang_res_select_resource_group_lib.php` などの helper include 群は root では wrapper 化したが、ロジック本体は依然 `_legacy/` 側に残っている。
- optional module 分離と code-native source-of-truth への再設計は別トラックで継続する。
