# Sample 48: AI Plugin Lantern Game

This sample is a small static browser game based on the AI-facing game-content and game-audio plugin examples.

The player collects lantern charms and opens the shrine gate while avoiding drifting shadows.

## First-slice implementation

The sample uses only browser standard APIs:

- no npm dependencies;
- no Node.js server;
- no database;
- no external image or audio files;
- no game engine project.

The visual assets are custom canvas shapes. The audio cues are metadata only and are represented by a small WebAudio tone adapter in the browser; no audio files are generated or bundled.

## AI plugin linkage

The sample references:

- `mtool/plugins/ai/domain.game-content/examples/minimal-rpg/candidate.json`
- `mtool/plugins/ai/domain.game-audio/examples/minimal-rpg-audio/candidate.json`

The local reference packet is:

- `reference/lantern-game-input.sample.json`

## Validate

```bash
node sample/tutorials/sample48-ai-plugin-lantern-game/scripts/validate-sample.mjs
```

## Run locally

Open:

```text
sample/tutorials/sample48-ai-plugin-lantern-game/public/index.html
```

Controls:

- arrow keys or WASD to move;
- collect 3 lantern charms;
- reach the gate after all charms are collected.

This is a static proof sample, not a production game engine, asset pipeline, audio pipeline, or runtime plugin.
