# App-local Package Archive Smoke First Slice / app-local package archive smoke first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Added focused archive smoke coverage for the generated `app-local-package-manifest` artifact. The smoke verifies that the generated `.tar.gz` archive exists, can be listed, can be extracted, and contains the expected package manifest and summary files.

generated `app-local-package-manifest` artifact 向けの focused archive smoke coverage を追加した。生成された `.tar.gz` archive が存在し、list でき、extract でき、期待する package manifest / summary files を含むことを検証する。

## Changes / 変更

- Extended `SharedDataClassContractFoundationTest` for the App-local package manifest artifact.
- Asserted archive existence and archive entry names.
- Extracted the generated archive into a temporary directory.
- Read the extracted `app-local-package-manifest.json` and verified the package manifest version and included file count.

## Boundary / 境界

- In scope: focused archive list/extract smoke for the existing `app-local-package-manifest` artifact path.
- Out of scope: native installers, new archive format, app shell packaging, operator/admin readiness UI, remote sync transport, push.

## Verification / 検証

- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedDataClassContractFoundationTest.php`
  - `11 tests, 550 assertions`
- `git diff --check`
- full `make test`
  - `327 tests, 10761 assertions, skipped 1`

## Next / 次

Replan after archive confidence. The likely next small slice is operator package readiness display, unless the packaging lane should close and move back to a broader no-code product goal.
