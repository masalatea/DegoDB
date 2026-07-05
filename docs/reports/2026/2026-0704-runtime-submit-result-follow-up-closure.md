# Runtime Submit Result Follow-Up Closure

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

This closes the manual result-check guidance lane after server-backed runtime submit accepts pending/running sync outbox work.

The accepted behavior is:

1. Runtime submit remains outbox-based.
2. Success feedback shows accepted status, item id, operation key, and operator detail path.
3. Success feedback tells the user to process the sync outbox item.
4. Success feedback tells the user to refresh the runtime preview after processing to see updated data.

This keeps the product honest while still giving a tryout user a clear next step.

## Accepted Capability

The accepted first slice is manual result follow-up guidance.

It is not live polling, direct business-row mutation, synchronous endpoint processing, or retry mutation from the runtime.

## Verification Baseline

The implementation slice was verified with:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 215 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10952 assertions, skipped 1`

This closure commit is docs-only. Local verification for this closure is `git diff --check`.

## Next Candidates

Choose one via a fresh replan:

- Link affordance polish: turn the outbox detail path into a clearer click/copy target while keeping generated runtime simple.
- Live result refresh: add explicit refresh/polling behavior after submit.
- Another scenario sample: prove the same submit/outbox/result-check path in a different no-code domain.
- Commit stack review / push cleanup: the local branch is intentionally ahead of `origin/develop`.

