# Runtime Data Controls Layout Accessibility First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#254 replans after the runtime-data controls closure and chooses layout/accessibility polish before persisted query state or combined-query behavior. #255 implements the first small slice.

The goal is to make the now-dense runtime-data control row easier to identify and test as one read-only data-control group without changing request behavior.

## Planned / Implemented

- Add stable `data-runtime-data-controls` to generated runtime-data controls.
- Add `role="group"` and `aria-label="Runtime data controls"` to the same generated control container.
- Preserve existing `no-code-pagination` class and all existing search/filter/sort/pagination/selection request behavior.
- Extend browser smoke metrics to assert labelled runtime-data control groups.
- Extend PHP generated HTML coverage for the new group semantics.

## Boundary

- In scope: grouping semantics and verification for the existing current/alias runtime-data controls.
- Out of scope: visual redesign, persisted query state, combined query requests, artifact-key preview behavior, and submit/outbox mutation behavior.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11114 assertions`, `1 skipped`)
