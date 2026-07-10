# Runtime Data Refresh Key Preservation

Status: DONE
Date: 2026-07-05

## Summary

This slice tightens the `runtime-data.json` Refresh path added in #212.

When a current/alias public runtime preview refreshes from live data, the form body is re-rendered from the data endpoint while actions and field metadata still come from the generated artifact. Some action key fields are not visible editable fields, so the refreshed form must still carry those keys into the local action intent draft.

## Implemented

- Added hidden action-key controls when a refreshed form has key fields in action metadata that are not visible rendered fields.
- Kept visible form controls unchanged.
- Kept immutable artifact-key preview behavior unchanged.
- Extended the public runtime browser smoke so real current/alias Refresh verifies the refreshed form keeps the expected key and no longer reports `key.missing:<field>`.
- Added generator-level Integration coverage for the hidden-key helper and marker attribute.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`: 337 tests, 11089 assertions, skipped 1

## Remaining Candidates

- Post-submit automatic fresh-data refresh after the outbox reaches terminal `done`.
- Clearer UI wording around static artifact-key preview vs current/alias live data refresh.
- A larger read-model shape for pagination, filters, and detail selection.

Push was not performed.
