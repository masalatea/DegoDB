# Runtime-data type-driven browser operator choices closure

Date: 2026-07-07

## Summary

#362 closes the type-driven browser operator-choice lane after #361.

The accepted capability is that generated current/alias runtime-data browser controls keep `contains` and `eq` available for every field, while ordered operators (`gt`, `gte`, `lt`, `lte`) are exposed only when the selected field type is explicitly ordered: `integer`, `number`, `date`, `datetime`, or `time`.

This keeps the browser controls aligned with the read-only endpoint semantics introduced in the numeric and date/time runtime-data lanes without changing endpoint contracts, artifact-key preview behavior, mutation, retry, outbox processing, or status polling.

## Accepted Capability

- String/text fields do not offer ordered operators in generated runtime filter controls.
- Numeric/date/time fields offer ordered operators in generated runtime filter controls.
- Previously mirrored URL/query state remains compatible with endpoint validation.
- Endpoint validation remains the authoritative fail-closed guard.
- Artifact-key preview remains static and unchanged.
- Current/alias routes remain the only paths involved in dynamic runtime-data reads.

## Verification Baseline

#362 is docs-only. The accepted implementation baseline is #361:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make test`

The full test suite passed at #361 with `337 tests`, `11152 assertions`, and `1 skipped`.

## Remaining Candidates

- Local commit stack review before push.
- Timezone offset policy for `datetime` values.
- Null/empty date/time ordering policy.

These are separate candidates and should not be silently bundled into the closed operator-choice lane.

## Push Status

No push was performed for #362.
