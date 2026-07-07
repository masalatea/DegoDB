# Runtime Data Multi-Column Sort Endpoint First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#316 chooses the runtime-data multi-column sort endpoint first implementation slice. #317 completes the endpoint slice while leaving generated browser sort controls on the existing one visible sort row.

## Implemented

- Extended current/alias read-only `runtime-data.json` `sort[field]=asc|desc` parsing from one field to a bounded ordered list.
- Capped the first slice at 3 sort fields.
- Preserved the existing one-field sort query as valid.
- Applied sort fields in request order, comparing display values with the existing natural case-insensitive comparison.
- Preserved stable row ordering by falling back to the original row index after all sort fields compare equal.
- Echoed the full sort map in `query.sort`.
- Added direct endpoint smoke coverage for:
  - `current-multi-sort`;
  - `current-too-many-sorts`.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`

`make test` result: 337 tests, 11134 assertions, 1 skipped.

## Boundary

- In scope: current/alias read-only endpoint multi-column sort contract, bounded fail-closed validation, response query echo, and direct endpoint smoke coverage.
- Out of scope: generated browser multi-sort controls, URL replay/control sync for more than the first visible sort row, dynamic sort rows, numeric/date-aware comparisons, field typing metadata, mutation behavior, history rewrite, and push.
