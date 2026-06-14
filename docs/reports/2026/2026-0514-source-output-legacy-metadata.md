# 2026-05-14 Source Output Legacy Metadata

## Summary

- `Source Output` の legacy-only field は current schema に新設せず、`notes` の structured block に退避する方針で実装を揃えた。
- `project_source_output_new_page.php` / `edit_page.php` / `detail_page.php` は、通常の notes 本文と legacy metadata を分離表示し、保存時だけ再マージする。
- `project_output_html_module_generator.php` の wrapper handoff は、legacy form にだけ存在する field を hidden input で current page へ引き継ぐようにした。
- `ProjectSourceOutput.PID` も同じ structured block に保持するため、current reorder reset や legacy route mapping で使う情報を落とさない。

## Policy

- canonical field として保持するもの:
  - `ProgramLanguage`
  - `ClassType`
  - `ReleaseTargetType`
  - `SourceTemplateDir`
  - `SourceOutputDir`
  - `SourceTempOutputDir`
  - `ProxyBaseURL`
  - `AutoloadFilenameSuffix`
  - `SourceTextCharCode`
- notes block に退避するもの:
  - `ProjectSourceOutput.PID`
  - `CustomFileExtention`
  - `DropboxBaseFolderPID`
  - `UnitTestTemplateDir`
  - `UnitTestOutputDir`
  - `TargetServerProjectSourceOutputPID`
  - `CSNameSpace`
  - `JavaPackageName`
  - `AutoLoadFilePathForPHP`
  - `JavaFunctionType`
  - `DotNetLanguageResourceType`

## Files

- `mtool/app/project_source_output_route_common.php`
- `mtool/app/project_source_output_new_page.php`
- `mtool/app/project_source_output_edit_page.php`
- `mtool/app/project_source_output_detail_page.php`
- `mtool/app/project_output_html_module_generator.php`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0514-progress-snapshot.md`

## Verification

```zsh
php -l mtool/app/project_source_output_route_common.php
php -l mtool/app/project_source_output_new_page.php
php -l mtool/app/project_source_output_edit_page.php
php -l mtool/app/project_source_output_detail_page.php
php -l mtool/app/project_output_html_module_generator.php

php -r 'require "mtool/app/project_source_output_route_common.php"; $notes = app_project_source_output_notes_with_legacy_metadata("User note", ["ProjectSourceOutput.PID" => "123", "JavaPackageName" => "jp.example.demo"]); var_export(app_project_source_output_split_notes($notes));'
php -r 'require "mtool/app/project_source_output_route_common.php"; $notes = "Derived from legacy ProjectSourceOutput PID 5.\n\n[[legacy-source-output]]\nProjectSourceOutput.PID=5\nUnitTestTemplateDir=/tmp/unit\n[[/legacy-source-output]]"; var_export(app_project_source_output_legacy_metadata_rows(app_project_source_output_split_notes($notes)["legacy_metadata"]));'
```

確認結果:

- 5 ファイルとも `php -l` は成功した。
- notes merge/split helper は `user_notes` と structured legacy metadata を往復できた。
- detail/edit/new は legacy metadata を通常の notes 本文と分離して扱える前提になった。
- `HTML-DB` は `artifact_key=20260514-042401-2ea215e3` で再 publish できた。
- `check_mtool_project1_outputs.php` は `definition_count=36`, `success_count=36`, `failure_count=0` を再確認した。

## Remaining Focus

- source-output bridge debt の次段として、`HTML` canonical source root / refresh policy へ進む。
- broad scope の残 major area である `Project Security / Host Assignment` currentization へ進む。
