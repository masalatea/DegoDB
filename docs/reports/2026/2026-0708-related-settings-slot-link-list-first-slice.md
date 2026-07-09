# Related Settings Slot Link List First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#442 adds the first concrete renderer on top of the visible custom slot placeholder layer.

The no-code runtime still treats custom UI as a declared extension boundary, but `related_settings_panel` slots using the `link_list` renderer can now render safe navigation links. This keeps the no-code screen tied to the data-flow source of the screen: a generated review surface can show which Mtool settings own the contract and source-output metadata behind it.

## Implemented

- `contract_metadata.extension_slots[].links` is normalized into screen-definition slot metadata.
- The Mtool Source Output review dogfooding probe declares related links to:
  - `/projects/MTOOL/shared-contracts`
  - `/projects/MTOOL/source-outputs`
- Generated runtime HTML renders `related_settings_panel` + `link_list` as stable anchor links with `data-extension-slot-link`.
- Other custom slot renderers remain non-executing placeholders.
- The Mtool dogfooding inspection boundary now detects related-settings link-list rendering.

## Preserved Boundary

- No custom React/component execution is added.
- No write operation, build, publish, approval, or mutation behavior is added.
- Artifact status and operator action slots remain placeholders.
- Existing generated runtime behavior for samples without extension slots is unchanged.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/NoCodeScreenDefinitionTest.php`
  - `OK (8 tests, 113 assertions)`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`
  - sample28 / sample29 / sample31 returned `ok: true`
- `make test`
  - `345 tests`, `11265 assertions`, `Skipped: 1`
