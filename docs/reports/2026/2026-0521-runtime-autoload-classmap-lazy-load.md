# 2026-05-21 Runtime Autoload Classmap Lazy Load

## 結論

- `autoload_mtool.php` の basename / loader entry contract は維持したまま、runtime dbclasses の eager `include_once(...)` 全列挙をやめた。
- current runtime bundle では、top-level function を持つ file と `_runtime_loader.php` だけを preload し、それ以外の class / interface / trait / enum は generated classmap + `spl_autoload_register()` で lazy load する。
- `PSR-4` / `Composer` は入れていない。current naming / directory layout を変えずに loader 振る舞いだけを差し替えた。

## 実装

- `mtool/app/project_output_runtime_generator.php`
  - runtime root の PHP file を走査し、top-level function preload list と class-like symbol classmap を生成する helper を追加。
  - `autoload_mtool.php` は既存 bootstrap / editable area を保持したまま、generated autoload block を差し込む方式へ変更。
- `mtool/app/project_output_service.php`
  - final layered runtime bundle 書き出し後に `autoload_mtool.php` を final output 基準で再 rewrite するよう変更。
- `tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - preload 対象 file の top-level function が定義されること、pure class file は `class_exists()` 時に初めて load されることを確認する test を追加。

## 検証

- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - pass (`4 tests / 42 assertions`)
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration`
  - pass (`55 tests / 1165 assertions`)
- `make mtool-self-loop-check`
  - pass
  - latest verified artifact: `20260521-023351-d52e8c8b`
- `php mtool/scripts/promote_runtime_reference.php --artifact-key=20260521-023351-d52e8c8b --requested-by=codex`
  - promote 済み
- `php mtool/scripts/show_runtime_reference_status.php --require-current`
  - `up-to-date`
  - `needs_promote=false`
  - `durable_recovery_ready=true`

## 補足

- `data-*` に top-level helper function を持つ file があるため、runtime dbclasses 全件を pure lazy load にはしていない。
- current contract は「helper function を壊さない preload」と「class-like symbol の lazy load」の hybrid で読む。
