# Mobile FS: Native wrapper packaging boundary

Date: 2026-07-14

## Summary / summary

Layer: Layer C, delivery/runtime mode consumers.

Target: Native wrapper packaging boundary.

Study key: `native-wrapper-boundary`.

Recommendation: continue to second pass as a boundary contract.

Blocker severity:

- low for boundary clarity;
- medium for native execution.

The current mobile wrapper artifacts already express the most important native boundary: Mtool emits metadata and handoff packets, but does not initialize native projects, manage signing, run store submission, or own device QA.

This is the right boundary for now. Mtool should preserve it as an explicit contract and avoid silently becoming a native app build system.

## Current boundary / 現在の境界

Mtool owns:

- metadata;
- validation;
- source artifact references;
- package manifests;
- structured handoff packets;
- non-goals;
- server-side authority statements.

External owner/tool owns:

- React app shell;
- Capacitor project;
- Flutter app;
- React Native app;
- native project files;
- native plugins;
- app IDs;
- deep links;
- build configuration;
- signing;
- store submission;
- device QA.

## Missing or weak metadata / 不足・弱いmetadata

Second pass should extract a formal native wrapper boundary section with:

1. native capability declarations;
2. plugin checklist;
3. app ID ownership;
4. deep-link/callback ownership;
5. web build output directory;
6. static asset/public path policy;
7. signing and certificate non-goals;
8. store submission non-goals;
9. simulator/device QA ownership;
10. native project overwrite policy.

## Recommendation / recommendation

Continue to second pass.

Do not implement native execution UI yet. The contract is valuable precisely because it prevents accidental scope expansion.
