# AI Schema Proposal Handoff Guide / AI schema proposal handoff guide

English companion:
Use this guide when Codex, Claude, or another coding agent receives an Mtool schema-proposal task packet and must decide how to use optional deterministic scan or local fallback artifacts.

この文書は、Codex / Claude / coding agent が Mtool の schema proposal task packet を受け取り、optional scan や local fallback artifact をどう扱うか迷わないための作業 guide です。

## Standard task root

Task roots are disposable local workspace artifacts. They are not the accepted Mtool metadata.

```text
work/ai-tasks/<task-id>/
  task.json                         machine-readable authority
  TASK.md                           human-readable task instruction
  input/source.json                 source of truth
  input/canonical-snapshot.json     current comparison context
  input/output-shape.json           candidate output contract
  input/scan.json                   advisory deterministic pointer/type scan
  input/fallback-candidate.json     optional advisory draft
  input/fallback-validation.json    optional advisory validation result
  output/candidate.json             formal agent-owned candidate
  output/validation.json            formal validation result
  output/review-artifact.json       Mtool-derived review artifact
```

If paths in `TASK.md` and `task.json` disagree, `task.json` wins.

## Authority order

1. `source.json`: source of truth.
2. `canonical-snapshot.json`: comparison context.
3. `output-shape.json`: required output contract.
4. `scan.json`: advisory only.
5. `fallback-candidate.json`: advisory draft only.

Fallback output can help an AI draft faster, but it is not accepted output.

## Confirmation wording

Before writing anything, the agent should summarize the declared reads, writes, validator, and prohibitions, then ask the exact task-specific confirmation from `task.json`.

Safe wording:

> I will read only the declared source/canonical/shape/scan/fallback files, write only `output/candidate.json`, run the declared validator, and perform no DB/config/SQL/import/apply/build/publish/network operation. Proceed with this specific task?

Generic earlier messages such as “continue” do not authorize a newly generated task packet.

## Formal candidate path

The formal path is:

1. Read `task.json` first.
2. Optionally read `TASK.md` as a companion.
3. Ask for confirmation.
4. Write exactly one candidate object to `output/candidate.json`.
5. Keep `canonical_diff=[]`; Mtool derives the review diff.
6. Run the declared validator:

```bash
php mtool/scripts/validate_schema_proposal_task.php \
  --task=work/ai-tasks/<task-id>/task.json \
  --candidate=work/ai-tasks/<task-id>/output/candidate.json
```

Success means `review_artifact_ready`, not apply/import/build/publish.

## Optional fallback path

The fallback path is separate:

```bash
php mtool/scripts/run_sample19_local_ai_proposal.php \
  --task=work/ai-tasks/<task-id>/task.json \
  --execute-local-fallback
```

or, for a local candidate file:

```bash
php mtool/scripts/run_task_local_fallback.php \
  --task=work/ai-tasks/<task-id>/task.json \
  --candidate-json=/path/to/candidate.json \
  --execute-local-fallback
```

Fallback writes only advisory artifacts under `input/`.

It must not write:

- `output/candidate.json`
- `output/validation.json`
- `output/review-artifact.json`

If fallback looks useful, the agent still reviews it against source/canonical/shape and then copies or adapts it into `output/candidate.json`. After that, run the formal validator again.

## Result metadata to check

Validation results expose:

- `mutation_performed=false`
- `validation_pipeline.validator=app_schema_proposal_task_validate`
- formal path: `validation_pipeline.advisory=false`
- fallback path: `validation_pipeline.advisory=true`

If a fallback result says `ok=true`, treat it as “draft validated as advisory,” not “accepted.”

## Non-goals

This guide does not enable automatic schema application, metadata import, SQL execution, build, publish, or remote paid-provider calls.

The AI handoff ends at a validated review artifact.

## Related

- [AI Task-Packet Workflow](ai-task-packet-workflow.md)
- [JSON To DB Entrance](json-to-db-entrance.md)
- [Security And Data Handling](security-and-data-handling.md)
