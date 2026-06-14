# 2026-05-28 DBACCESS legacy support normalization

## 要約

- `M2. DBACCESS wrapper/base migration` の completion slice として、current `RUNTIME-DBCLASSES` の DBACCESS base / legacy support contract を sample1-style wrapper/base 実態へ揃えた。
- `legacy_delegate_function_count=0` の current promoted/runtime artifact では、`base/dbaccess-*Base.php` が `Legacy` 親を持たず standalone になるよう generator を修正した。
- `_support/legacy-dbaccess/dbaccess-*.php` は circular wrapper ではなく、delegate 不要時の standalone compatibility placeholder に正規化した。

## 変更内容

- `mtool/app/project_output_runtime_generator.php`
  - generated `dbaccess-*` stage file は、legacy delegate が残る時だけ `require_once ../_support/legacy-dbaccess/...` と `extends *Legacy` を出すようにした。
  - current runtime source が wrapper/base shell しか持たない場合は、`_support/legacy-dbaccess/` へ standalone placeholder class を出すようにした。
  - future に delegate が必要なのに valid legacy support source を解決できない場合は、warning を出して runtime reference fallback に戻す guard を追加した。
- `mtool/reference/dbclasses/base/dbaccess-*Base.php`
  - promoted runtime reference の DBACCESS base を standalone 化し、legacy parent require/extends を除去した。
  - representative digest baseline (`mtool/reference/mtool-self-loop-expected-output.json`) を更新した。
- `mtool/reference/dbclasses/_support/legacy-dbaccess/dbaccess-*.php`
  - placeholder class へ正規化し、`_runtime_loader.php` / `base/...` require と wrapper loader dependency を除去した。
- docs / UI wording
  - `docs/internal/generated-code-strategy.md`
  - `docs/internal/runtime-architecture.md`
  - `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/README.md`
  - `mtool/app/project_output_service.php`
  - `mtool/app/project_source_output_detail_page.php`
  - 現在の DBACCESS base は legacy delegate が残る時だけ support を使う、という説明へ揃えた。

## focused verification

- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/RuntimeReferenceLayoutContractTest.php`
  - `OK (2 tests, 1207 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - `OK (5 tests, 48 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
  - `OK (16 tests, 1650 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ProjectDbAccessBootstrapRuntimeContractTest.php`
  - `OK (2 tests, 5 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/BlobContractGuardTest.php`
  - `OK (9 tests, 10 assertions)`

## self-loop check

- `make mtool-self-loop-check`
  - runtime artifact generation 自体は `OK`
  - `generation_summary` は expected と一致
  - representative runtime file hash (`dbaccess-Project.php`, `base/dbaccess-ProjectBase.php`, `dbaccess-dbtable.php`, `base/dbaccess-dbtableBase.php`) は expected と一致
  - ただしこの slice 時点では、`db_access_sync candidate count drift (99/611 -> 117/701)` により全体結果は `NG` だった
  - drift の切り分けと baseline 整理は follow-up の `docs/reports/2026/2026-0528-close-verification-status-freeze.md` に記録した

## 判断

- 今回の `NG` は DBACCESS wrapper/base / legacy support 修正の hash mismatch ではなく、live metadata sync の candidate count drift によるものである。
- `M2` の対象だった runtime DBACCESS contract と representative digest は揃ったため、current wave では `M2` を完了扱いにしてよい。
- 上記 drift の rebaseline / 最終 close 判定は `Close` で扱う前提とし、この文書では `M2` slice 固有の変更点に留める。
