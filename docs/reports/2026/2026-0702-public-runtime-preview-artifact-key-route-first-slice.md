# Public Runtime Preview Artifact-Key Route First Slice / public runtime preview artifact-key route first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Implemented the first public no-code runtime serving slice. Approved `NO-CODE-RUNTIME` candidates now expose a public artifact-key URL for `runtime-preview.html`; draft, review, rejected, missing, or mismatched artifacts remain unavailable.

approved 済み `NO-CODE-RUNTIME` candidate は、`runtime-preview.html` の public artifact-key URL を持つようになった。draft / review / rejected / missing / mismatched artifact は公開されない。

## Changes / 変更

- Added approved-candidate lookup by project/artifact key.
- Added `/runs/no-code/{project_key}/{artifact_key}/runtime-preview.html`.
- Served only the generated `runtime-preview.html` from the existing artifact bundle root.
- Added the public preview link to approved candidate package exposure.
- Kept public alias key route, custom cache policy, rollback selection, package copying, and arbitrary bundle file serving out of scope.

## Boundary / 境界

- In scope: approved candidate gate, artifact-key public preview route, existing artifact manifest/bundle storage, route/auth contract coverage, repository lookup coverage.
- Out of scope: public alias, stable published slug, new storage table, rollback/revision selection, package copy, broad static file serving.

## Verification / 検証

- `php -l mtool/app/no_code_public_runtime_page.php`: passed.
- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`: passed.
- `php -l mtool/app/project_source_output_detail_page.php`: passed.
- `php -l mtool/app/http.php`: passed.
- `php -l mtool/app/router.php`: passed.
- `php -l tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`: passed.
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`: passed.
- `git diff --check`: passed.
- Focused Docker-backed PHPUnit for `NoCodePublishCandidateRepositorySqliteTest`: passed (`8 tests, 104 assertions`).
- Focused Docker-backed PHPUnit for `OpenApiSourceOutputContractTest`: passed (`22 tests, 1754 assertions`).
- Full `make test`: passed (`319 tests, 10505 assertions, skipped 1`).

## Next / 次

Replan the next public delivery slice. Likely candidates are public alias route planning, cache/version policy, and revision selection/rollback semantics.
