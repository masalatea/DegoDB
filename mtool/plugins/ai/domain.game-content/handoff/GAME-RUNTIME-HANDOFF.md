# Game Runtime Handoff / game runtime handoff

This handoff is for an external game runtime owner.

Mtool / AI-facing plugin owns:

- structured content candidate shape;
- scenario, map, scene, and character status vocabulary;
- task-packet and review boundary;
- explicit non-goals and prohibited actions.

External runtime owner owns:

- game loop;
- rendering;
- controls and input handling;
- runtime persistence;
- asset pipeline and licensing;
- engine-specific project structure;
- package installation;
- production server, matchmaking, anti-cheat, deployment, signing, and release.

The first slice does not include:

- music cue management;
- sound effect management;
- shared game-state sync;
- Unity, Godot, or web-canvas-specific project files.

Any engine-specific handoff must be introduced as a later explicit plugin extension or separate task packet.
