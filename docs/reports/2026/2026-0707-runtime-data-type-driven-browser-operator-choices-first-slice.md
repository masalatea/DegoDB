# Runtime Data Type-Driven Browser Operator Choices First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#361 implements the first browser-side operator-choice slice after date/time semantics were proven at the endpoint.

Generated current/alias runtime-data filter controls now use field type metadata to decide whether ordered operators should be offered.

## Implemented Scope

- Runtime filter field options now carry their generated field type.
- Runtime filter operator selects still include:
  - `contains`;
  - `eq`.
- Ordered operators are exposed only for selected fields with type:
  - `integer`;
  - `number`;
  - `date`;
  - `datetime`;
  - `time`.
- Ordered operators are hidden and disabled for non-ordered types such as `string` and `text`.
- If a non-ordered field becomes selected while an ordered operator is active, the operator resets to `contains`.
- Payload replay, URL replay, browser history replay, and runtime-data refresh control syncing all resync operator choices after field selection changes.
- sample31 browser smoke now verifies that:
  - a string field does not expose `gte`;
  - the `needed_by` date field exposes and can select `gte`.

## Boundaries Preserved

- No endpoint contract change.
- No `no-code-runtime-data-v0` version change.
- No additional filter operators beyond existing endpoint support.
- No artifact-key preview behavior change.
- No mutation, retry, outbox processing, or status polling change.
- Endpoint fail-closed validation remains the final guard for invalid operator / field combinations.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11152 assertions`, `1 skipped`)

## Remaining Candidates

- More explicit operator labels or grouped operator UI if the controls become too dense.
- Timezone-offset policy for `datetime`.
- Null/empty date/time sort/filter semantics.
- Local stack review and commit cleanup before push.

## Push

Push was not performed.
