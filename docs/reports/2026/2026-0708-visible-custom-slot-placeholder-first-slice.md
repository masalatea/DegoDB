# 2026-07-08 Visible custom slot placeholder first slice

Status: `FIRST_SLICE_DONE`

## Summary

#441 adds the first visible rendering layer for custom UI slots.

The slice keeps behavior deliberately small: generated runtime HTML renders declared extension slots as visible, non-executing placeholder regions. It does not execute custom operations or mount custom React components yet.

## Accepted Capability

- Generated runtime HTML renders `extension_slots` when a screen declares them.
- Each placeholder exposes stable `data-extension-slot` attributes.
- Each placeholder displays:
  - slot label
  - slot type
  - target
  - renderer hint
- The Mtool dogfooding inspection summary now reports `visible_placeholder`.
- The Mtool Source Output review probe confirms placeholders for:
  - related settings
  - artifact status
  - operator actions

## Boundary

This is still generated UI, not custom behavior.

The placeholder is a safe visual contract that helps users and implementers see where custom content can attach. Actual link lists, status cards, action panels, React component mapping, and custom operation execution remain separate follow-up slices.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- focused PHPUnit: `OK (8 tests, 109 assertions)`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`: sample28/sample29/sample31 `ok: true`
- full `make test`: `345 tests`, `11261 assertions`, `Skipped: 1`

The PHPUnit warning about `.phpunit.result.cache` being read-only is the existing container cache warning and does not affect the test result.

## Recommended Next Step

Run broader UI/runtime verification, then decide whether this is a push checkpoint or whether to add one concrete slot renderer next.

The safest next renderer candidate is `related_settings_panel` because it can start as a generated link list without invoking side-effecting operations.
