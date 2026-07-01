# 2026-0629 App-local Persistence Source Output Artifacts

Status: `COMPLETED`

## Summary

App-local persistence を generated Source Output artifact として扱えるようにした。

`shared-contract-manifest-v0` から以下を出力する `app-local-persistence-php` strategy を追加した。

- `schema.sql`
- `app-local-contract.json`
- `app-local-summary.json`
- `AppLocalPersistence.php`
- `README.md`

`AppLocalPersistence.php` は generated PHP wrapper で、runtime が `app_local_sqlite_schema.php` と `app_local_sqlite_dbaccess.php` を読み込んでいる前提で `applySchema()`, `save()`, `read()` を提供する。

## Implemented

- Added `mtool/app/project_output_app_local_persistence_generator.php`.
- Added `app-local-persistence-php` to Source Output strategy validation, captions, generation support, runtime-source requirement, customization model, and create-from-definition dispatch.
- Added runtime source path helper:
  - `mtool/app-local-persistence-source-outputs/<PROJECT>/<SOURCE_OUTPUT>`
- Extended `SharedDataClassContractFoundationTest` to verify:
  - strategy allow-list / caption / generation support
  - generated artifact tree
  - JSON contract / summary payloads
  - SQLite schema text
  - generated PHP wrapper class
  - in-memory SQLite apply / save / read round trip through the generated wrapper
  - `app_project_output_create_from_definition()` artifact packaging path
- Added sample27 Source Output seed:
  - `APP-LOCAL-PERSISTENCE`
  - `AppLocalPersistence`
  - `app-local-persistence-php`
- Added sample27 standard companion `AI-CONTEXT-MD` row to keep the tutorial Source Output seed aligned with the AI context rollout contract.
- Extended sample27 verification to generate the Source Output artifact and assert strategy, key, and file count.

## Design Notes

This keeps the existing boundary from the feasibility study:

- DataClass remains implementation-facing generated class/DTO shape.
- Shared contract remains the language-neutral manifest carrying persistence semantics.
- App-local persistence is a separate generated artifact derived from the shared contract, not a flag on generated DataClass.

This also means managed operation metadata can build on shared contract and App-local persistence without coupling operation semantics to DataClass output.

## Verification

- `php -l mtool/app/project_output_app_local_persistence_generator.php`
- `php -l mtool/app/project_output_service.php`
- `php -l mtool/app/domain_validation.php`
- `php -l mtool/app/runtime_storage_paths.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `php -l mtool/scripts/lib/sample27_app_local_persistence_demo_check.php`
- `php -l tests/Integration/Sample27AppLocalPersistenceDemoTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/SharedDataClassContractFoundationTest.php`
  - `OK (6 tests, 223 assertions)`
- `make sample27-pack-runtime-test`
  - `OK (1 test, 11 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 280, Assertions: 9440, Skipped: 1.`

## Next

Move to the Managed data operation layer:

- operation metadata model
- operation field model
- permission policy binding
- sync skeleton around server-copy / local-copy semantics
