# Game Audio AI Plugin / game audio AI plugin

This AI-facing plugin helps an agent plan BGM and SFX metadata for a game content packet.

Current scope:

- music cue metadata;
- sound effect cue metadata;
- scene and event trigger mapping;
- external audio/runtime handoff boundary.

Out of scope:

- generating audio files;
- choosing final asset licenses;
- playback engine or mixer implementation;
- dependency installation;
- Unity, Godot, native, or web project generation;
- build, publish, or deployment.

## Files

```text
plugin.json
schemas/game-audio-candidate.schema.json
packets/ai-game-audio-task.template.json
packets/AI-GAME-AUDIO-TASK.md
examples/minimal-rpg-audio/source.json
examples/minimal-rpg-audio/candidate.json
validators/README.md
handoff/GAME-AUDIO-RUNTIME-HANDOFF.md
```

## Validation

Run:

```sh
php mtool/scripts/validate_ai_game_audio_packet.php \
  --plugin=mtool/plugins/ai/domain.game-audio/plugin.json \
  --task=mtool/plugins/ai/domain.game-audio/packets/ai-game-audio-task.template.json \
  --candidate=mtool/plugins/ai/domain.game-audio/examples/minimal-rpg-audio/candidate.json
```

The first validator is contract-focused. It checks manifest/task/candidate boundaries, cue ID uniqueness, trigger cue references, and non-goal boundaries; it is not a generic JSON Schema engine.
