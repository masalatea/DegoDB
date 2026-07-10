# Sample18 Generated-Submit Availability Documentation First Slice

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #681

## Context

#679 fixed route-level coverage for generated-submit `executor_config` metadata. The current availability/config contract is now stable enough to document for sample18 and the broader no-code sample UI conversion lane.

## Changes

- Updated `docs/no-code-ui-testing.md` with the sample18 generated-submit availability boundary:
  - disabled default;
  - mutation and executor enablement flags;
  - app config over env fallback precedence;
  - all-success-or-failure execution policy;
  - `executor_config` metadata fields;
  - injected transaction callable precedence;
  - default runtime reference fail-closed behavior;
  - browser smoke as an outer gate.
- Updated `sample/tutorials/sample18-mini-task-board-demo/README.md` with the sample-facing generated-submit availability/config summary.

## Verification

- `git diff --check`

## Next

Promote #682 as a lane closure to decide whether broader browser smoke, route response/status refinement, or the next sample18 no-code action/input gap should be promoted next.
