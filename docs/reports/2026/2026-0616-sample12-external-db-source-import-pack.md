# 2026-06-16 Sample12 External DB Source Import Pack

## Status

- status: `DONE`
- target lane: `sample/tutorials/`
- target pack: `sample12-external-db-source-import`
- project key: `SAMPLE12`
- main flow: external named DB source -> table import -> DataClass sync -> `DATACLASS-PHP` publish

## Summary

`sample12-external-db-source-import` を user-facing tutorial runtime pack として追加した。

この pack は、一般ユーザーが自分の DB を DegoDB に接続する最初の実用導線を固定する。`database_sources.source_key=sample12_lab` を登録し、`db-lab` 側の `ExternalArticle` table を `named-live-schema:sample12_lab` から import し、DataClass sync と Source Output publish まで進める。

## Added Files

- `sample/tutorials/sample12-external-db-source-import/`
  - `compose.yaml`
  - `run.sh`
  - `README.md`
  - `seed/`
  - `lab-seed/`
  - `reference/DATACLASS-PHP/`
- `mtool/scripts/check_sample12_external_db_source_import_outputs.php`
- `mtool/scripts/lib/sample12_external_db_source_import_output_check.php`
- `tests/Integration/Sample12ExternalDbSourceImportOutputTest.php`
- `docs/study/external-db-source-import.md`

## Verification

- `make sample12-pack-runtime-test`
  - `OK (1 test, 8 assertions)`
- `bash mtool/scripts/check_sample_pack_compose_smoke.sh`
  - `validated runtime sample pack compose smoke: 15 pack(s)`
- `make test`
  - `OK (139 tests, 6602 assertions)`
- `git diff --check`
  - passed

## Notes

- `LanguageResource` / i18n is out of scope for this tutorial lane.
- The reference output is actual generated output copied from `work/source-outputs/SAMPLE12/DATACLASS-PHP/`.
- The checker prepares the external fixture idempotently so both the focused sample12 stack and the full shared sample test stack can exercise the same import flow.
