# Sample19 material insight preview route

Date: 2026-07-12

## Summary

#811 adds the default-off authenticated Sample19 material insight preview route first slice.

The route renders a validated `material_insight_v0` artifact as source/basis evidence, bounded Q&A cards, read-only UI outline screens, and prohibited actions.

## Implementation

Added `mtool/app/material_insight_preview_page.php` with:

- `MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED`;
- fixture loader over the fixed Sample19 source/proposal/canonical files;
- `material_insight_v0` build and validation before rendering;
- read-only HTML markers for source, basis, Q&A cards, UI outline, and prohibited actions;
- fail-closed error rendering.

Integrated:

- route: `GET /projects/SAMPLE19/material-insight`;
- route name: `project_sample19_material_insight_preview`;
- auth requirement;
- HTTP dispatcher;
- default-off compose environment pass-through.

Added `tests/Integration/MaterialInsightPreviewPageTest.php` covering:

- default-off/truthy feature flag behavior;
- route identity and authentication requirement;
- successful fixture render markers;
- absence of form/button/script/POST/generated execution controls;
- loader fail-closed behavior for missing/invalid fixtures;
- compose environment pass-through.

## Verification

- `php -l mtool/app/material_insight_preview_page.php`
- `php -l tests/Integration/MaterialInsightPreviewPageTest.php`
- `php -l mtool/app/http.php`
- `php -l mtool/app/router.php`
- `git diff --check`
- `make test`
  - 476 tests
  - 14,283 assertions
  - 1 skipped

## Scope boundary

This slice still does not add:

- AI provider or Ollama calls;
- generated submit/action execution;
- DB/config metadata mutation;
- import/apply/build/publish;
- browser smoke evidence.

## Next lane

#812: close the preview route slice and decide whether browser evidence is required before the next material-to-UI increment.

