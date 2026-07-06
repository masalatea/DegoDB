# Runtime Data Refresh UI Consumption First Slice

Status: DONE
Date: 2026-07-05

## Summary

This slice connects the proven current/alias `runtime-data.json` endpoint to the public runtime preview UI.

The generated artifact remains immutable. The public current/alias page injects a request-local `runtime_data_url` binding, and the existing Refresh affordance uses that binding to fetch fresh read-only runtime data.

## Implemented

- Added `runtime_data_url` to the public current/alias runtime binding.
- Kept artifact-key preview without the data binding.
- Added client-side runtime-data fetch for Refresh when a data binding is present.
- Validated `contract_version: no-code-runtime-data-v0` before applying data.
- Merged lightweight live data screens into the existing generated preview metadata so fields, actions, titles, and summaries still come from the generated artifact.
- Re-rendered list/detail/form bodies from live `runtime-data.json` data.
- Kept old page reload behavior for previews without a data binding.
- Extended browser smoke coverage so real current/alias public runtime pages click Refresh and verify live data fetch plus row rendering.

## Verified Behavior

- SAMPLE28 current/alias Refresh fetches `runtime-data.json` and renders first row key `1001`.
- SAMPLE29 current/alias Refresh fetches `runtime-data.json` and renders first row key `2001`.
- SAMPLE31 current/alias Refresh fetches `runtime-data.json` and renders first row key `3101`.
- Stubbed terminal status branches still verify done / failed outbox status behavior without also forcing data fetch.
- Submit/enqueue/outbox processing smokes continue to pass.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/no_code_public_runtime_page.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`: 337 tests, 11087 assertions, skipped 1

## Remaining Candidates

- Post-submit automatic fresh-data refresh after the outbox reaches terminal `done`.
- Clearer UI wording around static artifact-key preview vs current/alias live data refresh.
- A larger read-model shape for pagination, filters, and detail selection.

Push was not performed.
