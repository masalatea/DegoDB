# Validator Contract / validator contract

Command:

```sh
php mtool/scripts/validate_ai_game_audio_packet.php \
  --plugin=mtool/plugins/ai/domain.game-audio/plugin.json \
  --task=<task.json> \
  --candidate=<candidate.json>
```

Current checks:

- plugin manifest has `schema_version=mtool-ai-plugin-v1`;
- task packet declares `plugin_id=domain.game-audio`;
- candidate has `schema_version=game_audio_ai_candidate.v1`;
- music cue IDs and SFX cue IDs are unique;
- trigger cue IDs point to declared music or SFX cues;
- runtime handoff lists Mtool-owned and external-audio-runtime-owned responsibilities;
- candidate does not claim audio generation, licensing decision, playback implementation, dependency installation, project generation, publish, or deployment.
