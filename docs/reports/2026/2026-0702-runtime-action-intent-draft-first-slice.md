# 2026-0702 Runtime Action Intent Draft First Slice

Status: `FIRST_SLICE_DONE`

## Summary

Added a small runtime interaction slice to the generated no-code preview.

The goal is to make the preview feel more tangible without turning the static public preview into a mutation surface. Users can edit fields and immediately see the local no-code action-intent draft JSON that would be handed to the managed operation layer.

## Changes

- Added `Action Intent Draft` panels to generated runtime preview screens with actions.
- Added browser-side draft generation from the first screen action and current editable controls.
- The draft includes action key, operation key/type, availability, executable flag, and key/input/filter payload buckets.
- Disabled actions stay disabled. The draft panel does not execute server updates and does not bypass policy checks.
- Updated sample28 browser smoke to verify draft panels exist and update after editing a form input.
- Updated `docs/no-code-tryout.md` and sample28 README with the browser-side preview boundary.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- Focused `NoCodeRuntimeTest`
  - `8 tests, 113 assertions`
- `make sample28-no-code-runtime-ui-smoke`
  - verified `intentDraftCount: 3`
  - verified disabled draft state
  - verified the form draft JSON changes after editing the required `body` input
- Full `make test`
  - `327 tests, 10810 assertions, skipped 1`
