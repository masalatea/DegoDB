# Game Audio Runtime Handoff / game audio runtime handoff

This handoff is for an external audio/runtime owner.

Mtool / AI-facing plugin owns:

- structured audio metadata candidate shape;
- music cue, SFX cue, and trigger vocabulary;
- task-packet and review boundary;
- explicit non-goals and prohibited actions.

External audio/runtime owner owns:

- final audio asset selection;
- license review and attribution;
- playback implementation;
- mixer behavior;
- runtime loading and caching;
- engine-specific integration;
- dependency installation;
- build, publish, deployment, signing, and release.

The first slice does not include:

- generated audio files;
- generated middleware config;
- Unity, Godot, WebAudio, FMOD, or Wwise-specific project files;
- shared-state synchronized audio events.

Any engine-specific audio handoff must be introduced as a later explicit plugin extension or separate task packet.
