# 2026-05-19 Runtime Reference Terminology Cleanup

## later update

- same-day 後続変更で、legacy migration sample / declaration test の入力は `original-codes/` 直読から `tests/fixtures/legacy-dbclasses/` の curated copy へ切り替えた。
- `app_runtime_storage_legacy_dbclasses_*()` helper は一時的に host-side recovery / audit 用 path として置いたが、same-day 後続変更で live code / test から未使用と確認できたため削除した。test mainline は fixture path を使う。
- 詳細は `docs/reports/2026/2026-0519-original-codes-host-only-enforcement.md` を参照。

## 背景

- `2026-0519-self-generated-runtime-reference-promotion.md` で、`mtool/reference/dbclasses/` は promoted self-generated tree を既定の runtime reference として使う状態になった。
- その後も current app / docs に `bootstrap copy` / `bootstrap reference` / `bootstrap source` といった旧状態前提の文言が残っていたため、現在の挙動に合わせて整理した。
- 併せて legacy migration sample が `original-codes/mtool_lib/dbclasses` を読む path を helper 化し、`mtool/reference/dbclasses` と混同しないようにした。

## 今回の変更

- `mtool/app/runtime_storage_paths.php`
  - `app_runtime_storage_legacy_dbclasses_relative_path()`
  - `app_runtime_storage_legacy_dbclasses_root()`
  - `app_runtime_storage_legacy_dbclasses_path()`
  - を追加し、legacy dbclasses input path を共通化した。
- `mtool/scripts/lib/sample9_*`, `sample10_*`, `sample11_*`
  - `original-codes/mtool_lib/dbclasses/...` の文字列直書きをやめ、上記 helper を使うようにした。
- `mtool/app/*`
  - DB Access / Data Class / Table / Source Output まわりの UI 文言を `runtime reference` 前提へ揃えた。
  - `Generated Bootstrap` / `Bootstrap Reference` / `bootstrap source preview` などの見出し・説明は current 状態に合う表現へ置き換えた。
- `docs/*`
  - `overview.md`
  - `generated-code-strategy.md`
  - `mtool-admin-roadmap.md`
  - `runtime-architecture.md`
  - `data-model.md`
  - `source-output-path-policy.md`
  - を更新し、現在状態を誤って `bootstrap copy` と説明していた箇所を `runtime reference` / `legacy recovery copy` ベースへ直した。

## ApacheHostSetting の扱い

- `ApacheHostSetting` / `ApacheHostSettingTemplate` の旧用途は、`data-ApacheHostSetting.php` の `InitializeByTemplate()` と `original-codes/mtool_lib/lib_mtool_apache.php` が示す通り、Apache config filename / access log / error log / template 本文の組み立てだった。
- current 側で必要な host assignment は `project_host_assignments` landing zone に残っているため、Apache template 専用 class を runtime/self-loop へ戻す必要はない。
- したがって、`2026-0519-apache-host-setting-runtime-exclusion.md` の方針は維持してよい。

## 検証

- `make mtool-self-loop-check`
  - pass
  - artifact key: `20260519-024441-5291f6e3`
  - generation summary:
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
- same-day code change 時点では `make test` も `10 tests, 112 assertions` で通過済み。

## 補足

- `bootstrap` という文字列自体はすべて削除していない。
- 意図的に残しているのは、`make bootstrap-dbclasses`、`sync-bootstrap`、`non-plain-bootstrap`、`bootstrap.php` のように command / enum / reason code / file name として既に意味を持つものだけである。
- current UX / current docs では、これらを「default runtime source の説明」として誤用しない状態に揃えた。
