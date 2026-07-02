# App-local Package Operator Readiness Display First Slice / App-local package operator readiness display first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Added operator/admin readiness visibility for `app-local-package-manifest` Source Outputs. The Source Output detail page now shows whether the latest package artifact, archive, output root, manifest file, and summary file are ready, and lists blockers when the package is not ready.

`app-local-package-manifest` Source Output 向けに operator/admin readiness visibility を追加した。Source Output detail page で latest package artifact、archive、output root、manifest file、summary file が ready かを表示し、package が ready でない場合は blocker を表示する。

## Changes / 変更

- Added a narrow App-local package readiness helper to the Source Output detail page.
- Added an `App-local Package Readiness` section for `app-local-package-manifest` strategies.
- Displayed latest artifact key, archive availability, output root state, manifest state, summary state, and blockers.
- Added static route/source contract assertions for the readiness section.

## Boundary / 境界

- In scope: operator/admin read-only readiness display for existing App-local package artifacts.
- Out of scope: new package generation behavior, native installers, new archive format, app shell packaging, remote sync transport, push.

## Verification / 検証

- `php -l mtool/app/project_source_output_detail_page.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
  - `22 tests, 1802 assertions`
- `git diff --check`
- full `make test`
  - `327 tests, 10765 assertions, skipped 1`

## Next / 次

Run `git diff --check` and full `make test` before local commit. After this slice, replan whether to close the packaging lane or add a focused UI/browser smoke.
