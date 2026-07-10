# Runtime Outbox Detail Open Link Affordance

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

Generated runtime previews now include a hidden-by-default `Open outbox detail` link near `Submit to server` and `Copy outbox path`.

After successful server submit, when the endpoint response includes an operator sync outbox detail path, the link becomes visible and points to that path.

## Accepted Capability

The accepted first slice is a direct open affordance for the accepted operator sync outbox detail path after submit.

It is not live polling, automatic navigation, synchronous endpoint processing, or runtime retry mutation.

## Verification

Local verification:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 223 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10960 assertions, skipped 1`

## Remaining Candidates

- Add live result refresh / polling after submit.
- Add a compact result refresh button that preserves form state.
- Prove the same open/copy handoff in another no-code sample.
