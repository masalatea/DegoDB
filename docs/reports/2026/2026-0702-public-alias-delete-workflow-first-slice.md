# Public Alias Delete Workflow First Slice / public alias delete workflow first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Operators can now list and delete configured public runtime aliases for `NO-CODE-RUNTIME`. Deleting an alias removes its row, so the corresponding alias route no longer resolves to an approved candidate.

operator は `NO-CODE-RUNTIME` の public runtime alias を一覧表示し、削除できる。alias を削除すると row が消えるため、対応する alias route は approved candidate を解決しなくなる。

## Changes / 変更

- Added public runtime alias list and delete repository helpers.
- Added `Public Runtime Aliases` UI with `Delete Public Alias` guarded by the existing operator/admin actor policy.
- Kept candidate approval history and candidate records intact when an alias is deleted.
- Kept soft-delete history, alias deletion events, custom domains/CDN, and package copy/static hosting out of scope.

## Boundary / 境界

- In scope: alias listing, operator/admin alias deletion, route deactivation via row deletion, focused/static coverage.
- Out of scope: soft-delete history, alias deletion event stream, custom domain or CDN configuration, package copy/static hosting.

## Verification / 検証

- PHP lint passed for:
  - `mtool/app/no_code_publish_candidate_repository_pdo.php`;
  - `mtool/app/project_source_output_detail_page.php`;
  - `tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`;
  - `tests/Integration/OpenApiSourceOutputContractTest.php`.
- Focused Docker-backed PHPUnit passed:
  - `NoCodePublishCandidateRepositorySqliteTest`: `14 tests, 223 assertions`;
  - `OpenApiSourceOutputContractTest`: `22 tests, 1792 assertions`.
- `git diff --check` passed.
- Full `make test` passed: `325 tests, 10662 assertions, skipped 1`.

## Next / 次

Replan the next public-delivery slice. Likely candidates are broader rollback workflow polish or public delivery closure notes.
