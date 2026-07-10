# Runtime Data Numeric Sort First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#356 implements the first numeric sort semantics slice for current/alias `runtime-data.json`.

The endpoint keeps the existing `sort[field]=asc|desc` query contract while using explicit read-model field metadata to decide whether a field should sort numerically or by display string.

## Implemented Behavior

- Kept the existing `sort[field]=asc|desc` URL shape and `query.sort` response metadata.
- Added numeric comparison for normalized `integer` and `number` read-model fields.
- Kept non-numeric fields on the existing display-string `strnatcasecmp()` behavior.
- Preserved multi-sort query order and original-row-order tie-breaking.
- Prevalidates numeric sort row values with a strict numeric parser and fails closed on non-numeric row values.
- Left generated browser sort controls, endpoint version, artifact-key behavior, mutation, retry, outbox processing, and status polling unchanged.

## Smoke Coverage

- sample28 verifies numeric sort on `priority`:
  - `asc` first row key: `1001`;
  - `desc` first row key: `1003`.
- sample29 verifies numeric sort on `id`:
  - `asc` first row key: `2001`;
  - `desc` first row key: `2002`.
- sample31 verifies numeric sort on `quantity_needed`:
  - `asc` first row key: `3101`;
  - `desc` first row key: `3102`.
- Existing string sort, multi-sort, invalid direction, and too-many-sort coverage remains in place.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`

`make test` result: 337 tests, 11152 assertions, 1 skipped.

## Next Candidate

The next natural step is closure/review before moving to date/time semantics or type-driven browser operator choices, because numeric filter and numeric sort semantics now both exist on explicit numeric read-model fields.

## Push

Push was not performed.
