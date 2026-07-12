# Sample19 Agent-Readable Task-Packet Foundation

Status: `FIRST_SLICE_DONE`

## Result

Sample19 can now generate a self-contained hash-bound task packet for Codex/Claude without executing an AI. The packet starts pending confirmation, contains source/canonical/output-shape inputs, and reserves but does not create candidate/validation/review outputs.

## Contents

- machine-authoritative `task.json`;
- agent-readable `TASK.md` with exact confirmation and one validator command;
- copied synthetic source, canonical snapshot, and generic output shape;
- optional advisory scan/fallback declarations;
- strict read/write lists, prohibitions, precedence, and completion fields.

`create_sample19_schema_proposal_task.php` writes a new task root and refuses overwrite. The packet requires confirmation in the active task interaction and explicitly rejects generic earlier continuation as provider/execution authority.

## Verification

- Full `make test`: 460 tests, 14,103 assertions, 1 skipped.
- No candidate, provider call, scan, validation, review artifact, or mutation was produced.

## Next

#778 aligns deterministic scan and Ollama fallback with this common packet/pipeline. Both remain optional/advisory and must never auto-run.
