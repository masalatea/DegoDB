# Post-Preview Submit Wiring No-Code Product Goal Replan

Date: 2026-07-04
Status: DONE

## Summary

Generated runtime preview submit wiring is in place, but the first verification pass exposed an important smoke gap: current and alias preview pages could render without the injected execution binding and still pass the browser smoke.

Before moving to enabled-action execution or another sample, the next useful slice is to harden the public runtime browser smoke so it checks the delivery split directly.

## Decision

Choose `Public runtime execution binding smoke hardening` as the next work unit.

## Scope

- Artifact-key preview smoke must require no execution binding.
- Current preview smoke must require `/current/execute.json` binding.
- Alias preview smoke must require `/alias/{alias}/execute.json` binding.
- Keep this as verification hardening only; no product behavior change is intended.

Push remains out of scope.
