# Post Route Response Contract Lane Closure

Date: 2026-07-10
Status: DONE
Plan: #685

## Context

#684 added durable response contract documentation and focused route assertions for sample18 generated-submit outcomes. The route now has a compact status/result/failure/recovery contract in `docs/no-code-ui-testing.md`, plus PHPUnit coverage across invalid, blocked, duplicate, failed, recovery-required, and executed outcomes.

## Acceptance

Accepted #684 as the response contract slice:

- invalid outcomes keep stable HTTP/status/failure semantics;
- blocked and duplicate outcomes remain HTTP 409 non-execution responses;
- pre-execution config/dependency failures remain HTTP 500 without recovery-required metadata;
- DBAccess rollback failure remains non-recovery HTTP 500;
- commit-status-unknown and post-commit recording failures expose `recovery_required=true`;
- executed success remains HTTP 200 with `result=executed` and all required recording metadata.

## Next Decision

Promote sample18 generated action/input gap inventory as #686.

Reasoning:

- The route and runtime response surfaces are now sufficiently stable.
- Broader browser smoke is still useful, but the next product gap is whether generated no-code action/input drafts line up cleanly with the executable generated-submit route.
- A lightweight inventory can define the missing UI/metadata/input handoff work before another implementation slice.

## Next

#686 should inventory the remaining gap between generated action metadata/input drafts and executable sample18 generated-submit route expectations.
