# AI-Facing Plugin Interface / AI向け plugin interface

English companion:
This document defines the first AI-facing plugin interface for DegoDB/Mtool. An AI-facing plugin is a packaged instruction, schema, task-packet, and handoff surface for Codex / Claude style agents. It is not an executable code plugin, runtime hook, dependency installer, or app generator.

この文書は DegoDB/Mtool の最初の AI-facing plugin interface を定義します。AI-facing plugin は Codex / Claude などの agent が読む instruction、schema、task packet、handoff surface をまとめた package です。実行 code plugin、runtime hook、dependency installer、app generator ではありません。

## Position / 位置づけ

AI-facing plugins exist to make domain-specific development easier without making Mtool silently own downstream implementation.

They package:

- domain vocabulary and model boundaries;
- JSON schemas and example packets;
- task instructions for Codex / Claude style agents;
- confirmation wording and prohibited actions;
- validation commands and expected review artifacts;
- handoff notes for external code/runtime owners.

They do not package:

- executable generator hooks;
- PHP/Node/Python runtime plugins;
- dependency installation;
- native app or game-engine project creation;
- store submission, signing, deployment, or production hosting;
- autonomous AI provider calls.

## Plugin roots / plugin 置き場

Use this root for AI-facing plugins:

```text
mtool/plugins/ai/
  README.md
  <plugin-id>/
    plugin.json
    README.md
    schemas/
    packets/
    examples/
    validators/
    handoff/
```

The root is intentionally separate from future code-facing plugins. A future code-facing plugin root may exist later, but it must not reuse this interface without a new compatibility decision.

## Minimum manifest / 最小 manifest

Every AI-facing plugin has a `plugin.json` manifest.

```json
{
  "schema_version": "mtool-ai-plugin-v1",
  "id": "domain.game-content",
  "name": "Game Content AI Plugin",
  "kind": "ai_facing",
  "interfaces": [
    "task_packet",
    "schema",
    "example",
    "validator_contract",
    "handoff"
  ],
  "runtime_execution": false,
  "generator_hooks": false,
  "default_task_packet": "packets/ai-game-content-task.json",
  "human_task": "packets/AI-GAME-CONTENT-TASK.md",
  "validation": {
    "required": true,
    "implemented": true,
    "command": "php mtool/scripts/validate_ai_plugin_packet.php --plugin=mtool/plugins/ai/domain.game-content/plugin.json --task=<task.json> --candidate=<candidate.json>"
  },
  "non_goals": [
    "game engine project generation",
    "dependency installation",
    "runtime execution",
    "asset licensing decisions",
    "publish or deployment"
  ]
}
```

For a new AI-facing plugin slice, the validation command may start as a planned contract rather than an implemented CLI. If a command is not implemented yet, the manifest or README must say so explicitly. The `domain.game-content` and `domain.game-audio` first validators are implemented as focused contract validators, not generic JSON Schema engines.

## Packet shape / packet 形状

AI-facing plugins should reuse the existing task-packet authority model.

```text
work/ai-tasks/<task-id>/
  task.json
  TASK.md
  input/
    plugin.json
    source.json
    output-shape.json
    examples.json
    handoff-context.json
  output/
    candidate.json
    validation.json
    review-artifact.json
```

Authority order:

1. `task.json`: concrete task authority.
2. `input/source.json`: source of truth for the task.
3. `input/output-shape.json`: candidate contract.
4. `input/plugin.json`: plugin capability and boundary declaration.
5. `input/examples.json`: advisory examples only.
6. `input/handoff-context.json`: downstream context only.

If `TASK.md` and `task.json` disagree, `task.json` wins.

## AI confirmation / AI確認

Before writing output, the agent should summarize:

- files it will read;
- exact file it will write;
- validation command or validation gap;
- prohibited actions;
- whether the plugin is AI-facing only.

Safe wording:

> I will use this AI-facing plugin only as instructions and schemas, write only the declared candidate file, run the declared validator if available, and perform no runtime execution, dependency install, project generation, build, publish, or deployment. Proceed with this specific task?

Generic earlier messages such as "continue" do not authorize a newly generated plugin task.

## First domain candidate / 最初の domain 候補

The first candidate domain is game content management because it exercises domain modeling without requiring Mtool to become a game engine.

The concrete first package lives at:

```text
mtool/plugins/ai/domain.game-content/
```

First slice:

- scenario management;
- map and scene management;
- character status management;
- bundle/handoff shape for an external game runtime owner.

Later slices:

- music cue management: `mtool/plugins/ai/domain.game-audio/`;
- sound effect management: `mtool/plugins/ai/domain.game-audio/`;
- shared game-state sync;
- engine-specific handoff extensions for Unity, Godot, web canvas, or another selected runtime.

## Relationship to existing docs / 既存文書との関係

- [AI Task-Packet Workflow](ai-task-packet-workflow.md) remains the base authority model.
- [AI Schema Proposal Handoff Guide](ai-schema-proposal-handoff-guide.md) remains the schema-proposal-specific guide.
- [External No-Code Output](external-no-code-output.md) remains the external app/no-code handoff contract.
- [Current Plans](current-plans.md) is the active plan index.

## Non-goals / 非目標

This interface does not standardize code-facing plugins yet. It also does not promise that every AI-facing plugin has an implemented validator, generated UI, runtime adapter, browser route, or sample stack on day one.

Code-facing plugin hooks require a separate plan because they affect compatibility, loading, validation, and runtime safety.
