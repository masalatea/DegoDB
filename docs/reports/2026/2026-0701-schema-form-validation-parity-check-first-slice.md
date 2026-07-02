# 2026-0701 Schema-Form Validation Parity Check First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

The schema-form comparison artifact now carries Mtool's blank-required string policy.

For required string fields, generated JSON Schema includes `minLength: 1`, `pattern: "\\S"`, and `x-mtool-blank-is-missing: true`. This keeps JSON Forms / rjsf comparison artifacts aligned with generated runtime and React bridge behavior, where whitespace-only required inputs fail closed.

## Scope

In scope:

- `NO-CODE-JSON-FORMS-PROBE` JSON Schema metadata.
- Schema-form contract `validation_parity` metadata.
- Consumer notes for the validation parity boundary.
- rjsf runtime smoke assertion for blank required strings.

Out of scope:

- JSON Forms / rjsf product runtime adoption.
- Full validation DSL.
- Cross-field validation.
- Server-side validation behavior.
- Localization.

## Implementation Notes

- Required string properties now include `minLength`, `pattern`, and `x-mtool-blank-is-missing`.
- Contract invariants include `x-mtool-blank-is-missing` as a stable extension key.
- Generated consumer notes include a Validation Parity Boundary section.
- The schema-form runtime smoke asserts the generated schema rejects a whitespace-only required field through rjsf validator plumbing.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `node --check mtool/scripts/check_no_code_schema_form_runtime_smoke.js`
- Generated a temporary schema-form probe at `/tmp/dego-schema-form-parity-probe` from existing sample28 runtime artifacts and current generator code.
- `node mtool/scripts/check_no_code_schema_form_runtime_smoke.js --probe=/tmp/dego-schema-form-parity-probe --work-dir=/tmp/dego-schema-form-parity-work --cache=/tmp/dego-schema-form-parity-cache`

## Verification Gap

`make sample28-no-code-schema-form-runtime-smoke` and full `make test` were not completed in this turn because Docker daemon was unavailable:

`Cannot connect to the Docker daemon at unix:///Users/matsue/.docker/run/docker.sock. Is the docker daemon running?`

The temporary-probe Node smoke covers the changed generator output and rjsf validation behavior without Docker. Docker-backed sample regeneration should be rerun once Docker is available.
