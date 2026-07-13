# Optional scan / Ollama fallback generalization preflight

Date: 2026-07-13

Status: `DONE_PREFLIGHT`

## Summary

#877 inventories the existing Sample19 task-packet / deterministic scan / local Ollama fallback work and fixes the boundary for generalization.

This lane is independent from SQLite-to-MySQL promotion. It must not turn Ollama into the primary path. Codex/Claude-style task-packet execution remains the normal workflow; Ollama remains an optional local fallback that can produce advisory input only.

## Current reusable parts

Reusable without major redesign:

- `app_schema_proposal_task_validate()`
  - shared authoritative validator;
  - creates Mtool-derived review artifact;
  - records `mutation_performed=false`;
  - refuses task/candidate/source/canonical mismatches.
- `validate_schema_proposal_task.php`
  - task-bound validator CLI;
  - writes only declared validation/review artifacts.
- task packet authority model:
  - source of truth;
  - canonical comparison context;
  - output shape contract;
  - advisory deterministic scan;
  - optional fallback candidate.
- confirmation/prohibition model:
  - requires user confirmation inside the concrete task;
  - prohibits DB/config writes, SQL, import, apply, build, publish, and network from the agent task.
- deterministic scan idea:
  - hash-bound pointer/type facts;
  - no inferred schema;
  - no values as acceptance authority;
  - advisory only.
- fallback ownership rule:
  - local fallback writes `input/fallback-candidate.json`;
  - it does not write the formal `output/candidate.json`;
  - Codex/Claude/user may inspect fallback output and create a formal candidate separately.

## Sample19 coupling to remove

The current first slice is intentionally Sample19-shaped:

- function name: `app_schema_proposal_sample19_task_packet()`;
- task ID prefix: `sample19-schema-proposal-...`;
- project key fixed to `SAMPLE19`;
- source root fixed to `/article`;
- deterministic scan requires `source.article`;
- prompt template path fixed to Sample19;
- local fallback script name fixed to `run_sample19_local_ai_proposal.php`;
- Ollama model and endpoint are hard-coded:
  - `qwen2.5-coder:7b`;
  - `http://127.0.0.1:11434/api/generate`;
- operation is fixed to `schema_proposal_candidate`;
- validator currently knows the schema-proposal candidate shape specifically.

These are acceptable in the proven first slice, but they must not become the generic contract.

## Generic contract decision

Use a small provider-neutral task packet foundation, not a new agent framework.

The generic layer should define:

- task identity and root;
- operation key;
- input declarations with relative path, media type, sha256, and authority;
- optional advisory inputs;
- precedence;
- output declarations;
- allowed read/write sets;
- validation command and success stage;
- confirmation prompt;
- prohibitions;
- completion report fields.

The schema-proposal task can then become one concrete operation profile on top of the generic packet shape.

Do not generalize acceptance semantics too far. Each operation still needs an authoritative validator. The generic layer can validate packet integrity and authority boundaries; it cannot decide whether a candidate is semantically correct for every future operation.

## First code slice selected (#878)

#878 should add a generic deterministic scan contract first, before touching Ollama execution.

Why first:

- deterministic scan is local, cheap, and mutation-free;
- it improves Codex/Claude and Ollama paths equally;
- it has no provider dependency;
- it is the least risky part to lift out of Sample19.

Proposed first slice:

- add a generic scan module, tentatively `mtool/app/task_packet_scan.php`;
- support JSON source bytes with configurable root pointer;
- output a versioned scan artifact, e.g. `mtool-deterministic-source-scan-v1`;
- include:
  - source sha256;
  - root pointer;
  - pointer;
  - JSON type;
  - object keys or array count;
  - no inferred entity/schema/relationship claims;
  - `authority=advisory`;
  - `mutation_performed=false`;
- keep Sample19 behavior compatible by making `/article` a caller-selected root pointer;
- add focused tests proving deterministic output, hash binding, root pointer behavior, invalid JSON failure, and no inferred fields.

## Later slices

#879 generic local-fallback CLI:

- read a declared task packet;
- refuse to execute without explicit flag;
- read only hash-bound inputs;
- write only declared advisory fallback artifacts;
- not know about Sample19 paths.

#880 configurable Ollama adapter:

- endpoint/model/timeout/context are explicit config;
- local default only;
- no credential requirement;
- no paid-provider call;
- fake transport for normal tests;
- opt-in real local smoke only.

#881 shared validation integration:

- fallback and primary candidates converge on one authoritative validator;
- authority labels and artifact ownership remain separate.

#882 workspace handoff:

- document paths, confirmation wording, AI review flow, and promotion boundary.

#883 qualification:

- normal tests require no Ollama;
- optional real Ollama smoke proves one local flow;
- Sample19 compatibility evidence remains.

## Non-goals

- Automatically launching Ollama.
- Treating scan or fallback output as source of truth.
- Accepting a fallback response without the operation validator.
- Requiring Ollama/model downloads for normal tests.
- Sending project content to external paid providers.
- Making Mtool an autonomous agent framework.
- Adding apply/import/build/publish authority to task packets.

## Blockers

No feasibility blocker remains for #878.

Known design constraints:

- generic packet integrity can be shared, but candidate semantic validation remains operation-specific;
- fallback execution must remain opt-in and advisory;
- local model availability cannot be required in CI/default tests;
- production or personal data requires separate redaction/approval policy from `security-and-data-handling.md`.

## Verification

This preflight is docs-only. No product code changed.

Existing evidence referenced:

- `docs/ai-task-packet-workflow.md`
- `docs/reports/2026/2026-0712-optional-scan-ollama-fallback-alignment.md`
- `mtool/app/schema_proposal_task_packet.php`
- `mtool/scripts/run_sample19_local_ai_proposal.php`
- `mtool/scripts/validate_schema_proposal_task.php`
