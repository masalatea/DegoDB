# Post-editable React bridge form state no-code product goal replan

## Status

`DONE`

## Decision

Next selected work: **React bridge validation hint display first slice**.

Editable React bridge form state proved that generated inputs can keep local state and emit changed scalar values through `no-code-runtime-action-intent-v0`. The next smallest product-facing continuation is to surface existing field metadata such as `required` and `readonly` in the generated React bridge without adding a validation engine.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| JSON Forms / rjsf transform probe | 1 - 3 days | Deferred. Useful comparison, but one more narrow custom-bridge UX proof should land first. |
| React bridge contract documentation polish | 0.5 - 1 day | Deferred. Useful, but generated behavior still has one small obvious metadata gap. |
| React bridge validation hint display | 0.5 - 2 days | Selected. It is the smallest product-facing continuation after editable form state. |
| Retry audit trail | 0.5 - 2 days | Deferred unless accountability becomes the next concrete product gap. |

## Boundary

In scope for the selected next slice:

- generated React bridge form fields expose existing `required` / `readonly` metadata;
- display lightweight field hints without adding a validation engine;
- browser smoke verifies required metadata for sample28 form fields.

Out of scope:

- custom validation rules;
- server-side validation;
- visual styling polish;
- JSON Forms / rjsf transform;
- full generated application shell.

## Verification Plan

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make sample28-no-code-react-bridge-build-smoke`
- `make test` if shared generator/test contract behavior changes
