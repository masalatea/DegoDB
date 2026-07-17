# Validator Contract / validator contract

Command:

```sh
php mtool/scripts/validate_ai_plugin_packet.php \
  --plugin=mtool/plugins/ai/domain.game-content/plugin.json \
  --task=<task.json> \
  --candidate=<candidate.json>
```

Current checks:

- plugin manifest has `schema_version=mtool-ai-plugin-v1`;
- task packet declares `plugin_id=domain.game-content`;
- candidate has `schema_version=game_content_ai_candidate.v1`;
- candidate has the expected schema version and required first-slice sections;
- scene transitions target declared scene IDs;
- map areas target declared scene IDs;
- runtime handoff lists Mtool-owned and external-runtime-owned responsibilities;
- candidate does not claim runtime execution, dependency installation, project generation, publish, or deployment.
