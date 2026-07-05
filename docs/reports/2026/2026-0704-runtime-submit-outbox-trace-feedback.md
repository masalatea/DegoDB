# Runtime Submit Outbox Trace Feedback

Status: `FIRST_SLICE_DONE`

Date: 2026-07-04

## Summary

Generated no-code runtime submit feedback now includes the accepted sync outbox item id and operation key when the execution endpoint returns an outbox item.

The intent is not to refresh business data immediately. The value of this slice is traceability: a tryout user can see that the server accepted the generated action, queued a sync item, and which operation that queued item represents.

## Changed

- Extended generated runtime success messaging from sync status only to sync status plus outbox item id and operation key.
- Extended the sample28 real-submit browser smoke to capture and assert the returned outbox item id and operation key.
- Extended the direct endpoint smoke to assert the outbox item id and operation key exist in the JSON response.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/NoCodeRuntimeTest.php`
- `make sample28-no-code-public-runtime-browser-smoke`

Push was not performed.
