# 2026-05-14 Language Resource Current Entry Slice

## 概要

- `LanguageResource` の current admin 入口を追加した。
- まずは canonical CRUD ではなく、copied legacy catalog を current route から辿れる `read-first` slice として入れた。
- `MTOOL` の `LanguageResource` metadata は `original-codes/mtool.sql` から export し、repo 内 reference JSON として固定した。

## 追加したもの

- export script
  - `mtool/scripts/export_legacy_language_resource_reference.php`
- copied reference
  - `mtool/reference/mtool-legacy-language-resource-catalog.json`
- loader
  - `mtool/app/legacy_language_resource_reference.php`
- route common
  - `mtool/app/project_language_resource_route_common.php`
- page
  - `mtool/app/project_language_resources_page.php`
  - `mtool/app/project_language_resource_groups_page.php`
  - `mtool/app/project_language_resource_detail_page.php`

## route

- `/projects/{project_key}/language-resources`
- `/projects/{project_key}/language-resources/groups`
- `/projects/{project_key}/language-resources/{resource_key}`

## current behavior

- `MTOOL` では copied legacy catalog を読み、resource list / group summary / resource detail を current admin から参照できる。
- route は read-only first slice であり、current canonical table / CRUD はまだ持たない。
- group に紐づく legacy `ProjectSourceOutputPID` は、current `project_source_outputs.notes` から解決できる範囲で `source_output_key` 表示へ寄せる。

## reference counts

- `resource_count = 1007`
- `group_count = 7`
- `group_language_count = 308`
- `group_source_output_count = 13`
- `additional_group_assignment_count = 0`
- `caption_count = 20250`
- `language_count = 51`

## verification

- `php -l`
  - `legacy_language_resource_reference.php`
  - `export_legacy_language_resource_reference.php`
  - `project_language_resource_route_common.php`
  - `project_language_resources_page.php`
  - `project_language_resource_groups_page.php`
  - `project_language_resource_detail_page.php`
  - `router.php`
  - `http.php`
  - `project_detail_page.php`
  - `dashboard_page.php`
- export 実行
  - `php mtool/scripts/export_legacy_language_resource_reference.php --project-key=MTOOL --project-pid=1 --sql-dump=original-codes/mtool.sql --output=mtool/reference/mtool-legacy-language-resource-catalog.json`
- `web-admin` container 内の stub session 実行で、list / groups / detail route が HTML を返すことを確認した。

## 残り

- current canonical table をどこまで持つかの決定。
- resource / group / caption / assignment の current write path。
- move / assignments / auto-translate route。
- optional module / code-native source of truth への段階移行。
