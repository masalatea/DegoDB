# Runtime Required Field Live Hints Closure

Date: 2026-07-03
Status: FIRST_SLICE_DONE

## Summary

The runtime required-field live hints lane is closed for the current no-code first slice. Required generated form fields now provide both static guidance and browser-local live feedback: present values are marked as present, and blank required values are marked as missing.

This remains intentionally local preview behavior. The generated runtime preview still builds and explains an action-intent draft without performing server mutation.

## Accepted Capability

- Required generated form fields keep the compact `Required` badge and hint.
- Required hints carry `data-required-state`.
- Required hint text updates from existing `draft_checks`.
- Missing key, input, and filter checks map to the matching rendered form hint.
- Browser smoke verifies the hint reaches `ok` after edit and `missing` after blank input.
- Blank required action input still fails closed in browser dispatch.

## Verification Baseline

- Focused `NoCodeRuntimeTest`: `8 tests, 148 assertions`.
- `make sample28-no-code-runtime-ui-smoke`: passed.
- Full `make test`: passed after resetting the sample01 stack, `327 tests, 10845 assertions, skipped 1`.

The first full `make test` attempt hit stale sample01 lab DB state (`external_article`) and failed in external/lab ingress tests. Resetting `sample01-simple-table-runtime` and rerunning passed.

## Remaining Candidates

- Server-backed no-code action execution after the non-mutating preview boundary is deliberately crossed.
- Richer per-field validation messages beyond present/missing required state.
- Another no-code scenario or sample that exercises a different action/schema shape.
- Commit grouping and push preparation after the local stack is accepted.

Push was not performed for this slice.
