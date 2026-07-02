# Runtime preview keyboard/action affordance polish first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-01

## Summary

Added focused generated runtime preview affordances around action controls without changing operation execution behavior.

## Changes

- Added action-control wrappers around generated runtime preview action buttons.
- Added deterministic `data-action-affordance="keyboard-intent-preview"` and `data-keyboard-activation="enter-space"` markers.
- Added `aria-describedby` action hints with screen-scoped hint ids.
- Added disabled-action reason markers for fail-closed preview actions.
- Added small action hint text for enabled and disabled action states.
- Covered the markers in `NoCodeRuntimeTest`, sample28 checker, and sample28 runtime UI smoke.

## Boundary

In scope:

- generated `runtime-preview.html` action affordance markup;
- keyboard/focus-readable action hints;
- disabled action reason markers;
- focused checker and browser smoke coverage.

Out of scope:

- operation execution semantics;
- new metadata tables;
- broad CSS redesign;
- visual builder;
- React bridge replacement;
- JSON Forms/rjsf product runtime adoption.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample28-no-code-runtime-ui-smoke`
- `make test` (`309 tests, 10314 assertions, skipped 1`)
