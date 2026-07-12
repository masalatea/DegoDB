# Sample19 material insight preview browser evidence

Date: 2026-07-12

## Summary

#813 adds headless browser evidence for the Sample19 material insight preview route.

The browser smoke runs with Chrome in headless mode, so it does not open a visible macOS window and cannot receive accidental user clicks or keyboard input.

## Implementation

Added `mtool/scripts/check_sample19_material_insight_preview_browser_smoke.js`.

The smoke verifies:

- unauthenticated access redirects to login;
- default-off route returns disabled/not-found behavior with no material insight markers;
- flag-on route renders the read-only preview root;
- Q&A cards, UI outline screens, source/basis markers, no-AI marker, no-mutation marker, and prohibited actions are visible;
- no form/button/script/generated execution controls are present;
- no POST targets the preview route or apply/import/build/publish-like routes;
- disabling the feature flag hides the route again.

## Headless browser evidence

Default-off:

```json
{
  "ok": true,
  "expected_state": "off",
  "unauthenticated_redirected_to_login": true,
  "preview_status": 404,
  "preview_root_count": 0,
  "qa_card_count": 0,
  "ui_screen_count": 0,
  "prohibited_action_count": 0,
  "preview_post_count": 0,
  "action_post_count": 0,
  "request_count": 6
}
```

Flag-on:

```json
{
  "ok": true,
  "expected_state": "enabled",
  "unauthenticated_redirected_to_login": true,
  "preview_status": 200,
  "preview_root_count": 1,
  "qa_card_count": 3,
  "ui_screen_count": 2,
  "prohibited_action_count": 6,
  "preview_post_count": 0,
  "action_post_count": 0,
  "request_count": 6
}
```

Rollback-by-flag:

```json
{
  "ok": true,
  "expected_state": "off",
  "unauthenticated_redirected_to_login": true,
  "preview_status": 404,
  "preview_root_count": 0,
  "qa_card_count": 0,
  "ui_screen_count": 0,
  "prohibited_action_count": 0,
  "preview_post_count": 0,
  "action_post_count": 0,
  "request_count": 6
}
```

## Verification

- `node --check mtool/scripts/check_sample19_material_insight_preview_browser_smoke.js`
- `git diff --check`
- `./sample/tutorials/sample19-json-first-content-model-demo/run.sh`
- `PLAYWRIGHT_CHROME_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' node mtool/scripts/check_sample19_material_insight_preview_browser_smoke.js --expect=off --headless`
- `MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED=1 ./sample/tutorials/sample19-json-first-content-model-demo/run.sh`
- `PLAYWRIGHT_CHROME_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' node mtool/scripts/check_sample19_material_insight_preview_browser_smoke.js --expect=enabled --headless`
- `./sample/tutorials/sample19-json-first-content-model-demo/run.sh`
- `PLAYWRIGHT_CHROME_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' node mtool/scripts/check_sample19_material_insight_preview_browser_smoke.js --expect=off --headless`

## Next lane

#814: close the browser evidence lane and choose Q&A/outline refinement, material insight docs, or the next bounded material-to-UI increment.
