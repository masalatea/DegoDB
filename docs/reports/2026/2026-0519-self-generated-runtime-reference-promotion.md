# 2026-05-19 Self-Generated Runtime Reference Promotion

## later update

- same-day 後続変更で、base Docker runtime から `original-codes/` mount は削除した。
- legacy migration sample / declaration test の入力は `original-codes/mtool_lib/dbclasses` 直読ではなく、`tests/fixtures/legacy-dbclasses/` の curated copy へ切り替えた。
- 最新の boundary 整理は `docs/reports/2026/2026-0519-original-codes-host-only-enforcement.md` を参照。

## 結論

- `RUNTIME-DBCLASSES` の full self-loop は、self-generated artifact 入力でも、promoted default reference 入力でも通った。
- latest passing artifact `20260519-010720-0d539855` を `mtool/reference/dbclasses/` へ promote し、default runtime reader は self-generated reference を使う状態になった。
- `APP_GENERATED_DBCLASSES_MODE` は固定 `legacy-copy-bootstrap` ではなく、`_support/runtime-generation-manifest.json` から `self-generated-reference:canonical-dbaccess-partial-sql-regenerated` を自動判定する。

## 直したこと

- `mtool/docker/mariadb/config-seed/023_backfill_language_resource_db_access_designer_seed.sql`
  - `LanguageResource*` の designer child row 欠落を backfill し、canonical SQL regeneration が full self-loop で揃うようにした。
- `mtool/app/runtime_reference_promotion.php`
  - verified artifact bundle の `mtool/dbclasses` を `mtool/reference/dbclasses` へ atomic に差し替える helper を追加した。
- `mtool/scripts/promote_runtime_reference.php`
  - latest または指定 `artifact_key` を durable reference へ promote する CLI を追加した。
- `Makefile`
  - `make promote-runtime-reference [ARTIFACT_KEY=...]` を追加した。
- `mtool/app/config.php`
  - `APP_GENERATED_DBCLASSES_MODE` 未指定時は runtime manifest から self-generated mode を自動判定するようにした。
- `mtool/scripts/check_mtool_self_loop.php`
  - DB Access sync の expected count を hard-code せず `mtool/reference/mtool-self-loop-expected-output.json` から読むようにした。
- `compose.yaml`
  - `APP_GENERATED_DBCLASSES_MODE` の default を空にして自動判定を有効化した。
  - `original-codes/` を read-only mount し、legacy migration sample が current promoted reference と干渉しないようにした。
- `mtool/app/runtime_storage_paths.php`
  - `original-codes/mtool_lib/dbclasses` を指す helper を追加し、legacy migration sample の input path を文字列直書きしないようにした。
- `mtool/scripts/lib/sample9_*`, `sample10_*`, `sample11_*`
  - migration sample の input source を `mtool/reference/dbclasses` ではなく `original-codes/mtool_lib/dbclasses` に戻した。
- `mtool/app/*` と current docs
  - promoted self-generated tree が既定になった前提で、stale な `bootstrap copy/reference` 表現を `runtime reference` へ整理した。

## promote 実施

- 実行:
  - `php mtool/scripts/promote_runtime_reference.php --artifact-key=20260519-010720-0d539855 --requested-by=codex`
- 結果:
  - target: `mtool/reference/dbclasses`
  - promoted file count: `515`
  - promoted total bytes: `1412451`
- current default mode:
  - `self-generated-reference:canonical-dbaccess-partial-sql-regenerated`

## 検証

- self-generated artifact 入力での full self-loop
  - `APP_DB_HOST=127.0.0.1 APP_DB_PORT=33061 APP_DB_NAME=config_app APP_DB_USER=config_app APP_DB_PASSWORD=config_app_local_2026 APP_CONFIG_DB_HOST=127.0.0.1 APP_CONFIG_DB_PORT=33061 APP_CONFIG_DB_NAME=config_app APP_CONFIG_DB_USER=config_app APP_CONFIG_DB_PASSWORD=config_app_local_2026 APP_REFERENCE_ROOT=work/artifacts/source-outputs/MTOOL/20260518-071523-06754a82/bundle/mtool-source-output-runtime-dbclasses-20260518-071523-06754a82/mtool php mtool/scripts/check_mtool_self_loop.php --requested-by=self-generated-bootstrap-check`
  - pass
- promoted default reference での self-loop
  - `make mtool-self-loop-check`
  - pass
- regression
  - `make test`
  - `10 tests, 112 assertions`

## current baseline

- `mode=canonical-dbaccess-partial-sql-regenerated`
- `generated_dbaccess_count=99`
- `fallback_dbaccess_count=0`
- `canonical_function_count=611`
- `sql_regenerated_dbaccess_count=98`
- `sql_regenerated_function_count=505`
- `canonical_helper_function_count=7`
- `canonical_data_class_count=83`
- `data_entity_count=99`
- `plain_data_candidate_count=63`
- `non_plain_data_candidate_count=36`
- `bootstrap_data_class_count=16`
- `legacy_delegate_function_count=0`
- `warnings=[]`

## 残り

- `mtool/reference/dbclasses/` は promoted self-generated tree に置き換わったので、今後の旧コピー導線は rollback / recovery 用の legacy path として扱う。
- repository driver は引き続き `pdo` 既定であり、`projects` / `lab_experiments` を generated repository へ切り替える段階はまだ次段である。
