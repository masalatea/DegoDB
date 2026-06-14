# 2026-05-14 HTML Canonical Live Rows

## 何をしたか

- `project_html_definitions` / `project_html_parameters` を `db-config` schema に追加した。
- `mtool/app/project_html_repository.php` を更新し、HTML list/detail/parameter CRUD は canonical table を優先して読むようにした。
- canonical table が空の `MTOOL` は、copied legacy HTML reference から初回アクセス時に 66 html / 145 parameter を bootstrap するようにした。
- `htmlTemplate` legacy table が無い環境でも動くよう、当時は template metadata を copied reference fallback で読める状態まで切り替えた。
- `da` legacy table が無い環境でも `DataType=dbaccessclassname` editor が動くよう、DB Access class selector は canonical `project_db_access_classes` fallback に切り替えた。

## 確認

- `php -l`
  - `mtool/app/project_html_repository.php`
  - `mtool/app/project_htmls_page.php`
  - `mtool/app/project_html_detail_page.php`
  - `mtool/app/project_html_parameters_page.php`
- running `db-config` に `015_project_html_metadata.sql` を適用した。
- running `web-admin` container から repository を直接呼び、次を確認した。
  - `app_fetch_project_html_catalog($app, "MTOOL", 1)` が `66` row を返す。
  - `app_fetch_project_html_parameter_catalog_for_project($app, "MTOOL", 1)` が `145` row を返す。
  - `project_html_definitions` / `project_html_parameters` table が `config_app` に存在する。
  - catalog の sample row で `html_key` と `legacy_html_pid` が preserved されている。

## その後

- follow-up として `2026-0514-html-template-canonical-settings.md` で `html_templates` / `html_template_parameters` も canonical table 化した。
- 現在の残件は generator / wrapper 側の bootstrap dependency 圧縮であり、template settings 自体の current CRUD と smoke test は完了している。
