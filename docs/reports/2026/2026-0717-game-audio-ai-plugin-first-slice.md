# Game audio AI plugin first slice / game audio AI plugin first slice

English companion:
This report records the first music/SFX AI-facing plugin package after the game-content package and validator.

## Summary / 要約

Added `domain.game-audio` under `mtool/plugins/ai/` as an AI-facing package only.

It defines:

- plugin manifest;
- candidate JSON schema;
- task packet template and human task instructions;
- minimal RPG audio source and candidate examples;
- validator contract placeholder;
- external audio/runtime handoff notes.

## Boundary / 境界

This slice does not add audio asset generation, asset licensing decisions, playback runtime, mixer implementation, dependency installation, engine project generation, build, publish, or deployment behavior.

The package is metadata and handoff only.

## Files / ファイル

- `mtool/plugins/ai/domain.game-audio/plugin.json`
- `mtool/plugins/ai/domain.game-audio/README.md`
- `mtool/plugins/ai/domain.game-audio/schemas/game-audio-candidate.schema.json`
- `mtool/plugins/ai/domain.game-audio/packets/ai-game-audio-task.template.json`
- `mtool/plugins/ai/domain.game-audio/packets/AI-GAME-AUDIO-TASK.md`
- `mtool/plugins/ai/domain.game-audio/examples/minimal-rpg-audio/source.json`
- `mtool/plugins/ai/domain.game-audio/examples/minimal-rpg-audio/candidate.json`
- `mtool/plugins/ai/domain.game-audio/validators/README.md`
- `mtool/plugins/ai/domain.game-audio/handoff/GAME-AUDIO-RUNTIME-HANDOFF.md`

## Follow-up / 次候補

Validator first slice completed after the initial package:

- `mtool/scripts/validate_ai_game_audio_packet.php`
- `mtool/app/ai_plugin_packet.php`
- `tests/Integration/AiPluginPacketTest.php`

Possible next plugin slices:

- shared-state sync AI plugin;
- separate code-facing plugin interface.
