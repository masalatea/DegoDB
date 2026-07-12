# Sample19 Generated Handoff Preview Browser Evidence

Date: 2026-07-12

## Summary

Added and ran a headless browser smoke for the default-off authenticated Sample19 generated handoff inspection route:

- `mtool/scripts/check_sample19_generated_handoff_preview_browser_smoke.js`

The smoke checks:

- unauthenticated request redirects to login
- flag-off route returns 404 and renders no generated handoff markers
- flag-on route renders generated no-code handoff markers
- no form/button/script/generated execution controls render
- no POST requests are made
- rollback-by-flag returns to off behavior

The browser was run headless. No visible Chrome window was opened.

## Verification commands

- `node --check mtool/scripts/check_sample19_generated_handoff_preview_browser_smoke.js`
- `./sample/tutorials/sample19-json-first-content-model-demo/run.sh up`
- `./sample/tutorials/sample19-json-first-content-model-demo/run.sh apply-seed`
- `PLAYWRIGHT_CHROME_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' node mtool/scripts/check_sample19_generated_handoff_preview_browser_smoke.js --expect=off --headless`
- `MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED=1 ./sample/tutorials/sample19-json-first-content-model-demo/run.sh up`
- `PLAYWRIGHT_CHROME_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' node mtool/scripts/check_sample19_generated_handoff_preview_browser_smoke.js --expect=enabled --headless`
- `./sample/tutorials/sample19-json-first-content-model-demo/run.sh up`
- `PLAYWRIGHT_CHROME_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' node mtool/scripts/check_sample19_generated_handoff_preview_browser_smoke.js --expect=off --headless`
- `./sample/tutorials/sample19-json-first-content-model-demo/run.sh down`

## Results

Default off:

```json
{
  "ok": true,
  "expected_state": "off",
  "unauthenticated_redirected_to_login": true,
  "handoff_status": 404,
  "handoff_root_count": 0,
  "screen_definition_version_count": 0,
  "runtime_version_count": 0,
  "handoff_screen_count": 0,
  "zero_action_marker_count": 0,
  "zero_custom_operation_marker_count": 0,
  "handoff_post_count": 0,
  "action_post_count": 0,
  "request_count": 6
}
```

Enabled:

```json
{
  "ok": true,
  "expected_state": "enabled",
  "unauthenticated_redirected_to_login": true,
  "handoff_status": 200,
  "handoff_root_count": 1,
  "screen_definition_version_count": 1,
  "runtime_version_count": 1,
  "handoff_screen_count": 2,
  "zero_action_marker_count": 1,
  "zero_custom_operation_marker_count": 1,
  "handoff_post_count": 0,
  "action_post_count": 0,
  "request_count": 6
}
```

Rollback/off:

```json
{
  "ok": true,
  "expected_state": "off",
  "unauthenticated_redirected_to_login": true,
  "handoff_status": 404,
  "handoff_root_count": 0,
  "screen_definition_version_count": 0,
  "runtime_version_count": 0,
  "handoff_screen_count": 0,
  "zero_action_marker_count": 0,
  "zero_custom_operation_marker_count": 0,
  "handoff_post_count": 0,
  "action_post_count": 0,
  "request_count": 6
}
```

## Boundary

This evidence does not add AI/Ollama calls, DB/config writes, import/apply/build/publish behavior, mutation, generated submit controls, or generated execution.

Future browser checks should continue to run headless by default.
