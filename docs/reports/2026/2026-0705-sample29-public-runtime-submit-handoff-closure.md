# Sample29 Public Runtime Submit Handoff Closure

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

The second-sample public runtime submit handoff lane is closed for the current minimum.

Sample29 now proves that the public no-code runtime submit handoff is not tied to sample28's ticket domain. The current / alias browser smoke reaches the real runtime execution endpoint for `update_support_case`, receives a pending sync outbox response, and exposes the same copy / open / manual-refresh handoff expected by the generated runtime UI.

## Accepted Capability

- Sample28 remains the full baseline with browser real-submit, direct endpoint smoke, and outbox processing smoke.
- Sample29 adds second-domain browser proof for artifact/current/alias delivery and current/alias real-submit handoff.
- Local Docker tryout stacks can configure stub-auth scopes through `APP_AUTH_STUB_SCOPES`, which is required for scoped managed operations such as `support_case:write`.
- The generated runtime submit success path consistently exposes pending outbox status, operation key, detail path copy, detail link, and process-then-refresh guidance across the two no-code samples.

## Out Of Scope

- Live polling after submit.
- Synchronous endpoint processing.
- Runtime retry mutation.
- Generic multi-profile endpoint smoke.
- Sample29 outbox processor smoke.
- Push / history cleanup.

## Verification Baseline

Latest verification from the implementation slice:

- `php -l mtool/app/auth.php`
- `php -l mtool/app/config.php`
- `bash -n mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh mtool/scripts/check_sample29_no_code_public_runtime_browser_smoke.sh`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make test` (`334 tests`, `10967 assertions`, `skipped 1`)

Docs-only closure check:

- `git diff --check`

## Next Candidates

Recommended next decision is a local commit stack review before choosing live polling, synchronous demo processing, retry mutation, generic smoke extraction, or push cleanup.
