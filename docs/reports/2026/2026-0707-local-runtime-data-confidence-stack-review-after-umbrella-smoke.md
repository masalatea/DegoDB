# Local runtime-data confidence stack review after umbrella smoke

Date: 2026-07-07

## Summary

#385 records the local stack boundary after the cross-profile public runtime browser smoke umbrella target.

The branch is 67 commits ahead of `origin/develop`. No push, squash, amend, or history rewrite was performed.

## Review Shape

The stack is reviewable as one runtime-data confidence stack with these groups:

- Runtime-data URL, search, filter, sort, pagination, selected-key, and browser-history replay.
- Dynamic filter and sort row builders, including second/third row retention.
- Sortable table headers and visible sort state.
- Field typing, numeric filtering/sorting, date/time ordering, datetime semantics, and timezone/null policy.
- Type-driven browser operators, value hints, and native typed controls.
- Browser-side inline filter validation and field-aware validation copy.
- Datetime/time native filter smoke coverage.
- Cross-profile public runtime browser smoke umbrella coverage for sample28, sample29, and sample31.

The latest umbrella run also confirmed the intended smoke responsibility split:

- sample29 continues to cover multi-filter retention.
- sample31 covers typed integer validation-copy and invalid fetch prevention.

## Verification Baseline

Latest passed verification from #384:

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make sample-no-code-public-runtime-browser-smoke`

No additional tests were run for #385 because it is docs-only.

## Push Boundary

Push remains pending. Before push, the main decision is whether to push this stack as-is or do a separate commit-history cleanup pass. The stack is coherent enough to review as-is, but it is large enough that a final pre-push review remains useful.
