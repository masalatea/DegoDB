# Post-React bridge validation hint display no-code product goal replan

## Status

`DONE`

## Decision

Next selected work: **React bridge action feedback display first slice**.

The custom React bridge now proves runtime display, editable form state, metadata hints, build smoke, and browser smoke. The next smallest product-facing continuation is to show local action/intent feedback inside the generated React bridge, parallel to the HTML preview's action feedback, without adding persistence or transport.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| JSON Forms / rjsf transform probe | 1 - 3 days | Deferred. Useful comparison, but the custom React bridge can absorb one more small UX proof first. |
| React bridge contract documentation polish | 0.5 - 1 day | Deferred. Useful, but behavior remains the clearer next product-facing gap. |
| React bridge action feedback display | 0.5 - 2 days | Selected. It closes the loop from edited form input to visible generated intent feedback. |
| Retry audit trail | 0.5 - 2 days | Deferred unless accountability becomes the next concrete product gap. |

## Boundary

In scope for the selected next slice:

- generated React bridge displays local last-intent feedback;
- browser smoke verifies the feedback state after an intent probe;
- no persistence, transport, scheduler, or validation engine.

Out of scope:

- server execution;
- visual styling polish;
- full generated app shell;
- JSON Forms / rjsf transform.

## Verification Plan

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make sample28-no-code-react-bridge-build-smoke`
- `make test` if shared generator/test contract behavior changes
