# 2026-05-26 Progress Snapshot

## 要約

- canonical metadata export/import、secret separation、config DB externalization、OpenAPI spec visibility control は current planned scope として完了した。
- config DB compose lane まわりの cleanup も完了し、local overlay / external lane / helper commonization / access URL policy / external parity scope の判断が一通り閉じた。
- contributor 向けの goal-based 入口として `docs/choose-your-path.md` を追加し、root/doc index の導線を補強した。
- `docs/project-metadata-bundle.md` と `docs/config-db-externalization.md` を追加し、bundle / externalization の stable rule を恒久文書へ昇格した。
- `docs/troubleshooting.md` を追加し、current lane の warning / error 切り分けを恒久文書へ昇格した。
- generated proxy auth model も review 済みで、fail-closed hardening を入れた current model を当面維持する判断に閉じた。
- OpenAPI public alias key / raw delivery policy も検討済みで、current slice では実装しない decision を固定した。

## 2026-05-27 status normalization update

- broad rewrite current wave の active execution plan は `2026-0527-broad-rewrite-temporary-closure-plan.md` の 1 本だけに固定した。
- active milestone は `[ACTIVE] M1. runtime contract truth normalization` とする。
- `[PENDING] M2. DBACCESS wrapper/base migration`、`[PENDING] M3. canonical generated data-* wrapper/base migration`、`[PENDING] Close. verification / docs / status freeze` を後続 milestone に固定した。
- `Close` の docs rule として、一般/permanent docs は日英併記、progress/handoff docs は日本語のみ可に固定した。
- 関連計画の status は次へ正規化した。
  - `2026-0518-mtool-runtime-wrapper-base-migration-plan.md`: `PENDING`
  - `2026-0514-functional-migration-vs-self-host-plan.md`: `PENDING`
  - `2026-0511-gradual-legacy-absorption-plan.md`: `PENDING`
  - `2026-0511-self-host-import-loop-plan.md`: `DONE`
- `M1` から `M3` まで完了した時点を broad rewrite current wave の中間完了とし、`Close` をもって status freeze する。

## current checkpoint

- export/import
  - project-scoped bundle export CLI: implemented
  - import CLI preview/apply: implemented
  - database source secret separation / `password_env`: implemented
- config DB externalization
  - `APP_CONFIG_DB_*` preflight / migrate: implemented
  - admin canonical metadata routing to `config_db`: implemented
  - base `compose.yaml` without `db-config`: implemented
  - local overlay `compose.local-db-config.yaml`: implemented
  - external lane convenience targets: implemented
- compose lane decisions
  - sample/helper compose stack merge commonized via `mtool/scripts/list_compose_stack_files.sh`
  - `show_compose_access_urls.sh`: keep base-only default
  - external lane `start/stop/reset/shell` parity: not adopted
- docs onboarding
  - `docs/choose-your-path.md`: added
  - `README.md` / `docs/start-here.md` / `docs/README.md`: linked
  - `DocsEntranceContractTest`: link contract updated
- docs promotion
  - `docs/project-metadata-bundle.md`: added
  - `docs/config-db-externalization.md`: added
  - `docs/current-supported-workflow.md` / `docs/common-tasks.md`: trimmed to hub-style wording
- docs troubleshooting
  - `docs/troubleshooting.md`: added
  - root/doc/task guides: linked
  - `DocsEntranceContractTest`: link contract updated
- OpenAPI
  - `project_source_outputs.spec_visibility`: implemented
  - authenticated viewer/internal artifact only: current policy
  - generated proxy auth model: reviewed, keep current hardened model for now
  - public alias/raw route: `[DONE]` reviewed and intentionally not implemented in current slice

## current status commands

- `php mtool/scripts/show_runtime_reference_status.php --require-current`
  - `status=reference-snapshot-only`
  - `durable_recovery_ready=true`
  - promoted artifact key: `20260521-023351-d52e8c8b`
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
  - `non_plain_items=36`
  - `unclassified_non_plain_items=0`
  - all non-plain items remain sample-gated
- `make help | sed -n '1,90p'`
  - local lane and external lane target split is visible
  - external lane exposes `up/ps/logs/health/config-db-preflight/db-config-migrate/down`

## verification baseline

- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ConfigDbExternalizationContractTest.php`
  - `OK (5 tests, 59 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/LabDbIngressContractTest.php`
  - `OK (4 tests, 52 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
  - `OK (6 tests, 118 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SamplePackCatalogTest.php`
  - pass
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
  - `OK (16 tests, 1646 assertions)`
- `make sample-pack-compose-smoke`
  - `17 pack(s)` pass
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (124 tests, 4482 assertions)`

## next trigger

- 新しい auth requirement が出た時だけ `proxy auth v2` を再検討する。
- 新しい共有要件が出た時だけ OpenAPI public alias key / raw delivery の検討を再開する。
