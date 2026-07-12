# Schema Proposal Task Validation Facade And CLI

Status: `FIRST_SLICE_DONE`

## Result

All agent and fallback paths now have one public validation facade and one CLI. The pipeline validates task authority and input hashes before candidate parsing, rejects unsafe/non-empty AI diff, derives canonical diff in Mtool, creates a distinct hash-bound review artifact, and never mutates DB/config state.

## Public API

`app_schema_proposal_task_validate($task, $candidateJson, $sourceBytes, $canonicalSnapshotBytes)`

Stable stages are `task_validation`, `input_integrity`, `candidate_decode`, `candidate_validation`, `canonical_diff_derivation`, `review_artifact_validation`, and `review_artifact_ready`.

The CLI is `php mtool/scripts/validate_schema_proposal_task.php --task=<task.json> --candidate=<candidate.json>`. It confines declared files to the task root, verifies the candidate path, writes validation JSON on every pipeline result, and writes the review artifact only on success.

## Safety and ownership

- malformed/unsafe task authority fails before candidate use;
- source/canonical hashes fail before candidate parsing;
- AI provenance and exact source binding are required;
- candidate `canonical_diff` must be empty;
- Mtool derives and exact-verifies review diff;
- review artifact records candidate and canonical snapshot hashes plus derivation version;
- every result exposes `mutation_performed=false`.

## Verification

- PHP syntax checks passed.
- Full `make test`: 458 tests, 14,084 assertions, 1 skipped.
- No AI/provider execution occurred.

## Next

#777 creates the Sample19 task packet and concise agent-facing `TASK.md` around this CLI, without executing an agent or fallback.
