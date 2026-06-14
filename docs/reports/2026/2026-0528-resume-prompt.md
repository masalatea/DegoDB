# 2026-05-28 Resume Prompt

2026-05-28 の current stop point からそのまま再開するための prompt。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。2026-05-28 の停止点から継続してください。

現状:
- broad rewrite current wave の execution plan は `docs/reports/2026/2026-0527-broad-rewrite-temporary-closure-plan.md` の 1 本で、status は `DONE`
- `M1. runtime contract truth normalization` は `DONE`
- `M2. DBACCESS wrapper/base migration` は `DONE`
- `M3. canonical generated data-* wrapper/base migration` は `DONE`
- `Close. verification / docs / status freeze` は `DONE`
- permanent docs の英語 companion 追加、top-level / internal docs の整理、docs rule 明文化、docker-based verification、status freeze まで完了
- current emitted `RUNTIME-DBCLASSES` file contract の source of truth は `docs/internal/generated-code-strategy.md`
- current promoted `mtool/reference/dbclasses/` tree は top-level `data-*.php` / `dbaccess-*.php`、`base/*Base.php`、`autoload_mtool.php`、`_runtime_loader.php`、`_support/legacy-dbaccess/`、`_support/runtime-generation-manifest.json`
- current emitted tree には `_base/` / `_wrappers/` は無い。historical self-generated bundle input compatibility としてだけ reader/build-plan helper が扱う
- `show_runtime_replacement_rollout.php --non-plain-only` の current display は `reason_code=generated-existing-runtime-wrapper-base` を返し、historical provenance は `raw_reason_code=generated-layered-runtime-wrapper-base` に残す
- `original-codes/` は host-side reference only。runtime input に戻さない

2026-05-28 に追加で完了したこと:
- `docs/internal/generated-code-strategy.md`、`docs/internal/runtime-architecture.md`、`mtool/app/README.md`、`mtool/extensions/MTOOL/RUNTIME-DBCLASSES/README.md`、`mtool/app/project_output_service.php` を current emitted contract に同期
- `tests/Integration/RuntimeReferenceLayoutContractTest.php` を追加
- promoted runtime reference が `_base/` / `_wrappers/` を持たず、root runtime file が `mtool_runtime_bundle_load_layered_file()` を呼ばないことを focused test で固定
- `mtool/app/project_output_runtime_generator.php` と `mtool/scripts/show_runtime_replacement_rollout.php` に historical reason code alias の正規化を追加
- slice report `docs/reports/2026/2026-0528-runtime-contract-truth-normalization.md` を追加
- `mtool/app/project_output_runtime_generator.php` を更新し、`legacy_delegate_function_count=0` の current DBACCESS base が legacy 親を持たないようにした
- promoted `mtool/reference/dbclasses/base/dbaccess-*Base.php` から legacy support require/extends を除去し、`_support/legacy-dbaccess/dbaccess-*.php` を compatibility placeholder class へ正規化した
- representative self-loop digest baseline (`mtool/reference/mtool-self-loop-expected-output.json`) を更新した
- slice report `docs/reports/2026/2026-0528-dbaccess-legacy-support-normalization.md` を追加
- promoted `data-*.php` / `base/data-*Base.php` 全件と manifest `bootstrap_data_class_count=0` を `RuntimeReferenceLayoutContractTest` で固定した
- slice report `docs/reports/2026/2026-0528-canonical-data-wrapper-base-completion.md` を追加
- `README.md`、`docs/*.md`、`docs/internal/*.md` の permanent docs 全 `27` file に `English companion:` 冒頭節を追加した
- `docs/README.md` と `docs/internal/README.md` に `恒久 docs = 英語 companion 付き / docs/reports = 日本語のみ` rule と `top-level docs は外部ユーザ向け / internal docs は 1 段内側` の整理を明文化した
- `tests/Integration/DocsEntranceContractTest.php` に permanent docs の `English companion:` と docs language rule を固定する assertion を追加した
- slice report `docs/reports/2026/2026-0528-close-docs-normalization-progress.md` を追加した
- `rg -c "English companion:" README.md docs/*.md docs/internal/*.md` で permanent docs 全件への反映を確認した
- `DocsEntranceContractTest` は Docker 復旧後の再実行で `OK (12 tests, 443 assertions)` を確認した
- representative focused verification (`RuntimeReferenceLayoutContractTest`、`SelfGeneratedRuntimeResolverTest`、`RuntimeReplacementRolloutLaneTest`、`OpenApiSourceOutputContractTest`、`ProjectDbAccessBootstrapRuntimeContractTest`、`BlobContractGuardTest`) は全件 green を再確認した
- `db_access_sync` は generated runtime `99/611` とは別に canonical-bootstrap metadata table `18` 件を含む `117/701` を返すことを切り分け、`mtool/scripts/check_mtool_self_loop.php` と `mtool/reference/mtool-self-loop-expected-output.json` に `db_access_sync_summary` baseline を追加した
- `make mtool-self-loop-check` は `OK` に戻した
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` は `OK (134 tests, 6681 assertions)` を確認した
- slice report `docs/reports/2026/2026-0528-close-verification-status-freeze.md` を追加し、plan / resume を current frozen state に更新した

次の最優先:
1. `non-plain bootstrap data-*` の圧縮を次 wave の先頭候補として再開する
2. runtime loader / custom bootstrap の整理へ進む
3. self-host / authoritative runtime switch の前提を詰める
4. page security / host assignment / HTML / Source Output bridge debt の最終吸収順を再確認する

最初に読む文書:
- `docs/reports/2026/2026-0527-broad-rewrite-temporary-closure-plan.md`
- `docs/reports/2026/2026-0518-mtool-runtime-wrapper-base-migration-plan.md`
- `docs/reports/2026/2026-0528-runtime-contract-truth-normalization.md`
- `docs/reports/2026/2026-0528-dbaccess-legacy-support-normalization.md`
- `docs/reports/2026/2026-0528-canonical-data-wrapper-base-completion.md`
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`

focused verification baseline:
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
  - `OK (12 tests, 443 assertions)` on `2026-05-28`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/RuntimeReferenceLayoutContractTest.php`
  - `OK (2 tests, 1910 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - `OK (5 tests, 48 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/RuntimeReplacementRolloutLaneTest.php`
  - `OK (3 tests, 843 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
  - `OK (16 tests, 1650 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ProjectDbAccessBootstrapRuntimeContractTest.php`
  - `OK (2 tests, 5 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/BlobContractGuardTest.php`
  - `OK (9 tests, 10 assertions)`
- `make mtool-self-loop-check`
  - `OK` on `2026-05-28`
  - runtime artifact hash / generation summary は expected と一致
  - `db_access_sync_summary` baseline は `total_candidate_entities=117` / `dbaccess_candidate_count=117` / `method_candidate_count=701`
  - 上記 `117/701` は runtime emitted `99/611` とは別に、sync-only canonical-bootstrap metadata table `18` 件を含む current metadata sync reality として固定した

full suite baseline:
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (134 tests, 6681 assertions)` on `2026-05-28`
```
