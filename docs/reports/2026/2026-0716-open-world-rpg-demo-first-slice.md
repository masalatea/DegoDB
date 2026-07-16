# 2026-07-16 Open World RPG Demo First Slice

## Summary

Added `sample47-open-world-rpg-demo` as a dependency-free Node.js communication game sample.

The sample targets a one-player-friendly, multiplayer-room-capable action RPG style:

- URL-named room;
- multiple players can join the same room;
- player-vs-player combat is disabled;
- enemies spawn away from players;
- enemies move slowly and weakly attack;
- Space swings a short sword;
- defeated enemies grant EXP and Gold;
- player HP slowly regenerates while idle;
- trees, rocks, and pond obstacles block movement;
- right-side HUD shows HP / EXP / Gold / enemy count;
- room-scoped SSE `rpg.updated` updates the browser.

## Boundary

This is a sample runtime, not production game-server generation.

Out of scope:

- authentication;
- moderation;
- anti-cheat;
- production persistence;
- public deployment;
- pathfinding sophistication;
- PvP combat.

## Validation

The validator checks:

- room page and room slug injection;
- join;
- multiple players;
- enemy spawn away from players;
- obstacle collision;
- move command;
- sword damage;
- enemy defeat rewards EXP and Gold;
- server tick enemy movement and weak attack;
- idle HP regeneration;
- PvP forbidden;
- 7-day inactive reset;
- same-room SSE `rpg.updated`;
- dependency-free boundary.
