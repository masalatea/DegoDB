# Editable React bridge form state first slice

## Status

`FIRST_SLICE_DONE`

## Summary

Generated React bridge form inputs now keep local editable state and emit changed scalar values through `no-code-runtime-action-intent-v0`.

This slice stays inside the generated React bridge preview boundary. It does not add persistence, transport, validation UX, a durable React component library, or a full app shell.

## Implementation

- `src/MtoolNoCodeRuntime.tsx` template now renders form screens through a small stateful form component.
  - Inputs initialize from runtime cell/value helpers.
  - Edited values stay in React local state.
  - Form actions pass the local scalar input state to `createActionIntent`.
- `src/mtoolNoCodeBridge.ts` template now exposes `editableInputFromItem()`.
- Browser-smoke globals now keep per-screen form state for deterministic smoke probing.
- `check_no_code_react_bridge_browser_smoke.js` now fills sample28 `body` and verifies the changed value in the observed action intent.

## Boundary

In scope:

- generated React bridge local form state;
- scalar changed-value action intent;
- sample28 React bridge browser smoke coverage.

Out of scope:

- visual styling polish;
- validation UX beyond existing metadata hints;
- durable React component library ownership inside Mtool;
- JSON Forms / rjsf transform;
- full generated application shell;
- remote transport or conflict resolution.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make sample28-no-code-react-bridge-build-smoke`
- `make test`

## Next

Next replan should choose between JSON Forms / rjsf transform probe, React bridge contract documentation polish, or another product-facing no-code gap after editable form behavior.
