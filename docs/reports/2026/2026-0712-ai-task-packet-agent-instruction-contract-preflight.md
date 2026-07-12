# AI Task-Packet And Agent Instruction Contract Preflight

Status: `DONE`

## Result

The provider-neutral task-packet contract is fixed before implementation. Codex/Claude use the packet as an interactive work order; deterministic scan and Ollama may supply advisory inputs but cannot change authority or bypass validation.

## Directory contract

Each task is one immutable-identity directory:

```text
work/ai-tasks/<task-id>/
  task.json
  TASK.md
  input/source.json
  input/canonical-snapshot.json
  input/scan.json                 optional
  input/fallback-candidate.json   optional
  output/candidate.json           agent-writable
  output/validation.json          validator-writable
  output/review-artifact.json     validator-writable on success
```

`task.json` is the machine-readable authority. `TASK.md` explains it but cannot broaden it. Paths are task-root-relative, normalized, and may not escape the task directory.

## `task.json` v0

Required fields:

- `task_version=ai-schema-proposal-task-v0`;
- non-empty `task_id`, `project_key`, and `operation=schema_proposal_candidate`;
- `state=pending_user_confirmation`;
- source/canonical entries with relative path, media type, and SHA-256;
- optional scan/fallback entries, each labelled `authority=advisory` and hash-bound;
- exact candidate, validation, and review-artifact output paths;
- exact validation command with no shell interpolation;
- `allowed_reads` and `allowed_writes` matching the declared files;
- prohibitions for DB/config writes, SQL, import, apply, build, publish, network/provider calls, and execution outside declared validation;
- information precedence: source, canonical, deterministic scan, fallback candidate;
- required confirmation text and completion report fields.

The packet does not contain provider credentials or require a Codex/Claude API.

## Agent interaction

Before writing anything, the agent must read `task.json` and `TASK.md`, verify hashes, and ask one question that names:

- project/task;
- files to read;
- candidate file to write;
- validation command to run;
- the zero-mutation/network boundary.

Only an affirmative response in that task interaction advances the packet to execution. Generic earlier continuation messages do not authorize provider calls, external transmission, or unrelated tasks.

After confirmation the agent may write only `output/candidate.json`, invoke the declared validator, and report stable validation fields. It may correct its candidate based on validation errors, but cannot edit source, canonical context, scan, fallback candidate, task authority, or validator output.

## Validation command

The packet declares exactly:

```bash
php mtool/scripts/validate_schema_proposal_task.php --task=<task.json> --candidate=<candidate.json>
```

The implementation must parse arguments without a shell, resolve all files under task root, verify task/input hashes before candidate parsing, and write stable JSON results. Exit `0` means `review_artifact_ready`; non-zero means no review promotion.

## Stable pipeline stages

1. `task_validation`
2. `input_integrity`
3. `candidate_decode`
4. `candidate_validation`
5. `canonical_diff_derivation`
6. `review_artifact_validation`
7. `review_artifact_ready`

Every result includes `ok`, `stage`, `errors`, `candidate_sha256`, optional `review_artifact_sha256`, and `mutation_performed=false`.

## Candidate and diff ownership

The AI candidate must keep `canonical_diff=[]`; a non-empty value is rejected, not erased. Once the candidate passes, Mtool derives canonical diff and creates a distinct review artifact with derivation metadata and the immutable candidate hash. The existing exact declared-versus-derived verification validates that enriched artifact.

## Fallback boundary

- `scan.json` and `fallback-candidate.json` are optional and advisory.
- Their absence never blocks Codex/Claude.
- Their presence never authorizes use of Ollama or any provider.
- Ollama execution remains an explicit, separate Mtool/user action and later feeds the same candidate validator.

## Next

#776 implements the pure task-validation facade and CLI with temporary injected task directories. It adds no UI, task generator, agent instruction file, scan, provider adapter, or AI execution.
