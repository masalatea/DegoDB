# Runtime Outbox Detail Path Copy Affordance

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

Generated runtime previews now include a disabled-by-default `Copy outbox path` control near `Submit to server`.

After successful server submit, when the endpoint response includes an operator sync outbox detail path, the control becomes enabled and copies that path. The path is still shown in status / feedback text and remains available as `data-runtime-outbox-detail-path`.

## Accepted Capability

The accepted first slice is a copy affordance for the accepted operator sync outbox detail path.

It is not full anchor rendering, live polling, synchronous processing, or runtime retry mutation.

## Verification

Local verification:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 221 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10958 assertions, skipped 1`

## Remaining Candidates

- Render the path as a first-class link.
- Add a compact "open operator detail" action in authenticated contexts.
- Add live result polling or explicit refresh automation.
- Add another no-code sample that uses the same copyable handoff path.
