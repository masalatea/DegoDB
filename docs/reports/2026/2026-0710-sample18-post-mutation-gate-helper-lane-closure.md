# Sample18 Post Mutation Gate Helper Lane Closure

Date: 2026-07-10
Plan: #600
Status: DONE

## Summary

#600 closes the sample18 non-mutating mutation gate helper lane.

The accepted state is that generated submit can now expose mutation gate readiness metadata, but DBAccess mutation remains disabled and unexecuted.

## Accepted Capability From #599

- The generated submit route returns `mutation_gate` metadata.
- Mutation gate enablement is explicit:
  - app-level `sample18_generated_submit_mutation_enabled`;
  - environment fallback `MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED=1`.
- Default route behavior remains disabled with `enablement_flag_disabled`.
- A healthy flag-on path can report `ready` only as metadata.
- Duplicate idempotency state blocks mutation.
- Audit/idempotency failures fail closed.
- Top-level mutation metadata remains `mutation_enabled=false` and `executed=false`.

## Decision

Promote gate failure matrix coverage before DBAccess mutation dry-run or execution.

Reason:

- The route now exposes `mutation_gate`, so edge outcomes should be locked before any execution path exists.
- Flag-on failures need explicit focused coverage for audit/idempotency unhealthy states.
- Duplicate, skipped, and failed gate outcomes should stay non-mutating and predictable.

## Next

#601 should add focused coverage for flag-on gate failures and duplicate/skipped/failed gate outcomes while keeping DBAccess mutation disabled.

Candidate coverage:

- flag enabled, audit append failed;
- flag enabled, idempotency failed;
- flag enabled, idempotency skipped;
- flag enabled, duplicate idempotency replay;
- invalid or skipped dispatcher path remains blocked and non-executing.

## Verification

- `git diff --check`
