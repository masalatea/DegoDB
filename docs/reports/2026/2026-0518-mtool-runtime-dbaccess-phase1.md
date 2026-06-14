# 2026-05-18 Mtool Runtime DBAccess Phase1

## 変更内容

- `MTOOL / RUNTIME-DBCLASSES` の final artifact について、`dbaccess-*` を `root wrapper + base/dbaccess-*Base.php` へ移行した。
- `data-*` はまだ移行途中のため、`root entry stub + _base/ + _wrappers/` を維持する。
- custom layer は引き続き `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/` を使い、root `dbaccess-*` wrapper から custom override を解決する。

## 実装ポイント

- `project_output_service.php`
  - runtime bundle build 時に `dbaccess-*` だけ別扱いし、`base/dbaccess-*Base.php` と generated root wrapper を出力するようにした。
  - `_runtime_loader.php` に `mtool_runtime_bundle_load_custom_wrapper()` を追加し、DBAccess wrapper からも custom layer bootstrap / wrapper 解決を再利用できるようにした。
- `project_output_runtime_generator.php`
  - generated DBAccess body に sample1 と同様の per-function hook 呼び出しを差し込んだ。
- 旧 PHP の `FOR FUNCTION` editable area は実使用が確認できなかったため再現しない。DBAccess custom は wrapper 継承ベースに寄せる。
- representative self-loop baseline
  - `_base/dbaccess-*.php` / `_wrappers/dbaccess-*.php` の確認をやめ、`base/dbaccess-*Base.php` と root `dbaccess-*.php` の digest へ更新した。

## 現在の runtime layout

```text
mtool/dbclasses/
  dbaccess-Project.php
  base/dbaccess-ProjectBase.php
  data-Project.php
  _base/data-Project.php
  _wrappers/data-Project.php
  _runtime_loader.php
  _support/legacy-dbaccess/...
  _support/runtime-generation-manifest.json
```

## 検証

- `php -l mtool/app/project_output_runtime_generator.php`
- `php -l mtool/app/project_output_service.php`
- `docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=RUNTIME-DBCLASSES --requested-by=codex-phase1-dbaccess`
- `docker compose exec -T web-admin php /var/www/mtool/scripts/check_mtool_self_loop.php --requested-by=codex-phase1-dbaccess-final`

## 次

- heavy な `data-*` は、まず dedicated sample pack で旧構造を actual tool output として再現する。
- sample で contract が固まった class から、`MTOOL` 側でも `root wrapper + base/*Base.php` へ移す。
