# Runtime Manual Result Refresh Closure

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

This closes the generated runtime manual result refresh lane.

The accepted first-slice flow remains outbox-based:

1. A generated current / alias runtime preview can submit an enabled action to the authenticated execution endpoint.
2. The endpoint accepts the request and creates a pending managed-operation sync outbox item.
3. The runtime shows pending sync status, item id, operation key, and operator outbox detail path.
4. The runtime provides copy/open affordances for the operator outbox detail path.
5. The runtime enables `Refresh preview` after successful submit and preserves current screen form values through reload.
6. The runtime guidance explicitly tells the user to process the sync outbox item, then use `Refresh preview` to reload the current screen.

## Accepted Capability

The accepted capability is a manual, explicit result-check handoff for no-code tryout users and operators.

The runtime does not poll, auto-refresh, synchronously process the outbox, or retry failed outbox items from the generated preview.

## Latest Verification Baseline

Latest implementation verification before closure:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 228 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10965 assertions, skipped 1`

This closure is docs-only. Local verification for this closure is `git diff --check`.

## Remaining Candidates

- Live result refresh / polling after submit.
- Another no-code sample proving the same submit/open/copy/refresh handoff.
- Synchronous local/demo processing behind an explicit demo-only boundary.
- Runtime retry mutation, only after failure-state UX and policy boundaries are explicit.
- Commit stack review / push cleanup, only with explicit user direction.

## Next Recommendation

Before adding a larger implementation lane, do a local commit stack review so the accumulated runtime submit and manual refresh work is easy to summarize or group.
