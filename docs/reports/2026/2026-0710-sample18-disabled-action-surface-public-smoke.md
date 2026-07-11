# Sample18 Disabled Action Surface Public Smoke

Plan item: #563 sample18 disabled action surface public smoke

Status: DONE

## Summary

Added a public runtime smoke that proves sample18 exposes the disabled managed action surface for `complete_task_card`, `create_task_card`, and `update_task_card` without enabling submit.

## Changes

- Added `sample18-no-code-public-runtime-disabled-action-smoke`.
- Added a sample18 wrapper script for the disabled action public runtime smoke.
- Extended the DOM-only browser smoke to assert disabled managed action buttons, operation types, hints, policy-disabled state, preview metadata, and a still-disabled runtime execute button.
- Kept the existing status filter DOM check in the same public runtime flow.

## Boundary

This slice does not enable generated submit, dispatch, outbox enqueue, or curated route replacement. It only verifies the public runtime can display the disabled generated managed action surface after #562.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `bash -n mtool/scripts/check_sample18_no_code_public_runtime_disabled_action_smoke.sh mtool/scripts/check_sample18_no_code_public_runtime_filter_dom_smoke.sh mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh`
- `make sample18-no-code-public-runtime-disabled-action-smoke`
- `make test`
- `git diff --check`

## Next

#564 should define the dispatch guard and failure contract before any sample18 generated submit path is enabled.
