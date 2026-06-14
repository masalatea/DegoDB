# 2026-05-28 Close verification status freeze

## 要約

- PC reboot 後に Docker が復帰したため、`Close. verification / docs / status freeze` の未完 verification を再開した。
- docs contract、representative focused PHPUnit、self-loop、full suite を current `M1-M3 DONE` 状態で再実行し、すべて green を確認した。
- `db_access_sync candidate count drift (99/611 -> 117/701)` は runtime hash mismatch ではなく、sync-only canonical-bootstrap metadata table `18` 件を self-loop assertion が見落としていたことが原因だったため、runtime `generation_summary` と `db_access_sync_summary` を分離して baseline 化した。

## verification

- `docker compose -f compose.yaml -f compose.local-db-config.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
  - `OK (12 tests, 443 assertions)`
- representative focused PHPUnit:
  - `RuntimeReferenceLayoutContractTest`: `OK (2 tests, 1910 assertions)`
  - `SelfGeneratedRuntimeResolverTest`: `OK (5 tests, 48 assertions)`
  - `RuntimeReplacementRolloutLaneTest`: `OK (3 tests, 843 assertions)`
  - `OpenApiSourceOutputContractTest`: `OK (16 tests, 1650 assertions)`
  - `ProjectDbAccessBootstrapRuntimeContractTest`: `OK (2 tests, 5 assertions)`
  - `BlobContractGuardTest`: `OK (9 tests, 10 assertions)`
- `make mtool-self-loop-check`
  - `OK`
  - artifact: `20260528-014952-e91e8147`
  - runtime `generation_summary` は `generated_dbaccess_count=99` / `canonical_function_count=611` / `canonical_data_class_count=99` / `bootstrap_data_class_count=0` / `legacy_delegate_function_count=0`
  - `db_access_sync_summary` は `total_candidate_entities=117` / `dbaccess_candidate_count=117` / `method_candidate_count=701`
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (134 tests, 6681 assertions)`

## self-loop fix

- `mtool/scripts/check_mtool_self_loop.php`
  - expected baseline に optional `db_access_sync_summary` を追加し、存在する場合はこちらを優先して `db_access_sync` summary を検証するようにした。
  - `db_access_sync_summary` が無い historical baseline に対しては、従来どおり runtime `generation_summary` から期待値を導く fallback を残した。
- `mtool/reference/mtool-self-loop-expected-output.json`
  - current sync reality として `db_access_sync_summary` を追加した。
- 切り分け結果:
  - generated runtime root 自体は top-level `dbaccess-*` / `data-*` が各 `99` 件で変化なし
  - 差分 `18` source / `90` methods は `canonical-bootstrap` の sync-only metadata tables
  - 対象:
    - `projects`
    - `project_source_outputs`
    - `project_db_access_*`
    - `project_custom_proxy_*`
    - `project_compare_outputs*`
    - `project_memberships`
    - `lab_experiments`
  - したがって今回の drift は emitted runtime contract の regression ではなく、metadata sync scope と runtime emission scope の差だった

## status freeze

- active plan `docs/reports/2026/2026-0527-broad-rewrite-temporary-closure-plan.md` は `DONE` に更新した。
- `docs/reports/2026/2026-0528-resume-prompt.md` は current frozen state と next-wave entry point を反映するよう更新した。
- broad rewrite current wave は `M1-M3 + Close` を完了し、current wave の status freeze を実施した。
