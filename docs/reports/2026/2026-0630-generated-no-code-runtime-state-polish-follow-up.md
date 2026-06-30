# Generated No-Code Runtime State Polish Follow-Up

Status: `FOLLOW_UP_DONE`

Date: 2026-06-30

## Scope

Completed the generated no-code runtime state polish follow-up.

This follow-up stays inside the existing generated Web / HTML runtime preview surface. It does not change the metadata model, add a visual builder, add a new sample domain, or expand app-local sync behavior.

## Implementation

- Added deterministic preview state to generated HTML with `data-runtime-state="ready|error"`.
- Added preview-level status badge copy such as `Preview ready`.
- Added screen-level state with `data-screen-state="ready|empty"`.
- Added screen state badges for `Ready`, `Empty`, `No detail`, and `No data`.
- Added action state attributes for generated buttons with `data-action-state="ready|disabled|working|success|error"`.
- Added click-time action feedback transition: idle -> working -> success/error.
- Extended the browser smoke to verify runtime state, empty screen state, and idle feedback state.
- Extended PHPUnit coverage for state attributes and state copy.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml --run-script=sample/tutorials/sample07-dbaccess-crud-basic/run.sh --phpunit-target=/var/www/tests/Integration/NoCodeRuntimeTest.php`
- `make sample07-no-code-runtime-ui-smoke`
- `make sample28-no-code-runtime-ui-smoke`
- `make sample28-pack-runtime-test`
- `make test`

`make test` result: 296 tests, 9884 assertions, 1 skipped.

## Next

The generated runtime UX/state polish lane is complete for this small product-facing pass. The next no-code work should be replanned from the next product goal before implementation starts.
