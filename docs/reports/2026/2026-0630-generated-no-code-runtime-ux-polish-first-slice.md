# Generated No-Code Runtime UX Polish First Slice

Status: `FIRST_SLICE_DONE`

Date: 2026-06-30

## Scope

Completed the first generated no-code runtime UX polish slice selected by the post-sample28 product-goal replan.

The slice improves the generated Web / HTML runtime preview without changing the metadata model, adding a visual builder, adding a new sample domain, or expanding app-local sync behavior.

## Implementation

- Added human-readable generated screen titles and subtitles to the runtime render model.
- Kept stable `screen_key` / `data-screen-key` identifiers for tests and runtime lookup.
- Added deterministic empty-state copy for list/detail/form previews.
- Added a browser-visible action feedback region for generated dispatch results.
- Updated the browser smoke profile expectations to check human-readable generated labels.
- Added PHPUnit coverage for generated screen title/subtitle metadata, empty-state HTML, and action feedback markup.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml --run-script=sample/tutorials/sample07-dbaccess-crud-basic/run.sh --phpunit-target=/var/www/tests/Integration/NoCodeRuntimeTest.php`
- `make sample07-no-code-runtime-ui-smoke`
- `make sample28-no-code-runtime-ui-smoke`
- `make sample28-pack-runtime-test`
- `make test`

`make test` result: 296 tests, 9877 assertions, 1 skipped.

## Next

Continue with generated runtime state polish follow-up: finer loading/working/error state behavior only where the current runtime already has enough deterministic state.
