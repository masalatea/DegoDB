# Runtime Execution Endpoint Disabled-Policy Smoke

Date: 2026-07-04
Status: FIRST_SLICE_DONE

## Summary

The sample28 public runtime smoke now includes an authenticated HTTP check for the current and alias execution endpoints. The check logs in through stub admin auth, extracts the execution binding from the rendered preview, and POSTs a valid no-code runtime execution request.

Because the published runtime artifact is generated without a principal-specific policy context, its action remains disabled. The endpoint must therefore accept the valid request binding but fail closed before dispatching a mutation.

## Implementation Notes

- Added `check_no_code_runtime_execution_endpoint_smoke.php`.
- The script keeps cookies, performs stub login via `/login`, confirms `/dashboard`, reads `no-code-runtime-execution-binding`, and posts to the bound `execute.json`.
- The smoke asserts both current and alias endpoints return `422` JSON with `ok: false`, `executed: false`, `request.ok: true`, and `error: action is not enabled: update_no_code_ticket`.
- Wired the script into `check_sample28_no_code_public_runtime_browser_smoke.sh` after the browser preview checks.

## Verification

- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`: passed.
- `php mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php --help`: passed.
- `make sample28-no-code-public-runtime-browser-smoke`: passed.

The Docker smoke confirmed current and alias endpoint requests both passed binding validation and failed closed with `422` disabled-action JSON.

Push was not performed for this slice.
