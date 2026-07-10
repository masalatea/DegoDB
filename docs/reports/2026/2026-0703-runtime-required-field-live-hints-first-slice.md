# Runtime Required Field Live Hints First Slice

Date: 2026-07-03
Status: FIRST_SLICE_DONE

## Summary

Generated runtime form required hints now update as the local action-intent draft changes. A required field with a present value is marked as present, and a blank required value is marked as missing.

This remains browser-local feedback. The runtime preview still does not execute a server update or bypass disabled policy checks.

## Implementation Notes

- Required hint elements now carry `data-required-state`.
- `writeRequiredFieldHints()` updates required hints from the existing `draft_checks`.
- Missing key/input/filter required checks map to the matching form hint when the field is rendered.
- The sample28 browser smoke now verifies `ok` after editing the required input and `missing` after blanking it.

## Verification

- Focused `NoCodeRuntimeTest`: `8 tests, 148 assertions`.
- `make sample28-no-code-runtime-ui-smoke`: passed.
- Full `make test`: passed after resetting the sample01 stack to remove stale lab DB state, `327 tests, 10845 assertions, skipped 1`.

The first full `make test` attempt hit stale sample01 lab DB state (`external_article`) and failed in external/lab ingress tests. Resetting `sample01-simple-table-runtime` and rerunning passed.

Push was not performed for this slice.
