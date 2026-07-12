# AI Task-Packet Workflow Replan

Status: `DONE`

## Decision

The primary workflow is agent-assisted, not provider-API-first.

Mtool creates a self-contained task packet. Codex or Claude reads it, explains the bounded operation, asks the user for confirmation, writes a proposal candidate to the declared output path, and runs one declared validation command. Ollama is an optional no-cost fallback adapter that consumes the same task packet or produces a preliminary scan/candidate for later agent review.

## User experience

The user should need only a short instruction such as:

> Mtoolの未処理タスクを進めて。

The agent then reads the task packet and asks one concrete confirmation naming:

- source files it will read;
- candidate/review files it may write;
- the validation command it will run;
- that DB/config metadata, SQL, import, apply, build, and publish are excluded.

No long provider-specific prompt copy is the primary path. Mtool does not require Codex/Claude API credentials.

## Task packet

One versioned task directory should declare:

- `task.json`: machine-readable task ID, project, source/canonical paths and hashes, allowed outputs, validation command, and mutation prohibitions;
- `TASK.md`: concise Codex/Claude instructions and required confirmation wording;
- source references or bounded copied synthetic source;
- optional deterministic `scan.json`;
- optional fallback `candidate.json` and `validation.json`;
- output location for the agent-produced candidate and validated review artifact.

Information precedence is explicit:

1. source material;
2. canonical Mtool metadata/snapshot;
3. deterministic scan;
4. Ollama or other fallback candidate.

Fallback output is advisory and must never become source of truth merely because it exists.

## One validation pipeline

Agents and adapters must not choose among internal helpers. The public PHP facade will be:

`app_schema_proposal_task_validate(array $taskPacket, string $candidateJson, string $sourceBytes, string $canonicalSnapshotBytes): array`

The public CLI will be:

`php mtool/scripts/validate_schema_proposal_task.php --task=<task.json> --candidate=<candidate.json>`

Internally it must run task/source integrity, decode/structural validation, candidate safety/provenance/evidence/reference checks, independent canonical diff derivation, transparent review-artifact construction, and final exact verification. Results expose a stable stage, errors, candidate/review hashes, and `mutation_performed=false`.

## Provider roles

- Codex/Claude: preferred interactive agents; read task packet, ask confirmation, perform detailed analysis, write candidate, run validation.
- Deterministic scanner: optional cheap structural hints and evidence pointers.
- Ollama: optional fallback scan/candidate adapter; never auto-detected or auto-run, clearly labelled local/optional, and routed through the same validation facade.
- Future paid API adapters: optional and independent; not required for the primary workflow.

Existing prompt/output-shape/validator/diff/review code remains reusable. Existing Ollama runner is retained only as a fallback prototype and must not define the product's primary UX.

## Implementation order

1. #775: task-packet contract and Codex/Claude confirmation/instruction preflight, documentation only.
2. #776: single validation facade and CLI with injected offline fixtures/tests.
3. #777: Sample19 task-packet generator and agent-facing `TASK.md`; no agent or Ollama execution.
4. #778: optional deterministic scan and existing Ollama adapter alignment with the common packet/pipeline.
5. #779: manual Codex/Claude workflow proof, only after the user explicitly starts/approves that task.

## Scope boundary

This replan performs no AI call, no task execution, no DB/config write, and no mutation. The uncommitted provider-first #774 proposal is superseded rather than adopted.
