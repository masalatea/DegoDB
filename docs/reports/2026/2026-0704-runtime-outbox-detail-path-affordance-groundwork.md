# Runtime Outbox Detail Path Affordance Groundwork

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

Runtime submit success now exposes the accepted operator sync outbox detail path as structured DOM state:

- `data-runtime-outbox-detail-path` on the runtime execute status
- `data-runtime-outbox-detail-path` on the action feedback

Visible wording remains unchanged. The path is still shown in the success message, and the new attribute gives later click/copy/link polish a stable source without parsing display text.

## Accepted Capability

The accepted first slice is structured outbox detail path exposure after successful submit.

It is not a full rendered anchor, copy button, live polling, or synchronous processing change.

## Verification

Local verification:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 216 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10953 assertions, skipped 1`

## Remaining Candidates

- Render a visible link from the structured outbox detail path.
- Add a copy affordance for the outbox detail path.
- Add live polling or explicit refresh automation after outbox processing.
- Add the same submit/outbox/result-check path to another sample.
