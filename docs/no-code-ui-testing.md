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

It is not yet a generated route replacement. The first reusable fast DOM contract harness exists in `tests/Support/NoCodeUiContractAssertions.php`; the next selected design gate is a reusable status filter contract for the generated list preview. Safe action-input mapping remains after that.

## Design Boundary

Fast UI contract tests prove that generated metadata and generated markup expose the expected UI contract. They do not prove browser layout, CSS pixel rendering, or server mutation. Browser smoke and route-level tests remain responsible for those outer boundaries.
