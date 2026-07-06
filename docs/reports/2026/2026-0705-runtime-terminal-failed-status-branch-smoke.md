# Runtime Terminal Failed Status Branch Smoke

Status: `DONE`.

Date: 2026-07-05.

## Context

#193 added deterministic browser smoke coverage for the terminal `done` status JSON branch. The next small confidence gap was the terminal failure branch: generated runtime should not retry or process failed work inline, but it should clearly move the user to an operator-review state.

## Implemented

- Extended the no-code runtime preview browser smoke assertions for `--status-probe=stub-failed`.
- Added the `stub-failed` pass to the sample28 public runtime browser smoke.
- Verified that the stubbed status JSON path returns `failed` with handoff state `needs_review`.
- Verified the generated runtime stops after one status JSON check with `data-runtime-outbox-status-poll-state="checked"`.
- Verified the runtime flow moves to `needs_review`: submit is done, track is error, refresh remains ready.
- Verified the visible feedback includes live failed status and operator-review guidance.

## Boundaries

- No retry mutation was added to generated runtime.
- No inline outbox processing was added.
- Real pending/timeout endpoint coverage remains unchanged; this is a deterministic browser-local terminal failure branch proof.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `bash -n mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test`

## Next Candidates

- Closure/report for the live outbox status polling lane.
- Multi-profile reuse of terminal done/failed smoke assertions.
- Manual refresh or demo-processing follow-up if product tryout needs stronger end-to-end visual confirmation.
