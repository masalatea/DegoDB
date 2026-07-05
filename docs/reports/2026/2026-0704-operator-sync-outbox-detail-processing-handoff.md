# Operator Sync Outbox Detail Processing Handoff

Status: `FIRST_SLICE_DONE`

Date: 2026-07-04

## Summary

The operator sync outbox detail page now has a read-only Processing Handoff section that explains what the current outbox status means and what should happen next.

This closes the handoff opened by generated runtime submit feedback: a tryout user can submit, see the sync outbox detail path, and land on a page that explains whether the item is queued, processing, complete, or needs retry review.

## Changed

- Added `app_project_sync_outbox_processing_handoff()`.
- Rendered a Processing Handoff section on the sync outbox detail page.
- Added coverage for pending and failed handoff states.
- Added source contract assertions for the detail page handoff wording.

## Verification

- `php -l mtool/app/project_sync_outbox_detail_page.php`
- `git diff --check`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/NoCodeOperatorSyncInspectionTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/OpenApiSourceOutputContractTest.php`

Push was not performed.
