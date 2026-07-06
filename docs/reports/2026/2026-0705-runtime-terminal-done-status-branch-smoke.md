# Runtime Terminal Done Status Branch Smoke

Date: 2026-07-05

## Status

Done locally. Not pushed.

## Context

The runtime live status lane already covered the real async pending path and bounded timeout guidance. The remaining UI branch was terminal `done`, which is harder to make deterministic through the real public runtime endpoint without changing container environment or processing behavior.

## Implemented Slice

- Extended `check_no_code_runtime_preview_ui_smoke.js` with `--status-probe=real|stub-done|stub-failed`.
- Made the existing fetch-stub submit probe return a sync outbox item so runtime status polling can run.
- Added assertions for the `stub-done` status JSON branch:
  - one status JSON GET,
  - `done` / `complete` status payload,
  - complete runtime flow,
  - terminal done guidance in status and feedback text.
- Added a current-preview stub-done pass to `check_sample28_no_code_public_runtime_browser_smoke.sh`.

## Boundary Notes

- Real current/alias endpoint smoke remains unchanged for pending/timeout behavior.
- The terminal `done` branch is deterministic and browser-local.
- No inline processing, retry mutation, or server behavior change was added.

## Next Candidates

- Add a deterministic `failed` / `needs_review` branch smoke.
- Add demo-processing terminal done smoke if the sample wrapper gets an explicit runtime SQLite environment mode.
- Revisit whether terminal done should auto-refresh data or keep manual Refresh preview only.

