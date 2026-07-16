# 2026-0716 Choice Adventure Game First Slice

## Summary

Added `sample46-choice-adventure-game` as a static-first old-school choice adventure sample.

The sample focuses on structured scenario data and simple interaction: opening, picture-card scene changes, keyboard selection, direct click/tap choices, adventure API-style next scene responses, good ending, and game over.

## Product shape

- Opening scene.
- CSS flipbook-like scene panels.
- Up/down keyboard choice selection.
- Enter confirmation.
- Mouse / touch direct choice selection.
- Adventure API contract for next scene / choices.
- Mock API adapter so no Node.js server is needed.
- About five choices to goal.
- Wrong choices lead to game over.
- Game over supports return/back or restart.

## Boundary

This is not a production scenario editor.

It does not include:

- peer/player communication;
- Node.js server runtime;
- database;
- external image assets;
- production writing workflow;
- production art pipeline;
- save/load state.

## Mtool artifact

Added:

- `reference/choice-adventure-input.sample.json`

The packet defines:

- scenario metadata;
- adventure API contract;
- mock API adapter boundary;
- input modes;
- scene schema;
- choice schema;
- opening / ending / game-over scenes;
- validation checks;
- forbidden actions around network, remote assets, database, or production writer/art claims.

## Validation

```bash
node sample/tutorials/sample46-choice-adventure-game/scripts/validate-sample.mjs
```

The validator checks:

- required files;
- no npm dependency;
- no Node.js requirement;
- no peer communication requirement;
- adventure API contract;
- mock API adapter;
- opening scene;
- five-choice goal path;
- game-over path;
- back/restart after game over;
- keyboard up/down/enter hooks;
- mouse/touch choice hook;
- structured scenario data;
- CSS scene panels instead of external image assets.
