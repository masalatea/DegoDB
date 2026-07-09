# 2026-07-08 Custom UI slot manifest first slice

Status: `FIRST_SLICE_DONE`

## Summary

#438 adds the first custom UI slot manifest layer after configured presentation metadata.

The goal is to declare where custom UI may attach without hand-editing generated artifacts. Rendering custom components is intentionally deferred.

## Accepted Capability

- `contract_metadata.extension_slots` can declare custom UI extension points.
- Screen definitions now expose normalized contract-level `extension_slots`.
- Each generated list/detail/form screen carries only slots that target its `screen_types`.
- Runtime preview JSON carries `extension_slots` forward so HTML runtime, React bridge, and future adapters can use the same contract.
- The Mtool Source Output review probe now declares:
  - `related_settings_panel`
  - `artifact_status_panel`
  - `operator_actions_panel`

## Metadata Shape

The first slice supports:

- `slot_key`
- `slot_type`
- `label`
- `placement`
- `renderer`
- `target`
- `screen_types`
- `source`

Allowed slot types are intentionally narrow:

- `related_settings_panel`
- `artifact_status_panel`
- `operator_actions_panel`

Allowed placements are `aside`, `header`, `footer`, and `inline`. Allowed renderer hints are `placeholder`, `link_list`, `status_card`, and `action_panel`.

## Out Of Scope

- No generated HTML slot rendering yet.
- No React component registry yet.
- No custom operation execution behavior.
- No admin edit UI for extension slots yet.
- No broad visual builder.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- focused PHPUnit: `OK (7 tests, 88 assertions)`
- `git diff --check`
- full `make test`: `344 tests`, `11240 assertions`, `Skipped: 1`

The PHPUnit warning about `.phpunit.result.cache` being read-only is the existing container cache warning and does not affect the test result.

## Recommended Next Step

Next implementation candidate is #439: apply the configured presentation and extension slot metadata to a concrete Mtool dogfooding inspection pass.

That pass should inspect the generated `screen-definition.json` / `runtime-preview.json` shape and decide which slot should receive the first visible placeholder or React bridge mapping.
