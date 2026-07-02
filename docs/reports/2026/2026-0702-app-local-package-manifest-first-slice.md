# App-local Package Manifest First Slice / app-local package manifest first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Added the first App-local package manifest artifact strategy. The new `app-local-package-manifest` Source Output strategy emits package metadata and readiness summary files around the existing App-local persistence artifact boundary without generating native installers, remote transport, conflict resolution, or a full app shell.

最初の App-local package manifest artifact strategy を追加した。新しい `app-local-package-manifest` Source Output strategy は、既存の App-local persistence artifact 境界を包む package metadata と readiness summary を生成する。native installer、remote transport、conflict resolution、full app shell はまだ生成しない。

## Changes / 変更

- Added `AppLocalPackage` as a source output class type.
- Added `app-local-package-manifest` as a generated artifact strategy.
- Added default runtime source path support under `mtool/app-local-package-source-outputs/...`.
- Added App-local package manifest generator output:
  - `app-local-package-manifest.json`
  - `app-local-package-summary.json`
  - `README.md`
- Added focused coverage for strategy registration, emitted files, manifest shape, artifact creation, and publish.

## Boundary / 境界

- In scope: manifest/summary generation from existing shared contract and App-local persistence file boundary, focused tests.
- Out of scope: native installers, archive packaging beyond normal artifact publication, remote transport, conflict resolution, background scheduler, visual builder, push.

## Verification / 検証

- `php -l mtool/app/runtime_storage_paths.php`
- `php -l mtool/app/domain_validation.php`
- `php -l mtool/app/project_output_service.php`
- `php -l mtool/app/project_output_app_local_persistence_generator.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedDataClassContractFoundationTest.php`
  - `11 tests, 534 assertions`

- `git diff --check`
- full `make test`
  - `327 tests, 10745 assertions, skipped 1`

## Next / 次

Replan after the manifest shape is stable. The next likely slice is operator package readiness display or a narrow package archive smoke.
