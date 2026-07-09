# Lightweight JS Interaction Test Spike

Status: `DONE`

Plan item: #555 lightweight JS interaction test spike

## Summary

Evaluated the lightweight DOM interaction lane for generated no-code UI without adding a repository-wide npm dependency.

## Scope

- Added `mtool/scripts/check_no_code_lightweight_dom_tooling.js`.
- Added `make no-code-lightweight-dom-tooling-check`.
- The checker inspects the sample32 fixture, root Node manifest state, and optional availability of `linkedom` / `happy-dom`.
- The recorded recommendation is to defer adding either dependency until a concrete event behavior gap is promoted.

## Decision

The repository currently has no root `package.json` / lockfile. Adding `linkedom` or `happy-dom` only for a speculative lane would make the fast test loop heavier before the first concrete interaction gap is selected.

Keep PHPUnit JSON / PHP `DOMDocument` fixture contracts as the default inner loop. Use a temporary dependency-backed Node probe later only for behavior PHP cannot prove, such as action click feedback, local intent draft refresh, or copy/execute helpers with explicit browser API stubs.

## Boundary

This slice does not add a runtime JS event test and does not replace existing Playwright/headless Chrome browser smoke tests.

## Verification

- `node --check mtool/scripts/check_no_code_lightweight_dom_tooling.js`
- `make no-code-lightweight-dom-tooling-check`
- `make test`

## Next

#556 should apply the fast contract checklist to the first existing sample conversion before relying on slower headless Chrome smoke.
