# 2026-05-15 Language Resource Additional Group Currentization

> Historical note:
> This was an intermediate slice before the later inspector-only / file-source-of-truth cutover on 2026-05-15.
> The write path described here is no longer the current design.

## 概要

- `LanguageResource` の additional group assignment を current detail route から更新できるようにした。
- resource list の group filter も legacy `GetLanguageResourceListWithAdditionalGroup()` 相当へ寄せ、selected group の additional-group hit を落とさないようにした。
- `Project` top の module summary も更新し、`LanguageResource` の残件を `auto-translate` 中心に寄せた。

## 変更点

- repository
  - `mtool/app/project_language_resource_repository.php`
  - `project_language_resource_additional_groups` を resource 単位で sync する current write path を追加した。
  - insert/delete で変化した group の `last_modified_dt` も current canonical table 側で更新する。
- detail page
  - `mtool/app/project_language_resource_detail_page.php`
  - `Additional Groups` editor を追加した。
  - base group 以外の group を checkbox で選択し、`update-additional-groups` action で保存できる。
- list page
  - `mtool/app/project_language_resources_page.php`
  - selected group filter は base group だけでなく additional group assignment も見る。
  - additional-group hit で表示された row は `matched via additional group` / `selected group relation: additional` を出す。
- project top
  - `mtool/app/project_detail_page.php`
  - `Language Resource` summary から `additional group` を残件から外した。

## verification

- `php -l`
  - `mtool/app/project_language_resource_repository.php`
  - `mtool/app/project_language_resource_detail_page.php`
  - `mtool/app/project_language_resources_page.php`
  - `mtool/app/project_detail_page.php`
- running docker で render 確認
  - detail page に `Additional Groups` editor と `Update Additional Groups` button が出ることを確認した。
- temporary smoke
  - `ACTION_ADDED_APACHE_HOST_SETTING` に `Common Lib` を additional group として一時付与した。
  - `/projects/MTOOL/language-resources?group_pid=5&q=ACTION_ADDED_APACHE_HOST_SETTING` で
    `matched via additional group` と `selected group relation: additional` が出ることを確認した。
  - その後 assignment を削除し、`project_language_resource_additional_groups` count が `0` に戻ることを確認した。

## 残り

- `auto-translate` currentization
- optional module / code-native source of truth への境界整理
