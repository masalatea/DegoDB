# Mobile FS common requirements / mobile FS 共通要件

Date: 2026-07-14

## Summary / summary

This report extracts common Mtool-owned requirements from the first-round mobile external feasibility studies.

First-round studies completed:

- `react-web-capacitor`;
- `pwa-readiness`;
- `codex-code-builder`;
- `flutter`;
- `react-native`;
- `native-wrapper-boundary`;
- `claude-code-builder`.

Main conclusion:

Mtool should own a provider/framework-neutral app handoff contract, plus target-specific extension packets. It should not own production frontend architecture, native project creation, signing, store submission, or offline sync by default.

## Common Mtool-owned artifact set / Mtool共通所有artifact

The common artifact set should include:

1. App handoff packet
   - schema version;
   - project/app identity;
   - source artifact refs and hashes;
   - screens;
   - navigation;
   - endpoints;
   - actions;
   - validation/error categories;
   - auth mode;
   - server authority;
   - non-goals.
2. Source artifact index
   - OpenAPI ref/hash;
   - no-code runtime ref/hash;
   - screen metadata ref/hash;
   - auth policy ref/hash;
   - bridge/runtime refs where available.
3. Ownership boundary
   - Mtool-owned artifacts;
   - external-owner artifacts;
   - confirmation-required actions;
   - not-generated-by-Mtool list.
4. Runtime/delivery readiness metadata
   - environment/base URL matrix;
   - static asset/build output facts;
   - PWA readiness mode;
   - browser/native storage class;
   - cacheability/offline policy.
5. AI/code-builder task packet
   - provider-neutral facts;
   - allowed actions;
   - confirmation-required actions;
   - forbidden guesses;
   - questions to ask;
   - validation commands;
   - target-specific companion note hooks.
6. Bundle manifest
   - artifact list;
   - intended consumption order;
   - target extension packet refs;
   - validation summary refs.

## Target-specific extension points / target別extension

### React/Web + Capacitor extension

Target-specific fields:

- web build output directory;
- public path/static asset policy;
- deep-link/callback policy;
- Capacitor project ownership note;
- native plugin checklist;
- secure storage expectation;
- React bridge/runtime compatibility facts.

Mtool should not generate:

- production React app shell;
- `package.json`;
- `capacitor.config.*`;
- `ios/`;
- `android/`;
- signing or store files.

### Flutter extension

Target-specific fields:

- widget/layout intent;
- navigation stack model;
- state management expectation;
- form validation binding;
- theming/design-token mapping;
- HTTP/OIDC package expectation or selection boundary;
- secure storage expectation;
- platform permission mapping;
- build flavor/environment mapping.

Mtool should not generate Dart source by default.

### React Native extension

Target-specific fields:

- navigation library expectation;
- state management expectation;
- form binding and validation display mapping;
- component/design-token mapping;
- API/OIDC package expectation or selection boundary;
- secure storage module expectation;
- native module/plugin policy;
- iOS/Android permission mapping;
- Expo versus bare React Native boundary, if selected later.

Mtool should not generate React Native source by default.

### PWA/runtime extension

Target-specific fields:

- manifest fields;
- service worker/cache policy;
- app installability checklist;
- browser storage class;
- static shell cacheability;
- API cacheability/stale-data policy;
- offline mode.

Recommended PWA modes:

| Mode | Meaning |
| --- | --- |
| `pwa_disabled` | No PWA metadata emitted. |
| `pwa_installable_online_only` | Installable app, but API behavior requires online server access. |
| `pwa_static_cache_only` | Static shell/assets can be cached; business data remains online-only. |
| `pwa_sync_contract_required` | Offline data behavior is blocked until explicit sync contract exists. |

### AI/code-builder extension

Target-specific fields:

- provider-neutral `mobile-ai-code-builder-task-v1` packet;
- optional Codex companion notes;
- optional Claude companion notes;
- context/file-reading guidance;
- command execution and permission policy;
- validation command map.

Provider-specific notes should not become the durable contract.

## Shared validation rules / 共通validation rule

Validation should check:

1. schema versions are known;
2. source artifact refs exist or are explicitly external;
3. source artifact hashes are present when artifacts are copied;
4. every mutating action declares idempotency policy;
5. server-side authorization/CSRF/Transaction Full authority is not weakened;
6. token storage policy is explicit;
7. offline sync is disabled unless a sync contract exists;
8. native project/signing/store generation is not implied by handoff mode;
9. selected target has required extension metadata;
10. validation command list exists for the selected target.

## Forbidden implicit behavior / 暗黙にやらないこと

Mtool or an AI consumer must not silently:

- create native iOS/Android projects;
- initialize Capacitor/Flutter/React Native projects;
- install dependencies;
- choose persistent token storage;
- add refresh-token behavior;
- enable offline sync;
- store production user/business data locally;
- choose native plugins;
- configure signing;
- configure store submission;
- overwrite existing external app files.

These require explicit user confirmation or an explicit project policy artifact.

## Output mode implications / output mode への影響

### `mtool_no_code`

Meaning:

- Mtool's own generated web/no-code/runtime output is the primary app surface.

Required metadata:

- app handoff packet;
- runtime/delivery readiness metadata;
- PWA readiness if selected;
- validation command list.

Boundary:

- Mtool owns its generated output and validation boundary.
- Native/app framework generation remains off unless explicitly selected.

### `external_no_code`

Meaning:

- Mtool emits handoff/input artifacts for an external no-code/app framework/code-builder.

Required metadata:

- app handoff packet;
- source artifact index;
- ownership boundary;
- target extension packet;
- AI/code-builder task packet when an AI builder is the consumer;
- validation command list;
- confirmation-required action list.

Boundary:

- Mtool owns the input packet and validation.
- External tool/owner owns app project, source code, dependencies, native project, signing, QA, and store submission.

### `hybrid`

Meaning:

- Mtool keeps its own web/no-code/runtime output and also emits external handoff artifacts.

Required metadata:

- everything required by `mtool_no_code`;
- everything required by selected `external_no_code` target;
- clear statement of which output is canonical for each user-facing surface.

Boundary:

- Useful while external FE/no-code suitability is being validated.
- Must not imply automatic two-way sync between two frontend implementations.

## Recommended next product contract / 推奨次contract

The next product contract should be:

```text
mobile-external-output-contract-v1
```

It should include:

- common app handoff;
- source artifact index;
- ownership boundary;
- runtime/delivery readiness;
- selected output mode;
- selected targets;
- target extension packets;
- AI/code-builder task packet, when applicable;
- validation command map;
- non-goals.

## Remaining parked items / parked項目

Keep parked:

- direct native iOS/Android generation;
- commercial no-code platform-specific output;
- automatic store submission;
- automatic signing/certificate management;
- offline sync without explicit sync contract;
- provider-specific AI contract as the core durable artifact.

## Exit condition for MW-11 / MW-11完了条件

MW-11 is complete for the current feasibility scope because this report defines:

- common artifact set;
- target-specific extension points;
- shared validation rules;
- fallback/non-goal behavior;
- output mode implications.

The next active step is MW-12: harden mobile output mode settings and contract names based on these common requirements.
