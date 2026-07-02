# Minimal Publish Candidate Persistence First Slice / minimal publish candidate persistence first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Implemented the first durable no-code publish candidate persistence slice. The slice stores a publishable `NO-CODE-RUNTIME` readiness snapshot as a draft candidate revision, then supports scoped list/find reads for operator/admin follow-up work.

no-code publish candidate persistence の最初の durable slice を実装した。`NO-CODE-RUNTIME` の publishable readiness snapshot を draft candidate revision として保存し、operator/admin の後続作業向けに scoped list/find read を提供する。

## Changes / 変更

- Added `no_code_publish_candidate_revisions` config-store table migration.
- Registered the table and core columns in config DB bootstrap preflight.
- Added `app_pdo_create_no_code_publish_candidate_from_readiness_snapshot(...)`.
- Added scoped read helpers:
  - `app_pdo_list_no_code_publish_candidates_for_source_output(...)`
  - `app_pdo_find_no_code_publish_candidate(...)`
- Added SQLite integration coverage for bootstrap, create/list/find, and fail-closed cases.

## Boundary / 境界

- In scope: durable draft candidate revisions from publishable readiness snapshots.
- In scope: project/source-output scoped list and find helpers.
- In scope: fail-closed rejection for blocked snapshots, non-`NO-CODE-RUNTIME`, artifact mismatch, and non-operator/admin actors.
- Out of scope: public runtime URL, packaging, approval transition mutations, approval UI buttons, rollback, and route-level create actions.

## Verification / 検証

- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`: passed.
- `php -l tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`: passed.
- `php -l mtool/app/config_db_bootstrap.php`: passed.
- Focused Docker-backed PHPUnit:
  - `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`
  - Passed: `4 tests, 52 assertions`.
- `make test`: passed (`315 tests, 10437 assertions, skipped 1`).

## Next / 次

Replan the next product slice after minimal candidate persistence. The likely next candidate is approval transition persistence, but it should stay separate from public URL/package exposure.
