# Lightweight No-Code UI Testing Plan

Date: 2026-07-09

Status: `DONE`

## Summary

#551 records the lightweight test strategy for moving toward L1 sample UI no-code conversion. The plan keeps headless Chrome as an outer smoke gate, but moves the normal development loop to fast JSON and static DOM contract tests.

## Decision

Use a test pyramid for no-code UI:

| Layer | Default tool | Role |
| --- | --- | --- |
| Fast contract | PHPUnit JSON assertions and PHP `DOMDocument` | Verify generated no-code metadata and stable HTML markers without launching a browser. |
| Lightweight interaction | Node built-in test plus `linkedom` or `happy-dom` if needed | Cover DOM events and local action-intent behavior only when static contracts are insufficient. |
| Browser smoke | Existing Playwright/headless Chrome scripts | Keep representative end-to-end and public preview coverage. |

## Plan Impact

- Add a durable design note at `docs/no-code-ui-testing.md`.
- Link the testing boundary from `docs/overview.md`.
- Add work units for:
  - fast no-code UI contract test harness;
  - dedicated no-code UI test lab sample;
  - fixture ladder;
  - optional lightweight JS interaction spike;
  - existing sample conversion checklist.

## Boundary

- This is a docs/planning slice only.
- No new package dependency is added here.
- No generated button execution or mutation route is enabled.
- Headless Chrome is not removed; it is repositioned as a slower representative gate.
- Push is not performed.

## Verification

- `git diff --check`
