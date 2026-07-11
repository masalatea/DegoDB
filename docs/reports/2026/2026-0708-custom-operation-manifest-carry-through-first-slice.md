# Custom Operation Manifest Carry-Through First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#449 adds the first code-backed custom operation manifest carry-through.

This is metadata-only. It does not add custom operation execution, build/publish/approval routes, or generated runtime mutation behavior.

## Implemented

- `contract_metadata.custom_operations` is normalized into no-code screen definitions.
- The normalized manifest carries:
  - `operation_key`
  - `label`
  - `category`
  - `target`
  - `side_effect_class`
  - `availability`
  - `policy_key`
  - `csrf_required`
  - `audit_event`
  - `adapter_handoff`
  - `intent`
- Mtool Source Output review dogfooding metadata declares:
  - `review_source_output_artifact`
  - `request_source_output_publish`
- Runtime preview JSON carries `custom_operations` on each rendered screen.
- Operator action panel items can bind to a manifest `operation_key`.
- Generated runtime HTML exposes stable operation binding attributes:
  - `data-extension-slot-operation`
  - `data-extension-slot-operation-key`

## Preserved Boundary

- Operator action buttons remain disabled.
- No execution route is added.
- No build, publish, review-request, approval, or mutation behavior is added.
- No custom React/component execution is added.
- The source of truth remains canonical metadata, not generated artifact hand edits.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- Focused PHPUnit: `OK (8 tests, 132 assertions)`
- `make sample-no-code-public-runtime-browser-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11284, Skipped: 1.`
