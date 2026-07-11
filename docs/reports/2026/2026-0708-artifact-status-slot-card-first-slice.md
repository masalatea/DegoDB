# Artifact Status Slot Card First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#443 adds the first read-only `status_card` renderer for declared custom slots.

This keeps the no-code UI layered on top of metadata rather than bespoke component code. The generated screen can now show the artifact boundary that supports the current no-code surface, while richer live artifact/build/revision inspection remains a later operator workflow.

## Implemented

- `contract_metadata.extension_slots[].status_items` is normalized into screen-definition slot metadata.
- The Mtool Source Output review dogfooding probe declares status items for:
  - Artifact Strategy
  - Target Binding
  - Spec Visibility
- Generated runtime HTML renders `artifact_status_panel` + `status_card` as a read-only `<dl>` card with stable `data-extension-slot-status-item` attributes.
- Related settings link-list rendering from #442 is preserved.
- Operator action slots remain non-executing placeholders.

## Preserved Boundary

- No live artifact repository lookup is added.
- No build, publish, approval, retry, or mutation action is added.
- No custom React/component execution is added.
- Existing generated runtime behavior for samples without extension slots is unchanged.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/NoCodeScreenDefinitionTest.php`
  - `OK (8 tests, 117 assertions)`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`
  - sample28 / sample29 / sample31 returned `ok: true`
- `make test`
  - `345 tests`, `11269 assertions`, `Skipped: 1`
