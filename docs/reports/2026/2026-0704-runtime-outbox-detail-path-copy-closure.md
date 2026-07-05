# Runtime Outbox Detail Path Copy Closure

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

This closes the runtime outbox detail path copy affordance lane.

The accepted behavior is:

1. Runtime submit remains outbox-based.
2. The accepted operator sync outbox detail path is shown in submit success feedback.
3. The path is also exposed as structured DOM state.
4. `Copy outbox path` is disabled until a successful submit returns a path.
5. After successful submit, the user can copy the operator path without parsing the full status text.
6. The copy affordance resets when local draft/action state changes.

## Accepted Capability

The accepted first slice is a copy affordance for the accepted operator sync outbox detail path.

It is not full link rendering, automatic navigation, live polling, synchronous processing, or runtime retry mutation.

## Verification Baseline

The implementation slice was verified with:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 221 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10958 assertions, skipped 1`

This closure commit is docs-only. Local verification for this closure is `git diff --check`.

## Next Candidates

Choose one via a fresh replan:

- Render the outbox detail path as a visible link or authenticated open action.
- Add live result refresh / polling after submit.
- Add another sample that proves the same submit/outbox/operator-handoff flow.
- Review and group the local commit stack before the next push.

