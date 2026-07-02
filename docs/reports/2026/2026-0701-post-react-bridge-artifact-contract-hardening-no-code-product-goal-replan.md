# Post-React bridge artifact contract hardening no-code product goal replan

## Status

`DONE`

## Decision

Next selected work: **Editable React bridge form state first slice**.

React bridge build/browser smoke, display/form state shaping, and artifact contract hardening are now complete for the first slice. The next smallest product-facing gap is to let the generated React bridge form inputs manage local edit state and emit changed scalar values into the generated action intent.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Editable React bridge form state first slice | 1 - 3 days | Selected. This is the most direct continuation after display/input helpers and contract hardening. |
| JSON Forms / rjsf transform probe | 1 - 3 days | Deferred. Useful as a comparison probe, but the custom React bridge should prove editable behavior first. |
| React bridge contract documentation polish | 0.5 - 1 day | Deferred. The contract is now covered by smoke/PHP tests; docs can follow after the next behavior slice or when consumer-facing docs become the blocker. |
| Retry audit trail | 0.5 - 2 days | Deferred. Useful later, but not the current React bridge product-facing gap. |

## Boundary

In scope for the selected next slice:

- generated React bridge form inputs keep local editable scalar state;
- action intent emits edited scalar values rather than only initial runtime preview values;
- sample28 browser smoke proves a changed form value reaches `no-code-runtime-action-intent-v0`;
- existing contract schema/invariant remains unchanged unless the behavior exposes a necessary field.

Out of scope:

- visual styling polish;
- validation UX beyond existing metadata hints;
- durable React component library ownership inside Mtool;
- JSON Forms / rjsf transform;
- full generated application shell;
- remote transport or conflict resolution.

## Verification Plan

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make sample28-no-code-react-bridge-build-smoke`
- `make test` if shared generator/test contract behavior changes
