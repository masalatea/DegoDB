# AI Game Content Task / AI game content task

Read `task.json` first. If this file disagrees with `task.json`, `task.json` wins.

## Goal

Create one review-only `output/candidate.json` for game content management.

The candidate should structure:

- scenario;
- map and scene;
- character status;
- external runtime handoff boundary.

## Required confirmation

Before writing, summarize the declared reads, the single allowed write, the validator status, and the prohibited actions. Then ask the confirmation text from `task.json`.

## Allowed output

Write only:

```text
output/candidate.json
```

Use schema version:

```text
game_content_ai_candidate.v1
```

## Prohibited actions

Do not:

- execute a runtime;
- install dependencies;
- create a Unity, Godot, native, or web game project;
- mutate Mtool metadata;
- mutate a database;
- build, publish, deploy, sign, or submit anything;
- decide final asset licensing;
- claim production game server, matchmaking, or anti-cheat support.

## Review boundary

Validation is a planned contract in the first slice. Until the validator exists, mark the result as manually reviewable and keep handoff boundaries explicit.
