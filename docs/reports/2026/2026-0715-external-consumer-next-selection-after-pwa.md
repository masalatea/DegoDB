# External consumer next selection after PWA readiness

## Status

`EF_M10_SELECTION_DONE`

## Purpose

Choose the next bounded implementation after:

- `external-output`;
- `ai-task-packet`;
- `output-mode-config`;
- `pwa-readiness`.

## Current state

The React/Web + Capacitor lane is now the first concrete external output target:

- optional external output packet exists;
- AI task packet exists;
- output mode config exists;
- PWA readiness metadata exists;
- sample35 consumes the external-output fixture;
- bundle manifest exposes the relevant artifacts.

Flutter and React Native already have first-pass later-platform input packets:

- `flutter-input-packet.json`;
- `react-native-input-packet.json`.

Those first-pass packets carry the shared app contract, but they intentionally avoid generating Flutter/Dart or React Native source.

## Candidate comparison

| Candidate | Value | Blocker / risk | Recommended now |
| --- | --- | --- | --- |
| User-approved real app dry-run | High practical proof, but requires target app directory and explicit user confirmation before writes/install/build | Needs user-provided external directory and separate approval; not safe to assume | Not automatic |
| Execution UI controls replan | Important later if UI can trigger artifact generation | Larger security/policy surface: CSRF, output-dir allowlist, overwrite, audit, failure handling | Not next implementation |
| Another external consumer packet from scratch | Adds breadth | Risk of shallow proliferation while Flutter/RN first-pass packets still lack extension detail | Not preferred |
| Flutter second-pass extension metadata | Good next platform detail; useful for Dart/mobile builders | Requires framework-specific fields but can stay metadata-only | Good candidate |
| React Native second-pass extension metadata | Good next platform detail; close to existing React/Web concepts and mobile wrapper boundary | Requires Expo/bare/native module boundary choices | Best next candidate |

## Decision

Promote React Native second-pass extension metadata as the next bounded implementation.

Rationale:

- React Native is closer to the existing React/Web + Capacitor mental model than Flutter, so it is the lower-friction second platform refinement.
- React Native has a clear unresolved boundary: navigation, state management, form binding, API/OIDC package expectations, secure storage module policy, Expo vs bare boundary, native module policy, permissions, and environment/build variants.
- This can remain an input-packet extension and does not require generating app source, installing packages, initializing a project, or building native targets.
- The result can later inform Flutter by making the second-pass extension structure concrete.

## Next scope

`EF-M11 React Native second-pass extension metadata`

First slice should:

- extend the React Native input packet with `react_native_extension`;
- include navigation/state/form/API/auth/storage/native module/environment/test metadata;
- keep package choices as expectations/boundaries, not automatic selections;
- preserve `not_generated_by_mtool`;
- update bundle/docs/tests;
- do not generate React Native source, package files, iOS/Android files, or run dependency/native commands.

## Non-goals

- React Native app generation;
- Expo project creation;
- bare React Native project creation;
- dependency installation;
- native module installation;
- iOS/Android project mutation;
- signing/store submission;
- device/simulator QA.
