# Custom Public Alias Key Storage First Slice / custom public alias key storage first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Operators can now assign a stable public alias key to an approved `NO-CODE-RUNTIME` publish candidate. The alias route serves the selected candidate through `/runs/no-code/{project_key}/alias/{alias_key}/runtime-preview.html`.

operator は approved `NO-CODE-RUNTIME` publish candidate に安定した public alias key を割り当てられる。alias route は `/runs/no-code/{project_key}/alias/{alias_key}/runtime-preview.html` で選択済み candidate を配信する。

## Changes / 変更

- Added `no_code_public_runtime_aliases` as the public alias selection table.
- Added alias key normalization/validation and repository helpers for setting and resolving aliases.
- Added a public alias runtime preview route under `/alias/{alias_key}` so it does not collide with artifact-key routes.
- Added a `Set Public Alias` operator action for approved candidates.
- Kept alias deletion, custom domains/CDN, separate rollback event stream, and package copy/static hosting out of scope.

## Boundary / 境界

- In scope: alias storage, operator/admin alias assignment, alias route lookup behavior, focused/static coverage.
- Out of scope: alias deletion/disable workflow, custom domain or CDN configuration, separate rollback event stream, package copy/static hosting.

## Verification / 検証

- PHP lint passed for:
  - `mtool/app/config_db_bootstrap.php`;
  - `mtool/app/no_code_publish_candidate_repository_pdo.php`;
  - `mtool/app/no_code_public_runtime_page.php`;
  - `mtool/app/project_source_output_detail_page.php`;
  - `mtool/app/router.php`;
  - `mtool/app/http.php`;
  - `tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`;
  - `tests/Integration/OpenApiSourceOutputContractTest.php`.
- Focused Docker-backed PHPUnit passed:
  - `NoCodePublishCandidateRepositorySqliteTest`: `13 tests, 202 assertions`;
  - `OpenApiSourceOutputContractTest`: `22 tests, 1786 assertions`.
- `git diff --check` passed.
- Full `make test` passed: `324 tests, 10635 assertions, skipped 1`.

## Next / 次

Replan the next public-delivery slice. Likely candidates are broader rollback workflow polish or public delivery closure notes.
