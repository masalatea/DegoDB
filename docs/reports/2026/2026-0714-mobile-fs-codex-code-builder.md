# Mobile FS: Codex-style code-builder handoff

Date: 2026-07-14

## Summary / summary

Layer: Layer B, AI/code-builder consumers.

Target: Codex-style code-builder handoff.

Study key: `codex-code-builder`.

Recommendation: continue to second pass.

Blocker severity:

- low for guided AI review;
- medium for autonomous code generation.

Current Mtool mobile wrapper artifacts are already readable enough for a Codex-style agent to understand target, ownership boundary, non-goals, verification gates, and server-side authority. The main gap is not more app code. The main gap is a dedicated AI task packet that tells the agent what it may infer, what it must ask the user, what it must not generate, and which validation commands prove the handoff is safe.

## Input artifacts / input artifacts

Working location:

```text
work/feasibility-studies/mobile-wrapper/20260714-codex-code-builder/
```

Generated input artifacts:

| Artifact | Path | SHA-256 |
| --- | --- | --- |
| wrapper target contract | `work/feasibility-studies/mobile-wrapper/20260714-codex-code-builder/input/c1/wrapper-target-contract.json` | `b5cb9cc59e6dc2d441567ebfd7875f4b0c1bcd244869be989345d5838151abc5` |
| React wrapper app handoff | `work/feasibility-studies/mobile-wrapper/20260714-codex-code-builder/input/react-wrapper-app/react-wrapper-app-handoff.json` | `9fd150417803eda9b6677b12851abbf2d7c371cb2e825976266582f26c6af5b8` |
| mobile wrapper bundle manifest | `work/feasibility-studies/mobile-wrapper/20260714-codex-code-builder/input/bundle-manifest/mobile-wrapper-bundle-manifest.json` | `5aaa38b948a90e5ff579c2a1fc137fa8278cedd19a789d003b78d4196b44f5de` |

Validation summary:

```text
work/feasibility-studies/mobile-wrapper/20260714-codex-code-builder/validation/summary.json
```

## Setup assumptions / setup assumptions

This study checks whether an AI builder can consume Mtool artifacts and guide the user toward a React/Web + Capacitor-style app implementation without guessing core behavior.

Assumptions:

- The AI builder runs in or near the user's workspace.
- The AI builder can read Mtool output artifacts and project files.
- The AI builder should ask the user before creating app projects, adding dependencies, choosing auth storage, or changing deployment/native packaging behavior.
- Mtool should provide enough structured information that the AI does not invent screens, actions, endpoints, auth policy, mutation semantics, native plugin requirements, or non-goals.

## Required Mtool artifacts / 必要なMtool成果物

Current useful artifacts:

- `WRAPPER-CONSUMER-NOTES.md`;
- `wrapper-target-contract.json`;
- `REACT-WRAPPER-APP-HANDOFF.md`;
- `react-wrapper-app-handoff.json`;
- `MOBILE-WRAPPER-BUNDLE.md`;
- `mobile-wrapper-bundle-manifest.json`.

The existing Markdown notes are especially useful for AI because they are short and directional:

- target;
- ownership boundary;
- implementation checklist;
- required validation gates;
- later targets;
- non-goals.

The JSON artifacts are useful for exact machine-readable state:

- schema versions;
- artifact refs and hashes;
- auth/API/screen/action boundaries;
- native/non-goal boundaries;
- verification gates.

## Missing or weak metadata / 不足・弱いmetadata

The current artifacts are good handoff materials, but they are not yet a complete AI task packet.

Missing or weak items:

1. explicit "ask the user before" list;
2. explicit "do not guess" list;
3. explicit "safe to infer" list;
4. target framework selection prompt;
5. output directory and overwrite policy;
6. dependency-install permission policy;
7. project creation policy;
8. validation command list by target and by risk level;
9. rollback/cleanup guidance for generated external app projects;
10. mapping from common requirements to target-specific prompt fragments.

These gaps are not blockers for guided review. They are blockers for safe autonomous code generation.

## Auth and token storage / auth・token storage

The current artifacts correctly state:

- OIDC mode;
- login/logout routes;
- no tokens in handoff packet;
- secure token storage implementation is external owner responsibility;
- redirect/deep-link policy is external owner configuration.

For an AI builder, Mtool should also provide a prompt-level rule:

```text
Do not choose persistent token storage, refresh-token behavior, callback/deep-link URLs, or native secure-storage plugins without user confirmation or an explicit project policy artifact.
```

This turns the existing boundary into operational AI behavior.

## API, action, validation, and error mapping / API・action・validation・error mapping

The current artifacts already help an AI avoid dangerous guesses:

- endpoint count is present;
- action count is present;
- mutating action is identified;
- idempotency is required;
- server authorization, CSRF, idempotency, and Transaction Full gates remain server-side.

The AI task packet should add:

- which endpoints are read-only versus mutating;
- which generated client code is allowed to call mutation endpoints;
- required error-state UI categories;
- validation commands before and after external app changes.

## Local storage and offline / local storage・offline

The current artifacts clearly state:

- offline sync is not default;
- no production user data in packet;
- offline sync requires explicit contract.

The AI task packet should make this a forbidden-guess rule:

```text
Do not add offline sync, local persistent user-data storage, background sync, or conflict resolution behavior unless an explicit sync contract artifact is present.
```

## Native and plugin responsibility / native・plugin責務

The current artifacts are strong here:

- Mtool does not initialize Capacitor;
- Mtool does not create native project files;
- Mtool does not manage signing or store submission;
- native plugins are external owner choices.

For Codex-style operation, this should become:

- ask before running any command that initializes a native/app project;
- ask before installing dependencies;
- ask before creating `ios/` or `android/`;
- ask before changing signing, app IDs, deep links, entitlements, or store settings.

## Generated files owned by external tool / 外部tool所有生成物

An AI builder may create or modify external app files only after user confirmation or an explicit task packet authorizes it.

External-owned files include:

- `package.json`;
- React app shell/routing/state/components;
- API client/retry implementation;
- OIDC client setup;
- secure storage implementation;
- `capacitor.config.*`;
- `ios/`;
- `android/`;
- build outputs;
- signing/store files.

## Recommended AI task packet shape / 推奨AI task packet

The second pass should consider a provider-neutral packet such as:

```json
{
  "schema_version": "mobile-ai-code-builder-task-v1",
  "target": "react_web_capacitor",
  "source_artifacts": [],
  "allowed_without_confirmation": [],
  "requires_user_confirmation": [],
  "forbidden_without_artifact": [],
  "implementation_facts": {},
  "questions_to_ask": [],
  "validation_commands": [],
  "non_goals": []
}
```

Codex-specific notes can then be an optional companion, not the core contract.

## Cross-layer dependencies / layer間依存

Layer A dependency:

- Codex-style handoff needs target-specific implementation facts for React/Web + Capacitor, Flutter, or React Native.

Layer C dependency:

- Codex-style handoff should include PWA/runtime readiness facts and native wrapper boundary facts so it does not invent delivery behavior.

## Recommendation / recommendation

Continue to second pass.

Codex-style handoff is feasible and useful, but the common output should be provider-neutral. The next hardening should focus on an AI task packet that separates:

- facts Mtool knows;
- choices the user must make;
- actions the AI may perform;
- actions the AI must ask before performing;
- behavior the AI must not invent.

Do not make this Codex-only. Use Codex as the first practical consumer, then check provider neutrality with the Claude-style study.
