# Generic optional local-fallback CLI contract / 汎用optional local fallback CLI contract

Date: 2026-07-13

Status: `DONE_GENERIC_CLI_CONTRACT`

## Summary

#879 replaces the Sample19-only local fallback write/validation boundary with a generic task-bound runner and CLI contract.

This still does not make Ollama the primary path, and it does not generalize model/endpoint configuration yet. That remains #880.

## Implemented

Added:

- `mtool/app/task_packet_local_fallback.php`
  - loads a task packet from a task root;
  - validates the current schema-proposal task contract;
  - verifies declared input paths and SHA-256 hashes;
  - requires advisory `optional_inputs.fallback_candidate`;
  - requires advisory `optional_inputs.fallback_validation`;
  - accepts candidate bytes from a provider callback;
  - writes only the declared advisory fallback candidate and fallback validation artifacts;
  - dispatches candidate validation through the existing authoritative schema proposal task validator;
  - returns `mutation_performed=false`.

- `mtool/scripts/run_task_local_fallback.php`
  - generic task-bound local fallback CLI;
  - refuses to run without `--execute-local-fallback`;
  - currently accepts an explicit local candidate JSON file as the provider response boundary;
  - writes fallback artifacts only through the generic runner.

Updated:

- `mtool/app/schema_proposal_task_packet.php`
  - declares `input/fallback-validation.json` as an advisory optional artifact.

- `mtool/scripts/run_sample19_local_ai_proposal.php`
  - remains as a compatibility wrapper;
  - keeps the Sample19/Ollama prompt and endpoint details for now;
  - delegates task loading, hash-bound input reading, fallback artifact writing, and validation to the generic runner.

## Tests

Added:

- `tests/Integration/TaskPacketLocalFallbackTest.php`
  - fake local candidate writes only advisory fallback files;
  - formal agent-owned `output/candidate.json` and `output/review-artifact.json` are not written;
  - task input hash drift fails closed before fallback artifacts are written;
  - generic CLI contains the explicit execution flag gate.

Updated:

- `tests/Integration/SchemaProposalTaskPacketTest.php`
  - Sample19 local Ollama wrapper now proves it delegates to `app_task_packet_local_fallback_run()`.

## Verification

- `php -l mtool/app/task_packet_local_fallback.php`
- `php -l mtool/scripts/run_task_local_fallback.php`
- `php -l mtool/scripts/run_sample19_local_ai_proposal.php`
- `php -l tests/Integration/TaskPacketLocalFallbackTest.php`
- focused `TaskPacketLocalFallbackTest`: 3 tests / 18 assertions
- focused `SchemaProposalTaskPacketTest`: 3 tests / 28 assertions
- `php mtool/scripts/run_task_local_fallback.php`: refuses implicit execution with exit 64
- `make test`: 600 tests / 15193 assertions / skipped 5

## Boundary

This slice is a generic execution boundary, not a generic Ollama adapter.

It proves:

- no implicit fallback run;
- task-bound declared inputs;
- hash-bound source/canonical/shape/scan reads;
- advisory fallback artifact ownership;
- shared validator convergence.

It does not yet prove:

- configurable Ollama endpoint/model/timeout;
- fake transport adapter for normal tests;
- opt-in real local Ollama smoke.

## Next

Proceed to #880: configurable Ollama local adapter.
