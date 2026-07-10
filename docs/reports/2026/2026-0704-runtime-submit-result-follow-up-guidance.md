# Runtime Submit Result Follow-Up Guidance

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

Generated runtime submit success feedback now tells tryout users what to do after server-backed submit accepts pending or running sync outbox work:

- process the sync outbox item
- refresh the runtime preview to see updated data

This is intentionally lighter than live polling or synchronous endpoint processing.

## Accepted Capability

When the endpoint response includes a pending/running outbox item, the generated runtime success message keeps the existing trace information and appends:

`Next result check: process the sync outbox item, then refresh this runtime preview.`

The guidance appears in both the runtime execute status and action feedback surface.

## Verification

Local verification:

- `php -l mtool/app/no_code_runtime.php`
- focused `NoCodeRuntimeTest`: `13 tests, 215 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`: `333 tests, 10952 assertions, skipped 1`

## Remaining Candidates

- Replace manual refresh guidance with live polling once the desired UX and load boundary are explicit.
- Add a detail-page link as an actual anchor rather than plain text.
- Add a result refresh affordance that preserves current form state.
- Extend another sample to show the same follow-up in a different domain.
