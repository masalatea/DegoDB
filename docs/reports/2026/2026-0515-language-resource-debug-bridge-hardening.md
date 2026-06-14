# 2026-05-15 Language Resource Debug Bridge Hardening

## 概要

- `LanguageResource` の通常運用導線を file-canonical 側へさらに寄せた。
- file tree export / validate を全 project 一括で回せるようにし、日常確認は DB bridge ではなく file tree の bulk export/validate を主系にした。
- DB sync CLI は migration/debug 専用であることを help と JSON 出力の両方で明示した。

## 変更

- `mtool/scripts/validate_language_resource_file_tree.php`
  - `--all` を追加した。
  - `mtool/resources/*` 配下の `manifest.json` を持つ project root を自動発見して一括 validate できる。
  - 全件モードでは project ごとの `manifest_counts` / `actual_counts` / `normalization.raw_reference_counts` / `normalization.dropped_counts` を summary として返し、巨大な `dropped_rows` は省略する。
- `mtool/app/legacy_language_resource_reference.php`
  - known project map を helper 化し、bulk export の列挙元を 1 箇所に寄せた。
- `mtool/scripts/export_language_resource_file_tree.php`
  - `--all` を追加した。
  - known project 全件に対して default root / default overlay seed を使い、一括 export を回せるようにした。
- `mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php`
  - usage に「current runtime/admin では使わない debug bridge 専用」であることを追記した。
  - JSON 出力に `mode=debug-db-bridge` を追加した。
  - warning にも file tree が source of truth であることを必ず含めるようにした。
- `mtool/scripts/debug/language_resource/`
  - DB bridge / retirement 用の script と lib 実装本体を `mtool/scripts/` 直下から移し、debug 専用 subtree へ寄せた。
  - 旧 `mtool/scripts/*.php` path には silent compatibility wrapper だけを残した。
- `Makefile`
  - `make mtool-lang-res-file-tree-export` を追加した。
  - `make mtool-lang-res-file-tree-check` を追加した。
- `mtool/resources/README.md`
  - `make mtool-lang-res-file-tree-export`
  - `make mtool-lang-res-file-tree-check`
  - `export --all --clean`
  - `validate --all`
  - 上記を current 運用導線として追記した。

## 検証

- `php -l mtool/scripts/validate_language_resource_file_tree.php`
- `php -l mtool/scripts/export_language_resource_file_tree.php`
- `php -l mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php`
- `php mtool/scripts/export_language_resource_file_tree.php --all --clean`
  - `project_count = 4`
  - `ok_count = 4`
  - `error_project_count = 0`
- `php mtool/scripts/validate_language_resource_file_tree.php --all`
  - `project_count = 4`
  - `ok_count = 4`
  - `warning_project_count = 0`
  - `error_project_count = 0`
- `make mtool-lang-res-file-tree-export`
  - `project_count = 4`
  - `ok_count = 4`
  - `error_project_count = 0`
- `make mtool-lang-res-file-tree-check`
  - `project_count = 4`
  - `ok_count = 4`
  - `warning_project_count = 0`
  - `error_project_count = 0`
  - 直前に観測した missing-file / invalid-json は `export --clean` と validate を並列実行した race であり、順次実行では再現しない。
- `php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php --project-key=MTOOL`
  - `ok = true`
  - `mode = debug-db-bridge`
  - DB 未接続環境では preview warning を返しつつ成功する。

## 含意

- `LanguageResource` の日常更新後に、bulk export と bulk validate を 1 コマンドずつ回せる状態になった。
- bulk export / validate の主系コマンドは順次実行なら安定しており、前回のエラーは file tree 不整合ではなく parallel race と判断できる。
- DB bridge CLI は `mtool/scripts/debug/language_resource/` へ寄せたことで、「まだ残っているが、使う理由が限定された debug tool」という位置づけが path 上でも明確になった。
- `MTOOL` / `SAMPLE2` / `SAMPLE4` / `SAMPLE6` の file tree は一括 validate で常時監視しやすくなった。
