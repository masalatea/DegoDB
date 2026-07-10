# Runtime Result Refresh Guidance Wording First Slice

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

Generated runtime previews now show a short `Refresh preview` status message.

Before submit, the status says refresh is available after server submit. After successful submit, it tells the user to process the sync outbox item, then use `Refresh preview` to reload the current screen.

The server submit success guidance now uses the same wording, making the outbox processing step and preview reload step distinct.

## Accepted Capability

The accepted first slice is wording and visible status for the manual refresh handoff.

It is not live polling, automatic background refresh, synchronous endpoint processing, or retry mutation.

## Verification

Local verification:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 228 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10965 assertions, skipped 1`

## Remaining Candidates

- Add live result refresh / polling after submit.
- Prove the submit/open/copy/refresh handoff in another no-code sample.
- Add a closure report for the manual refresh lane before a larger lane or commit cleanup.
