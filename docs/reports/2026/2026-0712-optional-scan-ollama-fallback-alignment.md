# Optional Scan And Ollama Fallback Alignment

Status: `FIRST_SLICE_DONE`

## Result

Sample19 task packets now include a deterministic advisory scan, and the existing Ollama prototype is an explicit task-bound fallback using the common validator. Neither path can auto-run or become source of truth.

## Deterministic scan

- hash-bound `input/scan.json` is generated with every packet;
- records JSON Pointer, JSON type, object keys/array counts, source hash, and root;
- contains no inferred entities, relationships, schema types, or values;
- declares `authority=advisory` and empty inference;
- source/canonical/output-shape/scan paths and hashes are checked by task/CLI boundaries.

## Ollama fallback

- requires both `--task=<task.json>` and `--execute-local-fallback`;
- without the explicit flag it prints that fallback never auto-runs and exits;
- reads only hash-bound task inputs;
- writes `input/fallback-candidate.json` and `input/fallback-validation.json`, never the agent-owned output candidate;
- calls `app_schema_proposal_task_validate()`, not a provider-specific acceptance path;
- reports advisory/local provider/model and `mutation_performed=false`.

## Verification

- PHP syntax checks passed.
- Full `make test`: 461 tests, 14,111 assertions, 1 skipped.
- No Ollama/Codex/Claude/provider execution occurred.

## Next boundary

#779 is a real interactive-agent workflow proof. The generated TASK.md explicitly requires confirmation in that task interaction; accumulated generic continuation messages do not authorize it. Stop before agent execution until the user starts/approves the concrete generated task.
