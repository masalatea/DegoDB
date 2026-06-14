# 2026-05-15 Language Resource File Tree Bootstrap

## Status Note

- この初期 bootstrap report の後続変更で、DB bridge / sync service は `mtool/app/` から `mtool/scripts/debug/language_resource/lib/` へ移され、`project_language_resource_repository.php` 互換 shim も削除された。
- current admin の auto-translate route も同日中に削除され、現在は file workflow 前提である。
- 現在の確定方針は [2026-0515-language-resource-file-source-of-truth-plan.md](<repo-root>/docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md) を正本として読む。

## 概要

- `LanguageResource` の file-based source of truth 候補として `mtool/resources/{project_key}/` を追加した。
- `MTOOL` について、copied legacy reference JSON から `manifest.json` + `group.json` + `resource.json` tree を export できるようにした。
- export 後の tree を validator で検査し、`locales=51 / groups=7 / resources=1007` を確認した。
- その後、legacy reference の孤児 row を file-canonical で正規化して落とす方針を固定し、`manifest.json.normalization` に差分を残すようにした。
- current runtime は DB canonical を read source に使わず、`file-canonical` を primary に、無い場合は copied legacy reference / empty fallback を返すようにした。
- canonical DB は source of truth ではなく migration / debug bridge とみなし、file tree から dry-run / apply で比較・反映できる CLI を追加した。
- runtime/generator/wrapper が読む catalog helper は `project_language_resource_catalog_loader.php` に分離し、DB canonical helper は `project_language_resource_db_bridge.php` へ寄せた。`project_language_resource_repository.php` は互換 shim としてだけ残している。

## 追加したもの

- app helper
  - `mtool/app/language_resource_file_catalog.php`
  - file tree の key/path/helper
  - optional overlay seed からの `legacy ProjectSourceOutputPID -> source_output_key` 解決
  - reference JSON からの tree build
  - tree write / load / validate
- scripts
  - `mtool/scripts/export_language_resource_file_tree.php`
  - `mtool/scripts/validate_language_resource_file_tree.php`
  - `mtool/scripts/sync_project_language_resource_from_file_tree.php`
- durable tree
  - `mtool/resources/README.md`
  - `mtool/resources/MTOOL/manifest.json`
  - `mtool/resources/MTOOL/groups/*/group.json`
  - `mtool/resources/MTOOL/groups/*/resources/*.json`
- sync service
  - `mtool/app/project_language_resource_sync_service.php`

## export shape

- root
  - `mtool/resources/MTOOL/manifest.json`
- group
  - `mtool/resources/MTOOL/groups/grp-005-common-lib/group.json`
- resource
  - `mtool/resources/MTOOL/groups/grp-001-developers-matsuesoft-co-jp/resources/ACTION_ADD_APACHE_SETTING.json`

## current behavior

- export script は `mtool/reference/mtool-legacy-language-resource-catalog.json` を読み、file tree を再生成する。
- `group.json` は locale list と source output binding を持つ。
- `resource.json` は base group、additional groups、caption map を持つ。
- optional overlay seed `030_project1_language_resource_source_output_seed.sql` から `source_output_key` を 10 本解決する。
- `manifest.json.counts` は file tree 実体 count を持ち、`group_source_outputs=10 / captions=20233` に正規化される。
- `manifest.json.normalization` は legacy reference の孤児 row を保持する。
  - dropped `group_source_outputs = 3`
  - dropped `captions = 17`
- current runtime の `app_fetch_project_language_resource_catalog()` は DB canonical に触れず、file tree があれば `file-canonical`、無ければ copied reference / empty fallback を返す。
- `sync_project_language_resource_from_file_tree.php` は dry-run で file tree summary を返し、`--apply` 時だけ migration / debug 用の canonical DB bridge を更新する。

## verification

- `php -l mtool/app/language_resource_file_catalog.php`
- `php -l mtool/app/project_language_resource_catalog_loader.php`
- `php -l mtool/app/project_language_resource_db_bridge.php`
- `php -l mtool/app/project_language_resource_repository.php`
- `php -l mtool/app/project_language_resource_route_common.php`
- `php -l mtool/app/project_language_resource_sync_service.php`
- `php -l mtool/scripts/export_language_resource_file_tree.php`
- `php -l mtool/scripts/validate_language_resource_file_tree.php`
- `php -l mtool/scripts/sync_project_language_resource_from_file_tree.php`
- `php mtool/scripts/export_language_resource_file_tree.php --project-key=MTOOL --clean`
  - `files_written = 1015`
  - `summary.group_source_outputs = 10`
  - `summary.captions = 20233`
  - `normalization.dropped_counts.group_source_outputs = 3`
  - `normalization.dropped_counts.captions = 17`
- `php mtool/scripts/validate_language_resource_file_tree.php --project-key=MTOOL`
  - `errors = 0`
  - `warnings = 0`
  - `manifest_counts.resources = 1007`
  - `manifest_counts.groups = 7`
  - `manifest_counts.languages = 51`
- `php -r 'require "mtool/app/bootstrap.php"; require "mtool/app/project_language_resource_catalog_loader.php"; $app=app_bootstrap(); $result=app_fetch_project_language_resource_catalog($app,"MTOOL",1); ...'`
  - `source = file-canonical`
  - `group_source_output_count = 10`
  - `caption_count = 20233`
- `php mtool/scripts/sync_project_language_resource_from_file_tree.php --project-key=MTOOL`
  - dry-run は成功
  - この環境では `db-config` に到達できないため DB preview は warning 扱い

## 残り

- current admin に file tree editor を作る予定はなく、編集は repo 配下 JSON の直接更新前提で進める。
- `--apply` の実 DB 反映は migration / debug bridge 用の確認に限る。
- 次段は current editor を足すことではなく、generator / artifact build の file-direct 化と sync bridge の縮退である。
