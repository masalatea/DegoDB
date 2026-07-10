# Runtime-data mobile density closure

Date: 2026-07-07

## Summary

#398 closes the generated runtime-data mobile density lane after #397.

The lane is intentionally small: it makes the generated current/alias runtime-data controls fit a narrow mobile viewport and adds smoke coverage that prevents the same layout from silently regressing.

## Accepted capability

- Generated current/alias runtime-data controls stack cleanly at a `390px` viewport.
- Runtime-data label, active-query summary, and control row groups can use full mobile width.
- Query-summary tokens wrap instead of forcing horizontal overflow.
- Public browser smoke captures mobile screenshots and reports `mobileRuntimeDataControls` metrics.
- Sample28, sample29, and sample31 current/alias paths pass with no visible control overflow, no narrow row groups, and no token overflow.
- Artifact-key previews without runtime-data controls are explicitly skipped by this probe.

## Preserved boundary

- Runtime-data URL/query values are unchanged.
- Endpoint parsing and `runtime-data.json` contracts are unchanged.
- Sample data, mutation behavior, sync outbox behavior, and immutable artifact-key preview behavior are unchanged.

## Verification baseline

Verification remains #397:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

The closure itself is docs-only, so no additional code tests were run for #398.

## Remaining candidates

- Local runtime-data stack review after mobile-density closure, before any push decision.
- Broader visual polish can stay separate unless a concrete usability issue appears in tryout.

## Push / history

Push was not performed. History was not rewritten.
