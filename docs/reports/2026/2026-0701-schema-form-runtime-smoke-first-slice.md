# Schema-form runtime smoke first slice

## Status

`FIRST_SLICE_DONE`

## Summary

Added a focused, non-product runtime smoke for the generated schema-form comparison artifact.

The smoke installs a temporary rjsf runtime in `work/tmp`, reads sample28 `NO-CODE-JSON-FORMS-PROBE` artifacts, checks Mtool extension metadata, and renders the generated JSON Schema with React server-side rendering.

## Implementation

- Added `mtool/scripts/check_no_code_schema_form_runtime_smoke.js`.
- Added `make sample28-no-code-schema-form-runtime-smoke`.
- The smoke checks:
  - `schema-form-contract.json`;
  - `json-schema.json`;
  - `ui-schema.json`;
  - contract/probe version markers;
  - sample28 form/action keys;
  - required field metadata;
  - editable field `x-mtool-action-field-role`;
  - editable field `x-mtool-client-write`;
  - UI Schema scopes;
  - rjsf SSR render output.

## Boundary

In scope:

- sample28 schema-form probe runtime smoke;
- temporary rjsf dependency install in `work/tmp`;
- generated artifact consumer viability check.

Out of scope:

- adopting rjsf or JSON Forms as product runtime code;
- replacing the custom React bridge;
- browser UI smoke;
- visual builder;
- server execution;
- transport or sync behavior.

## Verification

- `node mtool/scripts/check_no_code_schema_form_runtime_smoke.js --help`
- `make sample28-no-code-schema-form-runtime-smoke`

## Notes

The first run exposed an rjsf ESM/CJS default export shape difference. The smoke now resolves nested default exports for both `@rjsf/core` and `@rjsf/validator-ajv8`.
