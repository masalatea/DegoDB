# Runtime Data Numeric Filter First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#354 implements the first numeric filter semantics slice for current/alias `runtime-data.json`.

The endpoint now uses explicit read-model field metadata from generated screen definitions to decide whether numeric comparison operators are allowed. This keeps the runtime-data contract additive and avoids inferring types from row values.

## Implemented Behavior

- Added accepted `filter_op[field]` values `gt`, `gte`, `lt`, and `lte`.
- Restricted numeric operators to fields whose normalized type is `integer` or `number`.
- Kept omitted operators and `contains` on the existing display-string contains behavior.
- Kept `eq` as display-string exact matching for compatibility, even on numeric fields.
- Parsed numeric query and row values with a strict numeric parser before comparison.
- Failed closed with HTTP 422 when a numeric operator targets a non-numeric field or non-numeric value.
- Left generated browser controls, endpoint version, artifact-key behavior, mutation, retry, outbox processing, and status polling unchanged.

## Smoke Coverage

- sample28 verifies `filter_op[priority]=gt` and expects first row key `1002`.
- sample29 verifies `filter_op[id]=gt` and expects first row key `2002`.
- sample31 verifies `filter_op[quantity_needed]=gte` and expects first row key `3102`.
- sample28/sample29/sample31 also verify a numeric operator on string `status` fails closed with a `numeric field` error.
- Existing invalid operator coverage now expects the expanded accepted operator list.

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

The next natural behavior lane is numeric sort semantics. It should start with a boundary plan before implementation because current sort behavior is still display-string based and user-visible table ordering can change.

## Push

Push was not performed.
