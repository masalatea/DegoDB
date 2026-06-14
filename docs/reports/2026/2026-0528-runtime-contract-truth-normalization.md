# 2026-05-28 runtime contract truth normalization

## 要約

- `M1. runtime contract truth normalization` として、current promoted `RUNTIME-DBCLASSES` tree と docs / scaffold wording のズレを解消した。
- actual emitted/promoted tree は `mtool/reference/dbclasses/` で、visible layout は top-level `data-*.php` / `dbaccess-*.php`、`base/*Base.php`、`autoload_mtool.php`、`_runtime_loader.php`、`_support/legacy-dbaccess/`、`_support/runtime-generation-manifest.json` に固定される。
- `mtool/dbclasses/_base/` / `_wrappers/` は current emitted tree には存在しない。`generated_catalog.php`、runtime build-plan、migration helper が historical self-generated bundle input を読むための compatibility layout としてのみ残る。

## 確認した事実

- `php mtool/scripts/show_runtime_reference_status.php --require-current`
  - `status=reference-snapshot-only`
  - `artifact_key=20260521-023351-d52e8c8b`
  - `durable_recovery_ready=true`
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
  - `non_plain_items=36`
  - `unclassified_non_plain_items=0`
  - non-plain 36 件は current display では `generated-existing-runtime-wrapper-base` として見える
  - historical manifest provenance は `raw_reason_code=generated-layered-runtime-wrapper-base` として保持する
- `find mtool/reference/dbclasses -maxdepth 1 -type d | sort`
  - current promoted tree の top-level directory は `base/` と `_support/` のみ
  - `_base/` / `_wrappers/` は存在しない
- representative runtime files (`data-Project.php`, `data-htmlTemplate.php`, `dbaccess-Project.php`) はいずれも `require_once __DIR__ . '/base/...Base.php';` を使っており、current emitted tree は wrapper/base 直結である
- `_runtime_loader.php` は current tree に残るが、top-level file から `mtool_runtime_bundle_load_layered_file()` は呼ばれていない

## 正規化した内容

- `docs/internal/generated-code-strategy.md`
  - current emitted `RUNTIME-DBCLASSES` file contract の source of truth として明示
  - visible layout と historical input compatibility の境界を追記
- `docs/internal/runtime-architecture.md`
  - file contract の正本を `docs/internal/generated-code-strategy.md` に寄せ、ここでは generation flow 要約に限定
- `mtool/app/README.md`
  - `generated_catalog.php` が読む `base/` / `_base/` / `_wrappers/` は historical input compatibility だと明記
- `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/README.md`
  - current emitted layout を wrapper/base 前提へ更新
  - `_base/` / `_wrappers/` を current output と誤読しない wording に修正
- `mtool/app/project_output_service.php`
  - artifact custom layer scaffold の README 文言を current emitted layout に合わせて更新
- `mtool/app/project_output_runtime_generator.php` / `mtool/scripts/show_runtime_replacement_rollout.php`
  - historical `generated-layered-runtime-wrapper-base` を current label `generated-existing-runtime-wrapper-base` へ正規化
  - CLI 表示は normalized `reason_code` を返しつつ、raw provenance を `raw_reason_code` に残す

## focused verification

- `tests/Integration/RuntimeReferenceLayoutContractTest.php`
  - promoted runtime reference が `_base/` / `_wrappers/` を持たないこと
  - promoted root `data-*.php` / `dbaccess-*.php` が `mtool_runtime_bundle_load_layered_file()` を呼ばないこと
  - runtime custom layer README / scaffold が historical layered path を input-only と説明すること
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/RuntimeReferenceLayoutContractTest.php`
  - `OK (2 tests, 415 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - `OK (4 tests, 42 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/RuntimeReplacementRolloutLaneTest.php`
  - `OK (3 tests, 843 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
  - `OK (8 tests, 273 assertions)`
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
  - current display label: `reason_code=generated-existing-runtime-wrapper-base`
  - preserved provenance: `raw_reason_code=generated-layered-runtime-wrapper-base`
- full suite baseline は 2026-05-27 のまま据え置き
  - full suite: `OK (124 tests, 4482 assertions)`

## 次の開始点

- `M1` は完了。broad rewrite current wave の next start point は `M2. DBACCESS wrapper/base migration`
- この report 時点では `M2` / `M3` / `Close` にはまだ入っていない
