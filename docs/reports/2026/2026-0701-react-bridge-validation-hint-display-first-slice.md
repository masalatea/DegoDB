# React bridge validation hint display first slice

## Status

`FIRST_SLICE_DONE`

## Summary

Generated React bridge form fields now surface existing `required` / `readonly` metadata as lightweight field hints and input attributes.

This slice does not add a validation engine. It keeps the behavior inside the generated React bridge preview and only exposes metadata that already exists in the no-code runtime field model.

## Implementation

- Generated form labels now include:
  - `data-field-required`;
  - `data-field-readonly`.
- Generated inputs now include:
  - `required`;
  - `readOnly`;
  - `aria-required`.
- Generated form fields now show a lightweight hint:
  - `Required`;
  - `Read only`;
  - `Optional`.
- React bridge browser smoke now verifies sample28 required metadata and required hint while preserving editable-state action intent coverage.

## Boundary

In scope:

- generated React bridge required/readonly metadata display;
- required/readOnly input attributes;
- sample28 browser smoke coverage.

Out of scope:

- custom validation rules;
- server-side validation;
- visual styling polish;
- JSON Forms / rjsf transform;
- full generated application shell.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make sample28-no-code-react-bridge-build-smoke`
- `make test`

## Next

Next replan should choose between JSON Forms / rjsf transform probe, React bridge contract documentation polish, or another no-code product-facing gap.
