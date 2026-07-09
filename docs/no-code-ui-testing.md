# No-Code UI Testing

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
- Add lightweight DOM interaction tests only when JavaScript behavior cannot be covered by JSON or static DOM assertions.
- Keep Playwright/headless Chrome as a representative integration gate for selected samples.
- Do not require every small no-code fixture to launch a browser.
- Prefer stable `data-*` markers and semantic labels over brittle text-only assertions.
- Keep generated button execution and mutation behavior separately gated from readonly UI rendering tests.

## Dedicated No-Code Test Sample

The first new sample for this lane should be a no-code-specific test lab rather than a converted existing sample. It should start small:

- one list screen;
- one detail screen;
- one form screen;
- one disabled or dry-run action;
- fixed seed data;
- generated `screen-definition.json`;
- generated `runtime-preview.json`;
- generated `runtime-preview.html`;
- fast PHPUnit JSON and DOM contract assertions.

The sample can then grow fixture-by-fixture: required fields, readonly fields, enum/select fields, search/filter/sort state, action-intent draft, route-boundary metadata, unavailable reasons, and audit labels.

## Existing Sample Conversion

After the dedicated test lab proves the harness, existing sample conversion should use the same checklist:

- choose one sample by explicit candidate inventory;
- preserve its current behavior as the golden reference;
- add metadata contract tests;
- add static DOM contract tests;
- add lightweight interaction tests only for real client-side behavior;
- keep one representative browser smoke for final confidence.

## Design Boundary

Fast UI contract tests prove that generated metadata and generated markup expose the expected UI contract. They do not prove browser layout, CSS pixel rendering, or server mutation. Browser smoke and route-level tests remain responsible for those outer boundaries.
