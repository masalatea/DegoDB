# Runtime Data Typed Filter Operators Multi Profile Smoke

Date: 2026-07-07

Status: `DONE`

## Summary

#302 chooses multi-profile typed filter operator smoke promotion after the sample28 first slice and closure. #303 verifies it.

## Purpose

The typed filter operator implementation is shared by current/alias runtime-data routes and generated runtime controls. This smoke promotion proves the same behavior outside sample28 without adding more sample-specific code.

## Verification

- Passed:
  - `make sample29-no-code-public-runtime-browser-smoke`
  - `make sample31-no-code-public-runtime-browser-smoke`

The shared browser smoke proved for each profile:

- generated primary/secondary filter operator controls are present;
- default filter operations request `filter_op[field]=contains`;
- initial URL replay can restore explicit `filter_op[field]=eq`;
- browser back/forward preserves runtime-data filter operator state.

## Observed Profile Coverage

- sample29:
  - current/alias initial replay retained `filter_op[status]=contains` and `filter_op[severity]=eq`;
  - direct endpoint smoke covered `current-filter-eq` and invalid operator fail-closed behavior;
  - browser history replay retained `status` plus `severity` filter state.
- sample31:
  - current/alias initial replay retained `filter_op[status]=contains` and `filter_op[quantity_needed]=eq`;
  - direct endpoint smoke covered `current-filter-eq` and invalid operator fail-closed behavior;
  - browser history replay retained `status` plus `quantity_needed` filter state.

## Boundary

- In scope: verification and documentation for sample29/sample31 typed filter operator coverage.
- Out of scope: code changes, new operators, additional filter rows, multi-column sort, broader read-model shape, mutation behavior, artifact-key preview changes, and push.
