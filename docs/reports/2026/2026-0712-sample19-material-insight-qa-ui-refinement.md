# Sample19 Material Insight Q&A/UI Refinement

Date: 2026-07-12

## Summary

This slice refines the fixture-backed Sample19 `material_insight_v0` artifact so the read-only preview carries clearer presentation metadata for AI-readable and human-readable review.

Added:

- Q&A answer categories: `structure`, `relationship`, and `ui_outline`.
- Per-card evidence pointer rendering through stable `data-material-insight-qa-evidence` markers.
- UI outline section grouping: `entity_review` and `qa_review`.
- Validator checks for Q&A categories, missing/invalid evidence pointers, and UI sections.
- PHPUnit marker coverage for the rendered preview.

## Boundary

This remains a fixture-backed read-only refinement. It does not add AI/Ollama calls, mutation, generated execution, import/apply/build/publish behavior, DB/config writes, or any new route action.

Browser smoke was not rerun in this slice because route behavior did not change. The previous headless browser evidence already covers default-off, login redirect, flag-on markers, zero POST, and rollback-by-flag for the preview route.

## Verification

- `php -l mtool/app/material_insight.php`
- `php -l mtool/app/material_insight_preview_page.php`
- `php -l tests/Integration/MaterialInsightTest.php`
- `php -l tests/Integration/MaterialInsightPreviewPageTest.php`
- `git diff --check`
- `make test`

Result:

- 476 tests
- 14,295 assertions
- 1 skipped

## Next

Close this refinement lane and choose the next bounded material-to-UI step: documentation, headless browser re-smoke only if route behavior changes, material-to-UI normalization, or a first generated UI handoff boundary.
