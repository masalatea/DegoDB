# Post Guarded Submit Payload Handoff Lane Closure

Status: `DONE`

Plan: #690 post guarded submit payload handoff lane closure

## Summary

#690 accepts #689 as the fast non-browser guarded-submit payload handoff contract for sample18.

The payload assembly itself is now covered enough to avoid jumping straight to heavier browser smoke. The next practical risk is whether keyed generated actions reliably receive the selected row identity in the generated runtime preview.

## Accepted From #689

- Generated action intent assembly splits create/update/complete fields into key/input payloads.
- The assembled payload normalizes through the generated-submit route contract.
- Missing required input fails closed before route normalization.
- Generated runtime HTML includes guarded submit POST assembly markers for operation key, CSRF token field, flat input fields, same-origin credentials, and JSON accept headers.
- Mutation and route replacement remain disabled unless explicitly enabled by existing route flags.

## Decision

Promote selected-row/key handoff hardening before browser smoke or availability expansion.

Reason: update/complete are only route-compatible if generated runtime state can reliably provide `id`. That row identity boundary should be proven with fast metadata/DOM/runtime-preview assertions before spending headless browser budget.

## Next

Promote #691: sample18 selected row/key handoff fast contract first slice.

That slice should verify row key source, selected key metadata, keyed action draft payload, and fail-closed missing-key behavior.
