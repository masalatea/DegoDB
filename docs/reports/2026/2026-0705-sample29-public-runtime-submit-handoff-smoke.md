# Sample29 Public Runtime Submit Handoff Smoke

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

Sample29 now has a public runtime browser smoke for the current / alias submit handoff.

The smoke reuses the public runtime artifact/current/alias flow and verifies the generated browser UI can:

- load immutable artifact preview without execution binding;
- load current / alias previews with authenticated execution binding;
- submit an enabled `update_support_case` action through the real endpoint;
- receive pending sync outbox feedback;
- expose copy/open outbox detail affordances;
- expose the manual `Refresh preview` handoff guidance.

## Accepted Capability

The accepted first slice proves that the submit/open/copy/refresh handoff is not sample28-only at the browser level.

It does not add sample29 direct endpoint payload smoke, outbox processing smoke, live polling, synchronous processing, or retry mutation.

Sample29 requires an `editor` role and `support_case:write` scope for `update_support_case`, so this slice also records the local stub-auth scope path needed to exercise scoped managed operations in Docker tryout stacks.

## Verification

Local verification passed:

- `php -l mtool/app/auth.php`
- `php -l mtool/app/config.php`
- `bash -n mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh mtool/scripts/check_sample29_no_code_public_runtime_browser_smoke.sh`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make test` (`334 tests`, `10967 assertions`, `skipped 1`)

## Remaining Candidates

- Add a generic multi-profile endpoint smoke.
- Add sample29 outbox processing smoke if the product needs cross-sample processing proof.
- Add live result refresh / polling after submit.
