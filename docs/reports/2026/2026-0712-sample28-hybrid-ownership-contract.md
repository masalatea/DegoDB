# Sample28 Hybrid Ownership Contract

## Status

`DONE`

## Purpose

Sample capability coverage did not require every sample or every screen to become fully no-code. The remaining gap was narrower: prove that a generated no-code artifact and custom frontend code can coexist in one workflow without confusing ownership.

This slice uses Sample28 because it already has one canonical no-code screen definition feeding three related artifacts:

- `NO-CODE-RUNTIME`: generated runtime preview.
- `NO-CODE-REACT-BRIDGE`: React + TypeScript consumer scaffold.
- `NO-CODE-JSON-FORMS-PROBE`: JSON Forms / rjsf comparison probe.

That makes Sample28 a better representative than replacing a domain editor flow. The goal is not to make Mtool own durable React UI product code; the goal is to make the handoff safe and explicit.

## Contract added

`bridge-contract.json` now includes `hybrid_ownership_contract` with:

- `contract_version`: `no-code-hybrid-ownership-v0`.
- `representative_slice`: `sample28-react-bridge-runtime-preview`.
- `generated_owns`: canonical screen definition, runtime preview, bridge contract schema/invariants, action-intent shape, validation hints, and custom-operation handoff metadata.
- `custom_owns`: React app shell, routing/navigation, component library/styling, durable client state, and server mutation adapter wiring outside the generated artifact.
- `shared_handoff`: the `bridge-contract.json` artifact, `no-code-runtime-action-intent-v0`, local/disposable preview state boundary, server-side authority boundary, and fallback path to runtime/schema-form artifacts.
- `test_ownership`: PHP contract tests for generated files, React build/browser smoke for the custom adapter scaffold, and JSON Forms/rjsf smoke as comparison evidence.

`CONSUMER-NOTES.md` renders the same section for human readers, and the generated TypeScript contract exposes `MtoolHybridOwnershipContract`.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `make sample28-pack-runtime-test`
- `make sample28-no-code-react-bridge-build-smoke`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make sample28-no-code-schema-form-runtime-smoke`

Full suite is required before commit because this is a code change.

## Decision

The hybrid capability is `COVERED` for the required sample matrix. The supported model is contract-based partial replacement:

- generated artifacts cover the stable metadata/action-intent/handoff shape;
- custom code owns real application UX and durable state;
- execution authority remains with explicit server routes, auth/CSRF/audit/transaction policy;
- schema-form probes are fallback/comparison artifacts, not a requirement to replace the custom React path.

This keeps the original product philosophy intact: cover the tool-supported 80-90% cleanly and avoid forcing full automation where custom code is the right boundary.
