# Public Runtime Current Alias Route First Slice / public runtime current alias route first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Implemented the first stable public no-code runtime alias. `/runs/no-code/{project_key}/current/runtime-preview.html` now resolves to the latest approved `NO-CODE-RUNTIME` publish candidate and serves its generated `runtime-preview.html`.

`/runs/no-code/{project_key}/current/runtime-preview.html` が、最新 approved の `NO-CODE-RUNTIME` publish candidate を解決し、その generated `runtime-preview.html` を公開するようになった。

## Changes / 変更

- Added current approved candidate lookup for `NO-CODE-RUNTIME`.
- Added the public `current` runtime preview route before the artifact-key route so `current` is not treated as an artifact key.
- Added the current preview link to approved candidate package exposure.
- Kept custom alias keys, rollback selection, new storage tables, package copying, and custom cache/version policy out of scope.

## Boundary / 境界

- In scope: latest approved candidate gate, project-level current alias route, existing artifact manifest/bundle storage, route/auth contract coverage, repository lookup coverage.
- Out of scope: custom alias key route, stable slug storage, explicit published revision table, rollback/revision selection, package copy, broad static file serving.

## Verification / 検証

- `php -l mtool/app/no_code_public_runtime_page.php`: passed.
- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`: passed.
- `php -l mtool/app/project_source_output_detail_page.php`: passed.
- `php -l mtool/app/http.php`: passed.
- `php -l mtool/app/router.php`: passed.
- `php -l tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`: passed.
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`: passed.
- `git diff --check`: passed.
- Focused Docker-backed PHPUnit for `NoCodePublishCandidateRepositorySqliteTest`: passed (`9 tests, 120 assertions`).
- Focused Docker-backed PHPUnit for `OpenApiSourceOutputContractTest`: passed (`22 tests, 1759 assertions`).
- Full `make test`: passed (`320 tests, 10526 assertions, skipped 1`).

## Next / 次

Replan the next public delivery slice. Likely candidates are cache/version policy, explicit revision selection / rollback boundary, custom public alias key storage, and candidate event display polish.
