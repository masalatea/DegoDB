# AI-facing game plugin first slice / AI向け game plugin first slice

English companion:
This report records the first concrete AI-facing plugin package after promoting the AI-facing plugin interface as the next active lane.

## Summary / 要約

Added `domain.game-content` under `mtool/plugins/ai/` as an AI-facing package only.

It defines:

- plugin manifest;
- candidate JSON schema;
- task packet template and human task instructions;
- minimal RPG source and candidate examples;
- validator contract placeholder;
- external game runtime handoff notes.

## Boundary / 境界

This slice does not add code-facing plugin hooks, runtime loading, dependency installation, game engine project generation, browser routes, or deployment behavior.

Music, sound effects, shared game-state sync, and engine-specific Unity/Godot/web-canvas handoff remain later slices.

## Files / ファイル

- `mtool/plugins/ai/domain.game-content/plugin.json`
- `mtool/plugins/ai/domain.game-content/README.md`
- `mtool/plugins/ai/domain.game-content/schemas/game-content-candidate.schema.json`
- `mtool/plugins/ai/domain.game-content/packets/ai-game-content-task.template.json`
- `mtool/plugins/ai/domain.game-content/packets/AI-GAME-CONTENT-TASK.md`
- `mtool/plugins/ai/domain.game-content/examples/minimal-rpg/source.json`
- `mtool/plugins/ai/domain.game-content/examples/minimal-rpg/candidate.json`
- `mtool/plugins/ai/domain.game-content/validators/README.md`
- `mtool/plugins/ai/domain.game-content/handoff/GAME-RUNTIME-HANDOFF.md`

## Follow-up / 次候補

Validator first slice completed after the initial package:

- manifest/task/candidate JSON decode checks;
- candidate schema validation;
- scene transition ID checks;
- map area scene ID checks;
- non-goal/prohibited-action checks.

Implementation:

- `mtool/app/ai_plugin_packet.php`
- `mtool/scripts/validate_ai_plugin_packet.php`
- `tests/Integration/AiPluginPacketTest.php`

Next scope selection moves to music/SFX, shared-state sync, or a separate code-facing plugin interface.
