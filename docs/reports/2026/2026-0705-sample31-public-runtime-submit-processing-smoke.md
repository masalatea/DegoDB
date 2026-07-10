# Sample31 Public Runtime Submit Processing Smoke / sample31 public runtime submit processing smoke

Status: `FIRST_SLICE_DONE`

Date: 2026-07-05

Push: not performed.

## Summary

#185 extends the public no-code runtime submit/processing confidence stack to sample31.

The sample31 generated runtime already proved inventory request artifact and browser-local behavior. This slice adds a public current/alias browser smoke that verifies the real endpoint and generated server DBAccess outbox processor path for the same domain.

## Implemented

- Added `check_sample31_no_code_public_runtime_browser_smoke.sh`.
- Extended the generic direct endpoint smoke profile to `sample31`.
- Extended the generic outbox processing smoke profile to `sample31`.
- Added `sample31-no-code-public-runtime-browser-smoke`.
- Documented the public smoke in the sample31 README.

## Boundary

- This proves direct endpoint enqueue and one generated server DBAccess processing pass against an isolated SQLite `inventory_request` row.
- It does not add live polling, runtime retry mutation, conflict resolution, transport, or a fourth domain sample.

## Verification

- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `php -l mtool/scripts/check_sample28_no_code_runtime_outbox_process_smoke.php`
- `bash -n mtool/scripts/check_sample31_no_code_public_runtime_browser_smoke.sh`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`Tests: 335, Assertions: 11044, Skipped: 1`)
