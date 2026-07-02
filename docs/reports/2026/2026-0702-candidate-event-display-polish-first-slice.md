# Candidate Event Display Polish First Slice / candidate event display polish first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

The existing `NO-CODE-RUNTIME` Source Output detail page now shows transition events for each publish candidate. Operators can see the transition name, from/to status, actor, timestamp, and reject reason when present.

既存の `NO-CODE-RUNTIME` Source Output detail page で、publish candidate ごとの transition event を表示するようにした。operator は transition 名、from/to status、actor、timestamp、reject reason がある場合の理由を確認できる。

## Changes / 変更

- Added `app_pdo_list_no_code_publish_candidate_transition_events(...)` to read existing append-only candidate transition events by project, source output, and revision.
- Added candidate-scoped transition event display under the Publish Candidate History table.
- Kept storage, transition states, public URL behavior, cache policy, rollback, and custom alias storage unchanged.

## Boundary / 境界

- In scope: existing transition event read path, existing detail page display, focused repository/static contract coverage.
- Out of scope: new events table, new approval states, public cache/version policy, revision rollback/selection, custom alias storage.

## Verification / 検証

- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`: passed.
- `php -l mtool/app/project_source_output_detail_page.php`: passed.
- `php -l tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`: passed.
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`: passed.
- Focused Docker-backed PHPUnit for `NoCodePublishCandidateRepositorySqliteTest`: passed (`10 tests, 138 assertions`).
- Focused Docker-backed PHPUnit for `OpenApiSourceOutputContractTest`: passed (`22 tests, 1762 assertions`).
- `git diff --check`: passed.
- Full `make test`: passed (`321 tests, 10547 assertions, skipped 1`).

## Next / 次

Replan the next public-delivery slice. Likely candidates are cache/version policy and explicit revision selection / rollback boundary.
