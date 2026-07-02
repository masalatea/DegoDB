# Post React bridge action feedback display no-code product goal replan

## Status

`DONE`

## Summary

This replan chooses JSON Forms / rjsf transform probe as the next small no-code product-facing implementation after React bridge action feedback display.

The custom React bridge has now proved the minimum behavior chain: generated artifact contract, build/browser smoke, readable display values, editable form state, required/readonly metadata hints, and local action feedback. The next useful risk is whether the same no-code screen metadata can also be expressed as schema-form friendly JSON Schema / UI Schema style output without replacing the custom bridge.

## Decision

Selected:

- JSON Forms / rjsf transform probe first slice.

Deferred:

- React bridge contract documentation polish.
- Retry audit trail.

## Why

JSON Forms / rjsf transform is now the strongest comparison probe because:

- the custom React bridge path is stable enough to compare against;
- existing screen field metadata already contains required/readonly/input semantics;
- schema-form output can clarify whether Mtool should expose framework-neutral form contracts beside generated React code;
- the first slice can stay comparison-only and avoid taking ownership of a JSON Forms / rjsf runtime UI.

## Boundary

In scope:

- choose one next small product-facing continuation after React bridge action feedback display;
- promote a schema-form comparison probe;
- keep the custom React bridge as the current default first adapter.

Out of scope:

- replacing the custom React bridge;
- installing a JSON Forms / rjsf runtime UI;
- visual builder;
- full generated application shell;
- server execution or transport.

## Next

Implement JSON Forms / rjsf transform probe first slice:

- define the comparison artifact boundary;
- emit a small schema-form style artifact for sample28 form fields;
- verify required/readonly/input mapping against existing generated runtime / React bridge behavior;
- update docs and current plan after focused verification.
