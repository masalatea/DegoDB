# Runtime Data Field Typing Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#352 closes the runtime-data field typing lane after the #350 boundary plan and #351 first implementation slice.

The accepted capability is intentionally additive: current/alias `runtime-data.json` now publishes a stable read-model field metadata map, but query behavior still uses the existing display-string filter and sort semantics.

## Accepted Capability

- Runtime-data success responses include `read_model.contracts[contract_key].fields[field_key]`.
- Each field metadata entry includes `field_key`, `label`, and normalized `type`.
- Fail-closed runtime-data responses keep an empty `read_model.contracts` shape.
- Field types come from generated screen definition fields, not row-value inference.
- Unknown or empty types normalize to `string`.
- Common aliases normalize conservatively, such as `int` to `integer` and `bool` to `boolean`.
- Sample28, sample29, and sample31 endpoint smokes assert typed read-model metadata.

## Boundary Kept

- `contains` and `eq` filtering still compare rendered display strings.
- Multi-column sorting still compares rendered display strings.
- Runtime-data contract version remains `no-code-runtime-data-v0`.
- Generated browser controls do not yet choose operators from field type.
- Artifact-key previews remain static.
- Runtime mutation, retry, outbox processing, and status polling behavior are unchanged.

## Verification Baseline

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11152 assertions`, `1 skipped`)

## Next Candidates

The next behavior lane can now depend on explicit field metadata instead of ad hoc row-value inference. Recommended order:

1. Numeric filter semantics for `eq`, `gt`, `gte`, `lt`, and `lte` on numeric fields.
2. Numeric sort semantics for numeric fields.
3. Date/time filter and sort semantics.
4. Type-driven generated filter operator choices in the browser controls.
5. Validation messages for invalid typed query values.

## Push

Push was not performed.
