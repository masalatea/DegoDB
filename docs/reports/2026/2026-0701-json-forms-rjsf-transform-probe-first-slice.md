# JSON Forms / rjsf transform probe first slice

## Status

`FIRST_SLICE_DONE`

## Summary

Added a comparison-only schema-form artifact path for no-code form metadata.

This first slice introduces `no-code-json-forms-probe` and sample28 `NO-CODE-JSON-FORMS-PROBE`. It emits JSON Schema / UI Schema style artifacts for the generated form screen without replacing the custom React bridge or bundling JSON Forms / rjsf runtime UI.

## Implementation

- Added `NoCodeJsonFormsProbe` class type and `no-code-json-forms-probe` artifact strategy.
- Added default runtime-source path support under `mtool/no-code-json-forms-probe-source-outputs/`.
- Generated:
  - `schema-form-contract.json`
  - `json-schema.json`
  - `ui-schema.json`
  - `README.md`
- Added sample28 `NO-CODE-JSON-FORMS-PROBE` Source Output seed.
- Extended sample28 checker to publish and verify the probe artifact.
- Added shared foundation coverage for strategy registration, generated files, schema contract markers, required fields, and UI schema scope.

## Boundary

In scope:

- one comparison artifact for schema-form ecosystems;
- sample28 form-field coverage;
- JSON Schema / UI Schema style metadata;
- focused checker and foundation verification.

Out of scope:

- replacing the custom React bridge;
- installing or bundling JSON Forms / rjsf runtime UI;
- visual builder;
- full generated application shell;
- server execution or transport.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `php -l mtool/app/domain_validation.php`
- `php -l mtool/app/project_output_service.php`
- `php -l mtool/app/runtime_storage_paths.php`
- `make sample28-pack-runtime-test`
- `make sample28-no-code-react-bridge-build-smoke`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make test`

## Next

Next replan should choose between React bridge contract documentation polish, schema-form probe hardening, generated runtime visual polish follow-up, or another product-facing no-code gap.
