# Sample18 Route Executor Config Metadata Coverage

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #679

## Context

#677 added `executor_config` metadata to generated-submit route responses. The resolver itself had focused helper coverage, but route-level response assertions were still needed so UI/runtime/documentation can rely on stable payload fields.

## Changes

- Extended generated-submit route coverage for default disabled responses:
  - `executor_config.status=disabled`
  - default app/env sources
  - default runtime dependency source
  - disabled reasons.
- Added route-level env fallback coverage where mutation enablement comes from `MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED=1` and executor remains disabled.
- Extended app-level metadata-only route coverage where mutation is app-enabled and executor remains default-disabled.
- Extended injected-callable execution coverage for ready `executor_config` with dependency source `injected_transaction_callables`.
- Extended missing default runtime reference coverage for failed `executor_config` metadata and route execution reasons.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample18-mini-task-board-demo/compose.yaml --run-script=./sample/tutorials/sample18-mini-task-board-demo/run.sh --phpunit-target=/var/www/tests/Integration/Sample18MiniTaskBoardDemoTest.php`
  - OK (28 tests, 1465 assertions)

## Next

Promote #680 as a lane closure to decide whether to document sample18 generated-submit availability, add browser smoke coverage, or refine route response/status docs next.
