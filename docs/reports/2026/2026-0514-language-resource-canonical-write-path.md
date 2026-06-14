# LanguageResource Canonical Write Path

## 概要

- `LanguageResource` の current admin を read-first から canonical CRUD + move/duplicate flow へ進めた。
- copied legacy catalog を固定 reference として保持しつつ、current DB に `project_language_resource_*` canonical table を追加した。
- `MTOOL` は copied reference から bootstrap し、current route は canonical source を読む。

## 追加したもの

- schema
  - `docker/mariadb/config-initdb/027_language_resource_metadata.sql`
  - `project_language_resource_groups`
  - `project_language_resource_group_languages`
  - `project_language_resource_group_source_outputs`
  - `project_language_resource_languages`
  - `project_language_resources`
  - `project_language_resource_captions`
  - `project_language_resource_additional_groups`
- repository
  - `mtool/app/project_language_resource_repository.php`
- route/page 更新
  - `mtool/app/project_language_resource_route_common.php`
  - `mtool/app/project_language_resources_page.php`
  - `mtool/app/project_language_resource_groups_page.php`
  - `mtool/app/project_language_resource_detail_page.php`
  - `mtool/app/request.php`
  - `mtool/app/project_detail_page.php`
  - `mtool/app/dashboard_page.php`

## 現在できること

- `/projects/{project_key}/language-resources`
  - current canonical catalog を表示する。
  - selected group 前提で resource create を行う。
  - `intent=duplicate&source_resource_key=...` で duplicate editor を prefill し、そのまま複製作成できる。
- `/projects/{project_key}/language-resources/groups`
  - group create/update/delete を行う。
  - selected languages / target source outputs を current canonical table に保存する。
- `/projects/{project_key}/language-resources/{resource_key}`
  - resource/caption update/delete を行う。
  - base group move を current canonical table に反映する。

## 実装方針

- current DB に table がある場合は canonical を優先する。
- canonical が空なら copied legacy reference から初回 bootstrap する。
- public に見せる `legacy_*_pid` と `resource_key` は維持する。
- caption は `resource + group + language` 単位で保存し、current group に有効な language だけを current catalog へ出す。

## 検証

- `php -l`
  - `project_language_resource_repository.php`
  - `project_language_resource_route_common.php`
  - `project_language_resources_page.php`
  - `project_language_resource_groups_page.php`
  - `project_language_resource_detail_page.php`
  - `request.php`
  - `dashboard_page.php`
  - `project_detail_page.php`
- running `config_app` に `027_language_resource_metadata.sql` を適用した。
- container 内 GET 確認
  - `/projects/MTOOL/language-resources`
  - `/projects/MTOOL/language-resources/groups`
  - `/projects/MTOOL/language-resources/ACTION_ADDED_APACHE_HOST_SETTING`
  - いずれも `current canonical` 表示を確認した。
- container 内 POST smoke
  - smoke group を create
  - smoke resource を create
  - resource/caption を update
  - resource を delete
  - group を delete
  - existing resource の move を実行し、caption group も追従することを確認した
  - duplicate editor の prefill を確認し、duplicate create/delete を確認した
  - DB row の増減を `config_app` で確認した。

## 残り

- additional group assignment UI
- auto-translate
- optional module / code-native source of truth への分離
