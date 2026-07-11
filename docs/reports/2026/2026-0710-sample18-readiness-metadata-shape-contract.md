# Sample18 Readiness Metadata Shape Contract

Date: 2026-07-10

Status: `DONE`

## Summary

#703 fixes the read-only readiness metadata shape before implementing the snapshot helper. The contract is now documented and represented in the sample18 fast contract checklist fixture, with PHPUnit assertions that keep the shape stable.

## Contract

The readiness snapshot is versioned as `sample18-generated-submit-readiness-v0` and remains read-only:

- `read_only=true`
- `mutation_dispatch_allowed=false`
- no generated-submit request while building the snapshot

The snapshot shape is:

- `snapshot_version`
- `read_only`
- `mutation_dispatch_allowed`
- `executor_config`
- `action_readiness`

The action readiness fields are:

- `action_key`
- `operation_key`
- `route_compatible`
- `readiness_state`
- `availability_candidate`
- `can_submit`
- `failure_reasons`
- `executor_config_status`

Route-compatible operations are `create_task_card`, `update_task_card`, and `complete_task_card`. `reopen_task_card` and `delete_task_card` remain non-ready with `operation_not_route_compatible`.

## Changes

- Added `readiness_metadata_contract` to `sample18` fast contract checklist fixture.
- Documented the readiness snapshot shape in `docs/no-code-ui-testing.md`.
- Added PHPUnit fixture-shape assertions in `Sample18MiniTaskBoardDemoTest`.
- Promoted #704 as the next active helper implementation slice.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - `OK (29 tests, 1719 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 413, Assertions: 13574, Skipped: 1.`
- `git diff --check`
