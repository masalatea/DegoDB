# React bridge contract documentation polish first slice

## Status

`FIRST_SLICE_DONE`

## Summary

Generated React bridge artifacts now include consumer-facing boundary notes.

This slice adds `CONSUMER-NOTES.md` and structured `consumer_notes` to the React bridge contract so frontend/schema-form consumers can understand what Mtool owns, what the generated scaffold proves, and what remains outside the artifact boundary.

## Implementation

- Added `CONSUMER-NOTES.md` to `no-code-react-bridge` emitted files.
- Added `consumer_notes` to `bridge-contract.json`.
- Added `CONSUMER-NOTES.md` to `contract_invariants.required_files`.
- Linked the notes from generated React bridge `README.md`.
- Extended sample28 checker coverage for:
  - required-file invariant;
  - structured contract boundary note;
  - generated notes file.
- Extended shared foundation coverage for generated file presence and published artifact presence.
- Updated React bridge build/browser smoke required-file checks.

## Boundary

In scope:

- generated React bridge consumer notes;
- structured contract notes;
- required-file invariant coverage;
- sample28 and foundation verification.

Out of scope:

- new runtime behavior;
- schema-form hardening;
- visual builder;
- full generated application shell;
- server execution;
- transport.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `node mtool/scripts/check_no_code_react_bridge_build_smoke.js --help`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-pack-runtime-test`
- `make sample28-no-code-react-bridge-build-smoke`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make test`

## Next

Next replan should choose between schema-form probe hardening, generated runtime visual polish follow-up, retry audit trail, or another product-facing no-code gap.
