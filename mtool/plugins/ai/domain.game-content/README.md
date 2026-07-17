# Game Content AI Plugin / game content AI plugin

This is the first concrete AI-facing plugin package.

It is for Codex / Claude style agents that need to turn game-design source material into structured content candidates for review.

Current scope:

- scenario management;
- map and scene management;
- character status management;
- external runtime handoff boundary.

Out of scope:

- game engine project generation;
- runtime execution;
- dependency installation;
- production server, matchmaking, anti-cheat, signing, publish, or deployment;
- music, sound effects, and shared game-state sync. Those are later slices.

## Files

```text
plugin.json
schemas/game-content-candidate.schema.json
packets/ai-game-content-task.template.json
packets/AI-GAME-CONTENT-TASK.md
examples/minimal-rpg/source.json
examples/minimal-rpg/candidate.json
validators/README.md
handoff/GAME-RUNTIME-HANDOFF.md
```

## Agent boundary

An agent may use this plugin as instruction, schema, examples, and handoff context.

An agent must not use this plugin as authority to execute a game runtime, install packages, generate Unity/Godot/native projects, mutate Mtool metadata, build, publish, or deploy.

## Validation

Run:

```sh
php mtool/scripts/validate_ai_plugin_packet.php \
  --plugin=mtool/plugins/ai/domain.game-content/plugin.json \
  --task=mtool/plugins/ai/domain.game-content/packets/ai-game-content-task.template.json \
  --candidate=mtool/plugins/ai/domain.game-content/examples/minimal-rpg/candidate.json
```

The first validator is contract-focused. It checks manifest/task/candidate boundaries and game-content ID references; it is not a generic JSON Schema engine.
