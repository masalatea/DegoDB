# Approval Transition Persistence First Slice / approval transition persistence first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Implemented the first durable approval transition persistence slice for no-code publish candidates. Stored candidate revisions can now move from `draft_candidate` to `review_requested`, then to `approved` or `rejected`, with an append-only transition event record.

no-code publish candidate の最初の approval transition persistence slice を実装した。保存済み candidate revision は `draft_candidate` から `review_requested`、その後 `approved` または `rejected` へ進められ、遷移は append-only event として記録される。

## Changes / 変更

- Added `no_code_publish_candidate_transition_events` config-store table migration.
- Registered the transition event table and required columns in config DB bootstrap preflight.
- Added `app_pdo_transition_no_code_publish_candidate(...)`.
- Added transition guards for:
  - project/source-output/revision scoped lookup;
  - `NO-CODE-RUNTIME` only;
  - operator/admin actor only;
  - expected current status mismatch;
  - invalid transition from current status;
  - missing reject reason.
- Added SQLite integration coverage for schema bootstrap, review request, approval, rejection, and fail-closed cases.

## Boundary / 境界

- In scope: repository-level transition persistence and event append.
- In scope: `request_review`, `approve`, and `reject`.
- In scope: expected status and actor guards.
- Out of scope: candidate route actions, approval UI buttons, public runtime URL, artifact packaging, rollback, and published revision selection.

## Verification / 検証

- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`: passed.
- `php -l mtool/app/config_db_bootstrap.php`: passed.
- `php -l tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`: passed.
- Focused Docker-backed PHPUnit:
  - `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`
  - Passed: `7 tests, 92 assertions`.
- `make test`: passed (`318 tests, 10477 assertions, skipped 1`).

## Next / 次

Replan the next product slice after approval transition persistence. The likely next candidate is a read-only/guarded route surface for candidate list/detail and transition actions, but public URL/package exposure should remain separate.
