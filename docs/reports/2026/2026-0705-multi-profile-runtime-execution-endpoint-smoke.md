# Multi-Profile Runtime Execution Endpoint Smoke

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

The direct no-code runtime execution endpoint smoke is now profile-aware.

`mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php` accepts `--profile=sample28|sample29`, keeps sample28 as the default, and uses profile-specific project/action/operation/key/input expectations. The shared public runtime browser smoke passes the selected profile through to the endpoint smoke.

Sample29 now runs the direct endpoint smoke by default from `make sample29-no-code-public-runtime-browser-smoke`, proving that:

- `/runs/no-code/SAMPLE29/current/execute.json` accepts an authenticated scoped tryout POST;
- `/runs/no-code/SAMPLE29/alias/stable/execute.json` accepts the same operation path;
- both responses enqueue pending managed-operation sync intent work for `update_support_case`;
- browser-level submit handoff and direct endpoint enqueue proof are both covered for the second no-code domain.

## Boundary

This slice intentionally does not add sample29 outbox processing. Sample28 remains the full processing baseline with generated server DBAccess outbox processing proof. Sample29 is now complete through browser submit handoff plus direct endpoint enqueue proof.

## Verification

- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `bash -n mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh mtool/scripts/check_sample29_no_code_public_runtime_browser_smoke.sh`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `334 tests`, `10967 assertions`, `skipped 1`
