# Public Delivery Browser Smoke First Slice / public delivery browser smoke first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Added a sample28 public runtime browser smoke that generates and publishes a `NO-CODE-RUNTIME` artifact, seeds an approved publish candidate, selects it as current, assigns the `stable` alias, and verifies artifact-key/current/alias public preview URLs through headless Chrome.

sample28 public runtime browser smoke を追加した。`NO-CODE-RUNTIME` artifact を生成・publish し、approved publish candidate、current selection、`stable` alias を seed したうえで、artifact-key / current / alias の public preview URL を headless Chrome で検証する。

## Changes / 変更

- Added `--url=...` support to `mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`.
- Added `mtool/scripts/create_no_code_public_runtime_smoke_revision.php` to seed an approved/current/alias public runtime fixture from an existing artifact.
- Added `mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh`.
- Added `make sample28-no-code-public-runtime-browser-smoke`.
- Fixed MySQL PDO duplicate named placeholder use in current/alias approved candidate lookup queries.

## Boundary / 境界

- In scope: sample28 smoke fixture, existing public runtime route behavior, cache header checks, browser checks for generated runtime semantics.
- Out of scope: new public route behavior, alias audit trail, new generated runtime UI behavior, push.

## Verification / 検証

- `make sample28-no-code-public-runtime-browser-smoke` passed.
  - Generated sample28 `NO-CODE-RUNTIME` artifact.
  - Seeded approved publish candidate, current selection, and `stable` alias.
  - Verified artifact-key URL: `/runs/no-code/SAMPLE28/{artifact_key}/runtime-preview.html`.
  - Verified current URL: `/runs/no-code/SAMPLE28/current/runtime-preview.html`.
  - Verified alias URL: `/runs/no-code/SAMPLE28/alias/stable/runtime-preview.html`.
  - Verified Cache-Control: artifact URL `public, max-age=31536000, immutable`; current/alias URLs `no-store`.
- `php -l mtool/scripts/create_no_code_public_runtime_smoke_revision.php` passed.
- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php` passed.
- `bash -n mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh` passed.
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php` passed: `14 tests, 223 assertions`.
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php` passed: `22 tests, 1795 assertions`.
- `git diff --check` passed.
- `make test` passed: `325 tests, 10665 assertions, skipped 1`.

## Next / 次

Replan the next product-facing slice. Likely candidates are alias lifecycle audit trail or a continuation outside public delivery now that public delivery route verification has browser coverage.
