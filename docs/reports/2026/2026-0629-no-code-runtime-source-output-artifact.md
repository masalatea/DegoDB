# 2026-06-29 No-Code Runtime Source Output Artifact

## Status

- implementation status: `IMPLEMENTED`
- current-plan link: `docs/current-plans.md` No-Code Completion Breakdown steps 1-2
- next step: sample connection for no-code runtime artifact

## Purpose

Make the first no-code runtime adapter available as a generated Source Output artifact.

The previous slices added `no-code-screen-definition-v0` and `no-code-runtime-v0` in code. This slice publishes those definitions into generated JSON files so sample packs and later runtime smoke tests can consume them through the normal artifact path.

## Implemented Scope

- Added `no-code-runtime-json` Source Output artifact strategy.
- Added `NoCodeRuntime` source output class type.
- Added `mtool/app/project_output_no_code_runtime_generator.php`.
- Added runtime source path:
  - `mtool/no-code-runtime-source-outputs/{PROJECT_KEY}/{SOURCE_OUTPUT_KEY}`
- Generated files:
  - `screen-definition.json`
  - `runtime-preview.json`
  - `runtime-preview.html`
  - `README.md`
- Connected `project_output_service` artifact creation dispatch.
- Verified artifact staging, artifact creation, and publish path.

## Boundary

This is an artifact/publish slice.

It does not yet add a tutorial sample seed, durable sample reference output, browser UI renderer, or persisted create/update smoke. Those remain the next steps in `docs/current-plans.md`.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/app/domain_validation.php`
- `php -l mtool/app/project_output_service.php`
- `php -l mtool/app/runtime_storage_paths.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml --run-script=sample/tutorials/sample07-dbaccess-crud-basic/run.sh --phpunit-target=/var/www/tests/Integration/SharedDataClassContractFoundationTest.php`
  - `OK (8 tests, 313 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 291, Assertions: 9774, Skipped: 1.`

## Result

No-code runtime definitions now have the same generated artifact path as shared contract, App-local persistence, and managed operation metadata.

The next useful slice is to connect this artifact to a sample source output and verify the generated files through a sample run.
