# Post-Runtime Required Field Guidance No-Code Product Goal Replan

Date: 2026-07-03
Status: DONE

## Summary

Runtime required-field guidance is complete for the first slice. Generated no-code runtime forms now expose required input contract inline with a `Required` badge, a short required-field hint, and `aria-describedby` linkage on rendered required controls.

The next action is not another implementation slice yet. The chosen next step is to close the required-field guidance lane first so the accepted behavior, verification baseline, and remaining options are easy to read before any later validation or execution work begins.

## Decision

Choose `Runtime required field guidance closure` as the next main-plan work unit.

This keeps the current boundary explicit:

- Required fields are now visible before reading draft summary or JSON.
- The runtime preview still builds a local action intent draft only.
- Server mutation and real action execution remain outside this slice.
- Push and history rewrite are still out of scope until explicitly requested.

## Deferred Candidates

- Live invalid/missing-field highlighting while editing the generated runtime form.
- Richer per-field validation text tied to draft checks.
- Server-backed action execution after the non-mutating draft boundary is deliberately crossed.
- Another no-code sample or scenario to exercise a different schema shape.
- Commit grouping and push preparation after the current local stack is accepted.
