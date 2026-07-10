# Runtime Data Field Typing First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#351 implements the first runtime-data field typing slice after the #350 boundary plan.

Current/alias `runtime-data.json` now exposes additive read-model field metadata under a top-level `read_model` section. The metadata is sourced from generated screen definition fields and includes stable per-contract field entries with `field_key`, `label`, and normalized `type`.

## Implemented Scope

- Added `read_model.contracts[contract_key].fields[field_key]` to successful runtime-data responses.
- Added an empty `read_model.contracts` shape to fail-closed runtime-data error responses.
- Normalized empty or unknown field types to `string`.
- Normalized common aliases such as `int` / `bigint` / `smallint` to `integer` and `bool` to `boolean`.
- Kept existing display-string filter and sort behavior unchanged.
- Kept runtime-data contract version unchanged at `no-code-runtime-data-v0`.
- Kept URL replay, browser history replay, sortable-header behavior, and artifact-key static behavior unchanged.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11152 assertions`, `1 skipped`)

The sample runtime-data endpoint smoke now asserts read-model field type metadata across sample28, sample29, and sample31, including integer fields such as `id`, `priority`, and `quantity_needed`.

## Deferred

- Numeric/date-aware filter semantics.
- Numeric/date-aware sort semantics.
- UI operator choices driven by field type.
- Validation messages for invalid typed query values.
- Endpoint contract version bump, unless a later semantic change requires it.

## Push

Push was not performed.
