# Custom Operation Unavailable Reason First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#453 adds explicit unavailable-reason metadata and display for custom operation manifests.

This keeps the lane metadata-first and non-executing. The goal is to show why a visible custom operation affordance is disabled before any route, policy, CSRF, audit, approval, build, publish, or mutation wiring exists.

## Implemented

- `contract_metadata.custom_operations[]` normalizes `unavailable_reason`.
- `contract_metadata.extension_slots[].action_items[]` normalizes `unavailable_reason`.
- Mtool Source Output review custom operations record why Review Artifact and Request Publish are deferred.
- Generated operator action panels render unavailable reasons below disabled buttons.
- The disabled reason markup exposes `data-extension-slot-unavailable-reason`.
- Mtool dogfooding inspection reports custom operation unavailable reasons.
- Mtool dogfooding HTML boundary reports whether unavailable-reason markup is present.

## Preserved Boundary

- No custom operation execution route is added.
- No build, publish, review-request, approval, or mutation action is wired.
- Generated operator action buttons remain disabled.
- No custom React/component execution is added.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l mtool/app/no_code_runtime.php`
- Focused PHPUnit: `OK (8 tests, 145 assertions)`
- `make sample-no-code-public-runtime-browser-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11297, Skipped: 1.`
- `git diff --check`
