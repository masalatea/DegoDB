# Task Review-Artifact Read-Only Route

Status: `FIRST_SLICE_DONE`

## Result

An authenticated default-off parallel Sample19 task-review route now renders validated agent artifacts only after independently rechecking the full integrity chain.

## Route

`GET /projects/SAMPLE19/schema-proposal-tasks/{task_id}/review`

It accepts strict Sample19 task IDs, confines resolution to `work/ai-tasks`, and requires `MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED`. Existing deterministic review remains unchanged.

## Integrity and presentation

The loader verifies task contract, source/canonical/output-shape/scan hashes, validation ready/mutation-false state, candidate/review hashes, source binding, Mtool derivation hashes, and exact independently derived diff. The page labels AI authorship, task/candidate/review hashes, and Mtool diff ownership.

No form, button, script, POST, runtime execution, apply/import action, validation run, provider call, or mutation is exposed.

## Verification

- Full `make test`: 464 tests, 14,133 assertions, 1 skipped.
- No AI/provider execution occurred.

## Next

#782 promotes the concrete task artifact through the real authenticated Sample19 HTTP/browser stack, proves disabled/default-off and POST zero, then decides G-L4 closure.
