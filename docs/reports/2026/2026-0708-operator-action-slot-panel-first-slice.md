# Operator Action Slot Panel First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#444 adds the first non-executing `action_panel` renderer for declared custom slots.

This completes the visible first pass over the three Mtool dogfooding slots: related settings links, artifact status cards, and operator action affordances. The operator actions are intentionally disabled in the generated preview. They document where build, review, publish, approval, or other custom operations would attach later without adding those mutation paths now.

## Implemented

- `contract_metadata.extension_slots[].action_items` is normalized into screen-definition slot metadata.
- The Mtool Source Output review dogfooding probe declares:
  - Review Artifact
  - Request Publish
- Generated runtime HTML renders `operator_actions_panel` + `action_panel` as disabled buttons with intent text and stable `data-extension-slot-action` attributes.
- Related settings link-list rendering from #442 and artifact status-card rendering from #443 are preserved.

## Preserved Boundary

- No build, publish, approval, or mutation route is added.
- No custom operation execution is added.
- No custom React/component execution is added.
- Existing generated runtime behavior for samples without extension slots is unchanged.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- Focused PHPUnit: `OK (8 tests, 121 assertions)`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11273, Skipped: 1.`
