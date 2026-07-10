# Runtime Execution Route Principal Policy Overlay Wiring

Date: 2026-07-04
Status: FIRST_SLICE_DONE

## Summary

The authenticated no-code runtime execution routes now evaluate action policy for the current principal before dispatch. The route still loads the approved stored runtime artifact, then overlays action availability and policy from a fresh principal-aware screen definition.

This connects the #123 policy overlay contract to the real execution path while preserving the approved artifact boundary.

## Implementation Notes

- Added `auth.php` to `no_code_public_runtime_page.php` dependencies.
- Extended `app_no_code_public_runtime_execution_response_for_candidate()` with an optional principal.
- When a principal is present, the function rebuilds the no-code screen definition from current project metadata and overlays action policy onto the stored runtime definition before request dispatch.
- If principal-aware policy definition generation fails, execution fails closed through the existing endpoint response contract.
- Artifact/current/alias execution handlers now pass `app_auth_principal()`.
- Static route contract coverage now checks for the principal and policy overlay wiring.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`: passed.
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample28-no-code-data-app-mvp/compose.yaml --run-script=sample/tutorials/sample28-no-code-data-app-mvp/run.sh --phpunit-target=/var/www/tests/Integration/OpenApiSourceOutputContractTest.php`: passed.
- `make sample28-no-code-public-runtime-browser-smoke`: passed.

The sample28 direct endpoint smoke still returns `422` disabled-action JSON for current and alias because the current stub admin principal does not satisfy the sample operation's `editor` role and `no_code_ticket:write` scope policy.

Push was not performed for this slice.
