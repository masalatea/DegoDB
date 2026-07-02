# Explicit Current Public Revision Selection First Slice / explicit current public revision selection first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Operators can now explicitly select which approved `NO-CODE-RUNTIME` publish candidate backs the project-level `current` public runtime preview. If no explicit selection exists, the current route keeps the previous latest-approved fallback.

operator は project-level `current` public runtime preview が参照する approved `NO-CODE-RUNTIME` publish candidate を明示選択できる。明示選択がない場合は、従来通り latest-approved fallback を使う。

## Changes / 変更

- Added `no_code_public_runtime_current_revisions` as the current public revision selection table.
- Added `app_pdo_select_current_no_code_publish_candidate(...)` with approved-candidate and operator/admin guards.
- Updated current approved candidate lookup to prefer explicit selection before latest-approved fallback.
- Added a `Set Current Public Revision` operator action for approved non-current candidates.
- Kept custom alias storage, new public URL shapes, package copy/static hosting, and a broader rollback workflow out of scope.

## Boundary / 境界

- In scope: explicit current selection storage, operator/admin selection action, current route lookup behavior, focused/static coverage.
- Out of scope: custom alias key storage, separate rollback event stream, package copy/static hosting, new public URL shapes.

## Verification / 検証

- PHP lint passed for:
  - `mtool/app/config_db_bootstrap.php`;
  - `mtool/app/no_code_publish_candidate_repository_pdo.php`;
  - `mtool/app/project_source_output_detail_page.php`;
  - `tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`;
  - `tests/Integration/OpenApiSourceOutputContractTest.php`.
- Focused Docker-backed PHPUnit passed:
  - `NoCodePublishCandidateRepositorySqliteTest`: `11 tests, 160 assertions`;
  - `OpenApiSourceOutputContractTest`: `22 tests, 1778 assertions`.
- `git diff --check` passed.
- Full `make test` passed: `322 tests, 10585 assertions, skipped 1`.

## Next / 次

Replan the next public-delivery slice. Likely candidates are custom public alias key storage or broader rollback workflow polish.
