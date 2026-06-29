# 2026-06-29 No-Code Runtime First Slice

## Status

- implementation status: `FIRST_SLICE_IMPLEMENTED`
- current-plan link: `docs/current-plans.md` order 1, No-code screen definition and runtime MVP
- full runtime MVP status: `IN_PROGRESS`

## Purpose

After `no-code-screen-definition-v0`, the next step is a minimal runtime boundary that consumes generated screen definitions.

This slice intentionally avoids a full UI framework. It adds a small runtime adapter that turns generated definitions into render models and routes enabled actions through a dispatcher intent.

## Implemented Scope

- Added `mtool/app/no_code_runtime.php`.
- Added `no-code-runtime-v0`.
- Added screen lookup and render model generation for:
  - list screen.
  - detail screen.
  - form screen.
- Added render output for:
  - fields.
  - action enabled/disabled state.
  - row / item display values.
  - sync status hint.
- Added action dispatch boundary:
  - disabled actions fail closed and are not dispatched.
  - missing required action input fails closed.
  - enabled actions create `no-code-runtime-action-intent-v0`.
  - dispatcher receives the generated operation intent instead of hand-coded screen logic.
- Added `NoCodeRuntimeTest`.

## Boundary

This is still not the full no-code runtime MVP.

The slice creates a runtime render model and dispatch intent, but it does not yet produce a browser bundle, render actual HTML screens, or persist a create/update flow end to end through generated DBAccess.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml --run-script=sample/tutorials/sample07-dbaccess-crud-basic/run.sh --phpunit-target=/var/www/tests/Integration/NoCodeRuntimeTest.php`
  - `OK (4 tests, 37 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 290, Assertions: 9728, Skipped: 1.`

## Result

Generated no-code screen definitions now have a first runtime consumer.

The next useful slice is to connect this runtime adapter to a generated/published artifact or sample path, then execute one persisted operation through the existing managed operation executor boundary.
