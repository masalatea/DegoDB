# Sample19 material insight browser evidence lane closure

Date: 2026-07-12

## Summary

#814 closes the Sample19 material insight browser evidence lane.

The first material-to-UI slice now has:

- fixture-backed `material_insight_v0` builder and validator;
- read-only authenticated preview route;
- fast PHP marker tests;
- full-suite evidence;
- headless browser evidence for default-off, login redirect, flag-on render, zero POST, and rollback-by-flag.

## Headless browser policy

Future browser evidence for this lane should run headless by default.

Do not use `--headed` unless the user explicitly asks to see the browser. Visible Chrome windows can receive accidental clicks or keyboard input, which is not acceptable for unattended smoke verification.

Recommended pattern:

```sh
PLAYWRIGHT_CHROME_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' \
  node mtool/scripts/check_sample19_material_insight_preview_browser_smoke.js --expect=enabled --headless
```

## Decision

Promote one small Q&A/UI outline refinement preflight next.

The current preview proves the route and safety boundary. The next useful product increment is not another infrastructure proof; it is making the material insight preview more understandable while staying fixture-backed and non-mutating.

## Next lane

#815: Sample19 material insight Q&A/UI outline refinement preflight.

The preflight should define small, testable improvements such as:

- clearer answer-card categories;
- explicit source evidence pointers per answer;
- UI outline sections grouped by entity/list/detail/Q&A purpose;
- stable markers for refined sections;
- no AI call, mutation, import, build, publish, or generated execution.

