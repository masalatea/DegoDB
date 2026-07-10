# Runtime Execution Request Contract First Slice

Date: 2026-07-03
Status: FIRST_SLICE_DONE

## Summary

Added the first server-backed runtime execution request contract helper. This slice does not expose a mutation endpoint and does not change generated preview behavior. It creates the small fail-closed request normalization boundary that a future endpoint can call before dispatching an action.

## Accepted Capability

- Requires `POST` before accepting a runtime execution request.
- Requires a matching CSRF token.
- Requires submitted project key to match the expected runtime binding.
- Requires submitted artifact key to match the expected runtime binding.
- Requires a non-empty action key.
- Normalizes scalar `input` fields and drops blank, nested, or object values.
- Returns explicit runtime artifact binding fields for later dispatch / audit code.

## Verification

- PHP lint in Docker: passed.
- Focused `NoCodeRuntimeTest`: `10 tests, 160 assertions`.
- `make sample28-no-code-runtime-ui-smoke`: passed after adding Docker and bundled Node to `PATH`.
- `git diff --check`: passed.
- Full `make test`: `329 tests, 10857 assertions, skipped 1`.

## Remaining Candidates

- Run focused `NoCodeRuntimeTest` and full test suite in the Docker-backed environment.
- Add the guarded public/runtime execution endpoint that calls this helper.
- Wire generated preview action submission to the endpoint after endpoint contract and policy are explicit.
- Add conservative result refresh and execution error surface.
- Add audit trail for runtime execution attempts.
