# Runtime Data Date/Time Semantics First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#359 implements the first runtime-data date/time semantics slice after the numeric semantics lane.

The slice adds an explicit date fixture to sample31 and enables strict date/time ordered filtering and sorting for explicit `date` / `datetime` / `time` read-model fields on current/alias `runtime-data.json`.

## Implemented Scope

- Added sample31 `inventory_request.needed_by DATE NOT NULL` with seeded values:
  - `2026-07-10`;
  - `2026-07-15`.
- Added `needed_by` as a read-only shared contract field for sample31.
- Updated shared contract type normalization so:
  - SQL `DATE` maps to `date`;
  - SQL `DATETIME` / `TIMESTAMP` maps to `datetime`;
  - SQL `TIME` maps to `time`.
- Added `date`, `datetime`, and `time` to the shared contract supported field-type vocabulary.
- Preserved app-local storage, DTO, managed-operation, and generated runtime handling for date/time values as strings.
- Added strict runtime-data date/time parsing:
  - `date`: `YYYY-MM-DD`;
  - `datetime`: `YYYY-MM-DDTHH:MM:SS` or `YYYY-MM-DD HH:MM:SS`;
  - `time`: `HH:MM:SS`.
- Extended current/alias runtime-data filters so `gt` / `gte` / `lt` / `lte` are allowed for explicit numeric or date/time fields only.
- Extended current/alias runtime-data sort so explicit date/time fields are sorted by normalized lexical date/time value.
- Added fail-closed coverage for invalid date values.

## Boundaries Preserved

- Endpoint version remains `no-code-runtime-data-v0`.
- URL contract remains additive:
  - `filter[field]=value`;
  - `filter_op[field]=gt|gte|lt|lte`;
  - `sort[field]=asc|desc`.
- Omitted operator / `contains` and `eq` stay display-string based for compatibility.
- Generated browser controls are unchanged; type-driven operator choices remain deferred.
- Artifact-key previews remain static.
- Mutation, retry, outbox processing, and status polling are unchanged.
- No timezone conversion or natural-language date parsing was added.

## Verification

- `php -l mtool/shared/shared_contract_core.php`
- `php -l mtool/app/shared_contract_manifest.php`
- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `php -l mtool/scripts/check_sample28_no_code_runtime_outbox_process_smoke.php`
- `php -l mtool/scripts/lib/sample31_no_code_inventory_request_demo_check.php`
- `git diff --check`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11152 assertions`, `1 skipped`)

## Next Candidates

- Closure report for the date/time semantics lane.
- Type-driven browser operator choices that expose ordered operators only for numeric/date/time fields.
- Timezone-offset policy for `datetime`.
- Null/empty date/time sorting and filtering policy.
- Local stack review / commit cleanup before push.

## Push

Push was not performed.
