# AI-Facing Plugins / AI向け plugins

AI-facing plugins are read by Codex / Claude style agents and by humans reviewing task packets.

They may contain:

- `plugin.json` manifest files;
- JSON schemas;
- task packet templates;
- examples;
- validator contracts;
- handoff notes.

They must not silently execute code, install dependencies, mutate DB/config metadata, build apps, publish artifacts, or generate native/runtime projects.

Current packages:

- `domain.game-content`: scenario, map, scene, and character-status content modeling.
- `domain.game-audio`: music/SFX cue metadata and audio runtime handoff planning.

The current interface is documented in [AI-Facing Plugin Interface](../../../docs/ai-facing-plugin-interface.md).
