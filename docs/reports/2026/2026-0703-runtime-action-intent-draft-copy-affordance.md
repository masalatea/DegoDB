# 2026-0703 Runtime Action Intent Draft Copy Affordance

Status: `FIRST_SLICE_DONE`

## Summary

Adds a copy affordance to generated no-code runtime `Action Intent Draft` panels.

The local draft JSON is useful for tryout notes, debugging, and future handoff into managed operation work. This slice keeps the draft read-only and browser-side, but makes the currently displayed JSON easier to carry out of the preview.

## Changes

- Adds `Copy draft JSON` to each generated draft panel.
- Adds a visible live status for copy readiness, success, and fallback messages.
- Copies the currently displayed draft JSON, including edits made in the form.
- Keeps the non-mutating preview boundary unchanged: copying the local draft does not execute a server update and does not bypass disabled action policy.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- Focused `NoCodeRuntimeTest`: `8 tests, 127 assertions`
- `make sample28-no-code-runtime-ui-smoke`: passed and confirmed `Draft JSON copied.` plus copied draft text matches the edited draft
- Full `make test`: `327 tests, 10824 assertions, skipped 1`
- `git diff --check`: passed

Push was not performed for this slice.
