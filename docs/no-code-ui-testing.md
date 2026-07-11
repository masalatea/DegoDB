# No-Code UI Testing

English companion:
This document defines the default testing shape for generated no-code UI. It is a durable design note, not a dated progress report.

## Goal

No-code samples should grow from small, fast, repeatable UI contracts before they rely on full browser smoke tests. Headless Chrome remains valuable, but it should be the outer confirmation layer rather than the default inner-loop test.

The intended roadmap order is:

1. Build no-code-only sample UI fixtures with tests.
2. Convert existing samples using the same fixture and contract checklist.
3. Apply the pattern to contained Mtool self no-code workflows.

## Test Layers

| Layer | Tool | What it checks | What it does not check |
| --- | --- | --- | --- |
| Metadata contract | PHPUnit JSON assertions | `screen-definition.json`, `runtime-preview.json`, screen keys, field metadata, action metadata, route-boundary metadata, disabled reasons. | Browser parsing, CSS, focus, DOM events. |
| HTML DOM contract | PHPUnit plus PHP `DOMDocument` | Stable generated HTML markers, list/detail/form regions, field labels, disabled buttons, action keys, accessible labels, draft/debug sections. | JavaScript execution and real layout. |
| Lightweight DOM interaction | Node built-in test plus `linkedom` or `happy-dom` if needed | DOM events, local action intent draft updates, client-side required-state changes, small runtime helpers. | Real browser compatibility and CSS layout. |
| Browser smoke | Existing Playwright/headless Chrome scripts | Public preview integration, current/alias routing, auth, fetch, real submit/outbox handoff, selected end-to-end behavior. | Fast inner-loop coverage for every generated fixture. |

## Default Policy

- Start every no-code UI slice with metadata contract tests.
- Add HTML DOM contract tests before adding a browser smoke.
- Use `tests/Support/NoCodeUiContractAssertions.php` for reusable PHPUnit JSON and `DOMDocument` assertions before adding sample-specific string checks.
- Add lightweight DOM interaction tests only when JavaScript behavior cannot be covered by JSON or static DOM assertions.
- Keep Playwright/headless Chrome as a representative integration gate for selected samples.
- Do not require every small no-code fixture to launch a browser.
- Prefer stable `data-*` markers and semantic labels over brittle text-only assertions.
- Keep generated button execution and mutation behavior separately gated from readonly UI rendering tests.

## Lightweight DOM Tooling Decision

`make no-code-lightweight-dom-tooling-check` records the current `linkedom` / `happy-dom` adoption state without adding a root npm manifest. The current recommendation is to defer either dependency until a concrete interaction gap is promoted.

When that happens, prefer `linkedom` first for narrow generated-runtime event probes that need query selection and basic event dispatch. Consider `happy-dom` only if the probe needs broader browser API coverage. Clipboard, fetch, layout, and real compatibility remain browser-smoke responsibilities unless explicitly stubbed.

## Dedicated No-Code Test Sample

The first new sample for this lane is `sample32-no-code-ui-test-lab`, a no-code-specific test lab rather than a converted existing sample. It starts small:

- one list screen;
- one detail screen;
- one form screen;
- one disabled or dry-run action;
- fixed seed data;
- explicit fixture JSON under `fixtures/`;
- generated `screen-definition.json`;
- generated `runtime-preview.json`;
- generated `runtime-preview.html`;
- fast PHPUnit JSON and DOM contract assertions.

The first fixture ladder rung is `fixtures/no-code-ui-contract-fixtures.json`. It names the expected screen keys, screen types, list fields, form fields, disabled managed action markers, and preview rows. The PHPUnit integration test and sample pack checker both read this fixture so future rungs can add expectations before any browser smoke is required.

The sample can now grow fixture-by-fixture: required fields, readonly fields, enum/select fields, search/filter/sort state, action-intent draft, route-boundary metadata, unavailable reasons, and audit labels.

## Existing Sample Conversion

After the dedicated test lab proves the harness, existing sample conversion should use the same checklist:

- choose one sample by explicit candidate inventory;
- preserve its current behavior as the golden reference;
- add metadata contract tests;
- add static DOM contract tests;
- add lightweight interaction tests only for real client-side behavior;
- keep one representative browser smoke for final confidence.

## Sample18 Conversion Checklist

`sample18-mini-task-board-demo` is the first existing sample UI conversion target. Before replacing or shadowing its hand-coded task board route, the no-code conversion must satisfy this minimum contract:

The applied checklist fixture is `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`. `Sample18MiniTaskBoardDemoTest` reads it as the current fast contract source before any browser smoke or generated route replacement is considered.

| Area | Minimum capability | Fast evidence |
| --- | --- | --- |
| Data shape | `task_card` fields for `id`, `title`, `body`, `status`, `assigned_to`, `priority`, `due_date`, `completed_at`, and `updated_at` are represented with key/required/readonly/client-write roles. | Metadata contract assertions on `screen-definition.json` and `runtime-preview.json`. |
| List screen | The generated list exposes task title/body, status, assignee, priority, due date, row identity, and a status filter boundary. | PHPUnit JSON assertions plus DOM markers for table/list region, columns, and filter controls. |
| Detail screen | The generated detail exposes the same row identity and readonly display fields without requiring mutation. | DOM assertions for detail region, field labels, and selected row markers. |
| Form screen | The generated form exposes create/update input fields, required title/body/status behavior, number/date field metadata, and readonly identity handling. | JSON field-role assertions and DOM assertions for inputs, required markers, and disabled or dry-run submit controls. |
| Actions | Create, update, complete, reopen, and delete are described as route-boundary-aware disabled or dry-run operations before any mutation is enabled. | Action metadata assertions for operation keys, route boundaries, unavailable reasons, and `generated_button_enabled=false`. |
| Golden comparison | Existing `/samples/sample18-task-board` behavior remains the comparison target. | A golden fixture names stable seed rows and expected DOM markers before generated no-code output is compared. |
| Outer smoke | Browser or HTTP smoke remains representative only. | Existing `make sample18-http-runtime-smoke` stays outside the fast inner loop. |

### Sample18 Current Boundary

`sample18-mini-task-board-demo` is accepted as the first L1 existing sample UI no-code entry only in a metadata-first and preview-first sense. It has a golden fixture, generated readonly list/detail/form metadata, generated runtime preview rows, and disabled dry-run action metadata with route boundaries for create, update, complete, reopen, and delete.

It is not yet a generated route replacement. The first reusable fast DOM contract harness exists in `tests/Support/NoCodeUiContractAssertions.php`; the status filter contract ties the curated route filter values to generated list metadata, and generated-submit route responses now expose tested execution/config metadata before browser smoke is broadened.

### Sample18 Generated Submit Availability

Generated-submit execution is disabled by default. The route remains safe for metadata and blocked-response inspection unless both mutation and executor enablement are explicitly true:

- app config `sample18_generated_submit_mutation_enabled=true` or env `MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED=1`;
- app config `sample18_generated_submit_executor_enabled=true` or env `MTOOL_SAMPLE18_GENERATED_SUBMIT_EXECUTOR_ENABLED=1`.

App config takes precedence over env fallback. Execution still must pass request validation, CSRF, audit append, idempotency, execution guard, DBAccess transaction, post-commit execution audit append, and idempotency execution-outcome update. The success policy is all-success-or-failure: user-facing success is returned only after every required step succeeds.

The route response includes `executor_config` metadata so tests and UI can inspect why execution is ready, disabled, or failed. Important fields are:

- `status`: `ready`, `disabled`, or `failed`;
- `mutation_enabled` / `executor_enabled`;
- `mutation_enablement_source` / `executor_enablement_source`;
- `dependency_source`: `default_runtime_reference` or `injected_transaction_callables`;
- `runtime_reference_dir`, `failure_code`, `missing_file`, and `reasons`.

Injected transaction callables are the highest-priority execution dependency for focused tests. Without injected callables, the route validates the sample18 generated runtime reference files before opening a transaction. Missing or unreadable reference files fail closed with `executor_default_runtime_file_missing`.

Browser smoke is still an outer representative gate. Prefer fast route/PHPUnit coverage for config and response semantics first, then use browser smoke for public preview, auth, submit handoff, and rendered feedback integration.

### Sample18 Generated Submit Response Contract

Generated-submit route responses use this compact status contract:

| Outcome | HTTP | `result` | `ok` | Recovery |
| --- | ---: | --- | --- | --- |
| invalid method / CSRF / payload / operation | 405 / 403 / 422 / 404 | `invalid` | `false` | no |
| disabled or duplicate non-execution | 409 | `blocked` | `false` | no |
| config or dependency failure before execution | 500 | `failed` | `false` | no |
| DBAccess/rollback failure before commit | 500 | `failed` | `false` | no |
| commit-status-unknown or post-commit recording failure | 500 | `failed` | `false` | yes |
| all required execution and recording steps succeeded | 200 | `executed` | `true` | no |

`failure_code` is required on every non-success response. Recovery-required failures must expose `route_execution.recovery_required=true` plus the relevant recovery reason from `transaction_result` or `post_commit_recording`.

### Sample18 Generated Action/Input Gap Inventory

The generated metadata and executable generated-submit route are close, but they are not the same boundary yet. Treat the route as the stricter contract before expanding browser smoke or enabling broader availability.

| Area | Current state | Gap to close before broader availability |
| --- | --- | --- |
| Executable operation set | The route contract executes `create_task_card`, `update_task_card`, and `complete_task_card`. | Generated metadata still names `reopen_task_card` and `delete_task_card` as curated-route-only candidates. They must remain disabled until DBAccess/custom adapter metadata exists. |
| Action payload shape | Route requests are `operation_key` plus flat action fields and `_csrf_token`. | Fast DOM/metadata assertions should prove every generated managed action exposes the same operation key, submit URL, CSRF handoff, and field names the route normalizer accepts. |
| Key field handoff | `update_task_card` and `complete_task_card` require `id`; create has no key fields. | Generated list/detail/form markup must expose a reliable row identity source for keyed actions before any generated button can be considered executable. |
| Required input handoff | Create/update require `title`; create has optional body/assignee/priority/due date; update also allows status. | Draft input collection must fail closed when required client fields are missing and must not send readonly/server-managed fields as client authority. |
| Availability state | Generated buttons remain disabled/blocked by default; route execution also requires explicit mutation and executor enablement. | The UI contract should show disabled/blocked state, unavailable reason, and route executor readiness consistently before outer browser smoke. |
| Success policy | The route follows all-success-or-failure semantics: user-facing success appears only after validation, CSRF, audit, idempotency, transaction, execution, and post-commit recording all succeed. | Generated UI tests should assert the handoff preserves this policy instead of treating a click or partial draft as success. |

Recommended next slice: add a focused fast contract that compares sample18 generated managed-action metadata and generated DOM attributes against the route contract for create/update/complete. Keep reopen/delete visible only as disabled curated-route candidates.

### Sample18 Generated Action/Input Route Compatibility

The first fast compatibility slice is covered in `Sample18MiniTaskBoardDemoTest`:

- route-compatible operations are limited to `create_task_card`, `update_task_card`, and `complete_task_card`;
- `reopen_task_card` and `delete_task_card` remain disabled metadata-only candidates until DBAccess/custom adapter metadata exists;
- generated action inventory must match route key fields, required client fields, optional client fields, and server-managed fields;
- generated screen-definition action fields must match route-compatible roles (`key` or `input`), required flags, and client-write flags;
- generated runtime HTML must expose matching `data-action-key`, `data-operation-key`, submit URL, CSRF handoff, and blocked route binding attributes.

### Sample18 Guarded Submit Payload Handoff

The fast payload handoff contract stays non-browser:

- generated action intent assembly splits route-compatible fields into key payload and client input payload;
- the assembled key/input payload normalizes through the sample18 generated-submit route contract for create/update/complete;
- missing required action input fails closed before route payload normalization;
- generated runtime HTML includes the guarded submit JS path that posts `operation_key`, the configured CSRF token field, flat input fields, same-origin credentials, and JSON accept headers.

### Sample18 Selected Row/Key Handoff

The selected-row/key contract stays fast and covers the static preview plus runtime source:

- generated render fields retain `is_key` metadata so list rows can expose stable `data-runtime-row-key` markers;
- runtime list rendering falls back to key fields when the current list action set does not include a key-bearing action;
- update/complete action intents place `id` in the key payload when a selected row id is supplied;
- missing keyed action input fails closed before guarded submit payload normalization;
- runtime source keeps the selected-key refresh path for runtime-data-backed previews.

### Sample18 Generated Runtime Browser Smoke

The first narrow browser smoke is an outer confirmation layer for the generated sample18 runtime preview. It verifies that the public preview renders row key markers, guarded submit attributes, disabled/default execution state, and blocked generated-submit feedback without enabling mutation or broader generated availability.

This smoke intentionally stays narrow:

- list rows must expose `data-runtime-row-key` and the first generated row key must match the selected fixture key;
- managed action buttons must expose the route-compatible submit URL, CSRF handoff, binding state, guarded click inventory, payload assembly, blocked response handling, and fail-closed result markers;
- generated action controls remain disabled/default-safe for actual execution, while the guarded click path renders blocked feedback for generated submit;
- mutation availability and broader execution enablement remain covered by route/PHPUnit contracts until explicitly promoted.

## Design Boundary

Fast UI contract tests prove that generated metadata and generated markup expose the expected UI contract. They do not prove browser layout, CSS pixel rendering, or server mutation. Browser smoke and route-level tests remain responsible for those outer boundaries.
