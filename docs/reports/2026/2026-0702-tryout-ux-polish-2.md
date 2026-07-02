# 2026-0702 Tryout UX Polish 2

Status: `FIRST_SLICE_DONE`

## Summary

Added a small first-time-user polish layer after the no-code tryout-ready milestone and #69/#70 verification.

The goal was not to add new runtime capability. It was to reduce confusion when a user lands on Source Outputs / NO-CODE-RUNTIME and sees both Web public preview readiness and App-local package readiness.

## Changes

- Added `Tryout Next Steps` to the `NO-CODE-RUNTIME` detail page.
  - Points sample users toward `Run Sample28 Tryout Approval`.
  - Links to the current public runtime preview.
  - Links back to Source Outputs for delivery status review.
- Clarified that App-local package readiness is a separate scenario from the Web no-code runtime preview.
- Added Delivery Overview wording that tells users to continue the Web preview tryout even when App-local package is blocked or not configured.
- Updated `docs/no-code-tryout.md` so the browser path mentions `Tryout Next Steps`.
- Added focused static contract assertions for the new wording.

## Verification

- `php -l mtool/app/project_source_output_detail_page.php`
- `php -l mtool/app/project_source_outputs_page.php`
- `git diff --check origin/develop..HEAD`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
  - `22 tests, 1821 assertions`

This slice is ready for normal push. No force push is required.
