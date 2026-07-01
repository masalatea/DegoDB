# No-Code Runtime Browser Dispatch Smoke

Status: `FIRST_SLICE_DONE`

Date: 2026-06-30

## Scope

Added the first browser/headless dispatch smoke for generated no-code runtime preview output.

This keeps sample07's generated preview fail-closed without a principal, then probes the authorized browser path in memory to verify that update action intent construction uses generated operation metadata instead of hand-coded screen logic.

## Implementation

- Added action field metadata to `no-code-runtime-v0` rendered actions.
- Embedded generated runtime preview JSON in `runtime-preview.html`.
- Added a minimal browser-side `noCodeRuntimeDispatchAction()` helper that builds `no-code-runtime-action-intent-v0` payloads from generated action fields.
- Extended `check_no_code_runtime_preview_ui_smoke.js` to verify:
  - generated `update_todo_item` operation metadata
  - generated key/input field roles
  - fail-closed disabled dispatch for the principal-free sample07 preview
  - authorized headless-browser dispatch probe mapping `id` to `payload.key` and editable fields to `payload.input`

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample07-no-code-runtime-ui-smoke`

## Next

The no-code runtime MVP first path is now complete through step 8. The next no-code work is `sample28-no-code-data-app-mvp`, a user-facing data-first no-code sample.
