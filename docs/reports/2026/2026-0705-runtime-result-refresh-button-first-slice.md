# Runtime Result Refresh Button First Slice

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

Generated runtime previews now include a disabled-by-default `Refresh preview` button near `Submit to server`.

After successful server submit, the button becomes ready. Clicking it stores the current screen form values in `sessionStorage` before reloading the preview, then restores those values after reload.

## Accepted Capability

The accepted first slice is a manual result refresh affordance for outbox-based runtime submit flows.

It is not live polling, automatic background refresh, synchronous endpoint processing, or retry mutation.

## Verification

Local verification:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 226 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10963 assertions, skipped 1`

## Remaining Candidates

- Add live result refresh / polling after submit.
- Add richer success wording that distinguishes refresh from outbox processing.
- Prove the same submit/open/copy/refresh handoff in another no-code sample.
