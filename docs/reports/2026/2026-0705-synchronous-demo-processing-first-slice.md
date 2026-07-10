# Synchronous Demo Processing First Slice / synchronous demo processing first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-05

## Summary

#180 adds a fail-closed synchronous demo processing path for no-code public runtime submit.

The production-oriented default remains asynchronous: runtime submit enqueues a managed-operation sync intent, shows outbox handoff, and expects the operator/worker path to process it. The new demo path is only advertised to generated runtime HTML when both conditions are true:

- `MTOOL_NO_CODE_RUNTIME_SYNC_DEMO` is truthy.
- `MTOOL_RUNTIME_SQLITE_PATH` is set.

When that binding is available, generated runtime submit adds `runtime_demo_process=1`. The public execution endpoint still requires the explicit POST flag before it attempts to process one pending outbox item through the existing server DBAccess outbox processor.

## Implemented

- Added `demo_processing: available` to the public runtime execution binding only when the demo env gate and SQLite path are present.
- Added explicit POST detection for `runtime_demo_process`.
- Added endpoint-side synchronous demo processing that reuses the existing managed-operation sync outbox processor and generated server DBAccess binding.
- Added fail-closed outcomes for disabled demo mode, missing operation key, operation lookup failure, runtime entity materialization failure, and binding failure.
- Updated generated runtime submit to send `runtime_demo_process=1` only when the binding says demo processing is available.
- Updated runtime success feedback so a demo-processed item can be described separately from normal async accepted handoff.
- Extended browser smoke probing so demo binding availability and submit flag behavior can be asserted without changing the default smoke behavior.

## Boundary

- This does not make synchronous processing the production default.
- This does not remove the async outbox model.
- This does not introduce a long-running worker or polling loop.
- This first slice keeps normal sample28/sample29 public runtime smoke on the async path, proving the new gate does not change the default behavior.

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make test`

Latest full test result:

- `Tests: 334, Assertions: 10989, Skipped: 1`

Push was not performed.
