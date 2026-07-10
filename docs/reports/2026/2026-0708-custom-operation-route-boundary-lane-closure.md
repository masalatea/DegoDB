# Custom Operation Route Boundary Lane Closure

Date: 2026-07-08

Status: `DONE`

## Summary

#464 closes the custom operation route-boundary metadata lane.

The lane now covers the two Mtool dogfooding operations that expose side-effect intent without execution:

- `review_source_output_artifact`
- `request_source_output_publish`

Both operations have inventory docs, structured route-boundary metadata, screen/runtime carry-through, React bridge handoff carry-through, disabled operator action UI wording, and integration coverage.

## Accepted Capability

- No-code custom operation metadata can describe operation identity, target, side-effect class, availability, policy, CSRF requirement, audit event, adapter handoff, route boundary, intent, and unavailable reason.
- `review_source_output_artifact` carries a POST route boundary for the future artifact review workflow.
- `request_source_output_publish` carries a POST route boundary for the future publish approval request workflow.
- Generated runtime HTML can display route-boundary readiness while keeping the action button disabled.
- React bridge handoffs can expose the same metadata for future adapter code.
- Dogfooding inspection can detect route-boundary presence.

## Still Out Of Scope

- POST route implementation.
- Operation dispatch.
- Approval transition mutation.
- Publish candidate creation or promotion.
- Review workflow creation.
- Build output publishing.
- Custom component execution.

## Decision

Do not enable execution directly from this lane.

The next step should be a separate execution preflight slice that defines the shared dispatch boundary for custom operations before implementing any concrete POST route. The preflight should choose one narrow first route, most likely `review_source_output_artifact`, and make policy, CSRF, audit, stale-artifact, duplicate handling, and disabled-state behavior testable before mutation is allowed.

## Verification Baseline

Latest code verification for this lane is #463:

- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- Focused PHPUnit: `OK (8 tests, 170 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11324, Skipped: 1.`
- `git diff --check`

#464 itself is docs-only and uses `git diff --check`.
