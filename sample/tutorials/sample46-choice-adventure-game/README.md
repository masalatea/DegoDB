# Sample 46: Choice Adventure Game

This sample is a static-first old-school choice adventure game.

It uses structured scenario data, CSS flipbook-style scene panels, choice buttons, and an adventure API adapter. It does not need Node.js or peer/player communication.

## Product idea

- Opening scene.
- Old-school picture-card / flipbook-like scene changes.
- Up/down keyboard selection.
- Enter key confirmation.
- Mouse / touch direct selection.
- Adventure API style response for the next scene / choices.
- About five choices to reach the good ending.
- Wrong choices lead to game over.
- Game over allows returning to the previous scene or restarting.

## First-slice implementation

The first slice uses only browser standard APIs:

- no npm dependencies;
- no Node.js server;
- no database;
- no peer/player communication;
- no external image assets.

The "images" are CSS scene panels. The default API is a mock adapter, so the sample remains deterministic while preserving the adventure-game shape. A production app can replace it with `SAMPLE46_ADVENTURE_API_URL`.

## Mtool handoff structure

The sample includes a Mtool-shaped handoff packet:

- `reference/choice-adventure-input.sample.json`

The packet defines:

- scenario metadata;
- input modes;
- adventure API contract;
- mock API adapter boundary;
- scene schema;
- choice schema;
- opening / ending / game-over scenes;
- validation checks.

## Validate

```bash
node sample/tutorials/sample46-choice-adventure-game/scripts/validate-sample.mjs
```

## Run locally

Open:

```text
sample/tutorials/sample46-choice-adventure-game/public/index.html
```

This is a static sample game, not a production scenario editor, Node.js service, or art pipeline.
