# AI Game Audio Task / AI game audio task

Read `task.json` first. If this file disagrees with `task.json`, `task.json` wins.

## Goal

Create one review-only `output/candidate.json` for game audio metadata.

The candidate should structure:

- music cues;
- sound effect cues;
- scene and event trigger mapping;
- external audio/runtime handoff boundary.

## Required confirmation

Before writing, summarize the declared reads, the single allowed write, the validator status, and the prohibited actions. Then ask the confirmation text from `task.json`.

## Allowed output

Write only:

```text
output/candidate.json
```

Use schema version:

```text
game_audio_ai_candidate.v1
```

## Prohibited actions

Do not:

- generate audio files;
- choose final asset licensing;
- implement playback runtime or mixer logic;
- install dependencies;
- create a Unity, Godot, native, or web game project;
- mutate Mtool metadata;
- mutate a database;
- build, publish, deploy, sign, or submit anything.

## Review boundary

Validation is a planned contract in this slice. Until the validator exists, mark the result as manually reviewable and keep handoff boundaries explicit.
