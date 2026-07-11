# Visible Custom Slot Renderer Closure

Date: 2026-07-08

Status: `DONE`

## Summary

#445 closes the visible custom slot renderer lane after #441-#444.

The lane moved custom extension slots from metadata-only declarations to visible, stable, non-executing generated HTML affordances. This is still a generated-runtime boundary, not a custom component runtime or custom operation execution path.

## Accepted Capability

- Generated runtime HTML renders declared extension slots as stable placeholder regions.
- `related_settings_panel` + `link_list` renders internal operator/admin navigation links when declared by metadata.
- `artifact_status_panel` + `status_card` renders read-only status items when declared by metadata.
- `operator_actions_panel` + `action_panel` renders disabled operator action affordances with intent text when declared by metadata.
- The Mtool Source Output review dogfooding probe exercises all three visible slot renderer shapes.

## Preserved Boundary

- Build, publish, approval, and review-request workflows are not executed from generated runtime HTML.
- Custom operation endpoints are not added by this lane.
- Custom React/component execution is not added by this lane.
- Generated runtime artifacts are not hand-edited; renderer behavior comes from normalized metadata.
- Public-preview separation remains intact for normal no-code samples without Mtool-specific extension slots.

## Next Candidates

- Local commit stack review before the next explicit push decision.
- Custom operation manifest inventory for build/publish/review/approval actions.
- Richer custom component adapter handoff, likely React-first, while preserving metadata as the source of truth.
- A second Mtool dogfooding surface if a broader admin/lab replacement probe is promoted.

## Verification

Docs-only closure. Latest code verification remains #444:

- PHP syntax checks
- Focused PHPUnit: `OK (8 tests, 121 assertions)`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11273, Skipped: 1.`
