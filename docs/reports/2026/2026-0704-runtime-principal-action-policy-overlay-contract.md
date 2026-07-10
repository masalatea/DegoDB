# Runtime Principal Action Policy Overlay Contract

Date: 2026-07-04
Status: FIRST_SLICE_DONE

## Summary

Added the first principal-aware policy bridge needed before real runtime execution can be wired to current / alias endpoints. The new helper overlays action availability and policy from a separately evaluated principal-aware definition onto a stored runtime definition.

This keeps the approved artifact stable while creating a narrow path for authenticated execution to use fresh principal policy when a later route slice opts in.

## Implementation Notes

- Added `app_no_code_runtime_definition_with_action_policy_overlay()`.
- The helper copies action `availability` and `policy` by `action_key` into contract actions.
- It also updates matching screen action `availability` so rendered action controls reflect the same policy.
- The helper leaves fields, screens, data, labels, operation metadata, and artifact structure unchanged.
- Added focused `NoCodeRuntimeTest` coverage that starts from a stored disabled definition, overlays an editor-enabled policy definition, then verifies render and dispatch both treat the action as enabled.

## Verification

- `php -l mtool/app/no_code_runtime.php`: passed.
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample28-no-code-data-app-mvp/compose.yaml --run-script=sample/tutorials/sample28-no-code-data-app-mvp/run.sh --phpunit-target=/var/www/tests/Integration/NoCodeRuntimeTest.php`: passed.

Push was not performed for this slice.
