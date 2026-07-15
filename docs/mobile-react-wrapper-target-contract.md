# Mobile React Wrapper Target Contract / mobile React wrapper target contract

English companion:
This document defines the M3 contract for the first mobile wrapper target. It bridges the validated `mobile-app-handoff.json` packet to a React/Web + Capacitor-style iOS/Android preparation lane without making Mtool own native builds, app signing, store submission, or a durable frontend component framework.

この文書は、first mobile wrapper target の M3 contract です。検証済み `mobile-app-handoff.json` packet を React/Web + Capacitor 系 iOS/Android 準備 lane に渡すための境界を定義します。Mtool が native build、app signing、store submission、恒久的frontend component frameworkを所有するものではありません。

## Position / 位置づけ

The wrapper target contract starts only after the handoff packet passes:

```php
app_mobile_app_handoff_validate($packet)['ready'] === true
```

Mtool's job is to provide a stable, reviewable input package. The app creator, Codex/Claude, or an external mobile builder owns the actual React app shell, Capacitor project setup, native build configuration, signing, device QA, and store distribution.

Mtool の責務は、安定した review 可能な input package を出すことです。実際の React app shell、Capacitor project setup、native build configuration、signing、device QA、store distribution は app 作成者、Codex/Claude、外部 mobile builder が所有します。

## Required input artifacts / 必須 input artifact

The wrapper preparation lane consumes these artifacts:

| Artifact | Owner | Purpose |
| --- | --- | --- |
| `mobile-app-handoff.json` | Mtool | Authoritative app/mobile packet. |
| `mobile-app-handoff.md` | Mtool | Creator-facing checklist and explanation. |
| OpenAPI/API contract ref | Mtool | Endpoint method/path/body/response/auth/error mapping. |
| No-code runtime metadata ref | Mtool | Existing web/runtime behavior and preview shape. |
| Screen metadata ref | Mtool | List/detail/form/navigation/action intent. |
| Auth policy ref | Mtool | SSO/OIDC/session/bearer assumptions and token boundary. |
| Optional `NO-CODE-REACT-BRIDGE/bridge-contract.json` | Mtool | Existing React/TypeScript adapter reference when available. |

Every referenced artifact must have a stable ref and SHA-256 hash in the handoff packet. The wrapper lane should not fetch mutable unnamed files.

## First target / first target

The first target key is:

```text
react_web_capacitor_ios_android
```

This target means:

- React/Web is the first app implementation surface;
- Capacitor-style tooling is the first iOS/Android packaging route;
- PWA can share the web artifact as an optional sibling target;
- Flutter WebView wrapper and React Native remain later input-packet consumers;
- direct native iOS/Android generation remains a non-goal.

## Wrapper package shape / wrapper package shape

A wrapper-ready package should expose:

```text
mobile-wrapper-target/
  wrapper-target-contract.json
  WRAPPER-CONSUMER-NOTES.md
  source-artifacts/
    mobile-app-handoff.json
    mobile-app-handoff.md
    ...
```

`wrapper-target-contract.json` should include:

```json
{
  "contract_schema_version": "mobile-react-wrapper-target-v1",
  "target_key": "react_web_capacitor_ios_android",
  "mutation_performed": false,
  "input_handoff_schema_version": "mobile-app-handoff-v1",
  "source_artifacts": {},
  "web_runtime": {},
  "react_adapter": {},
  "capacitor_boundary": {},
  "auth_boundary": {},
  "api_boundary": {},
  "screen_flow_boundary": {},
  "action_boundary": {},
  "native_capability_boundary": {},
  "offline_boundary": {},
  "security_boundary": {},
  "verification": {},
  "non_goals": []
}
```

The first side-effect-free package builder is:

```php
app_mobile_wrapper_target_build_c1_package(array $handoff): array
```

It validates the handoff packet first, then returns an in-memory package containing:

- `wrapper-target-contract.json`
- `WRAPPER-CONSUMER-NOTES.md`

This first builder does not write files, initialize Capacitor, create a React project, build native targets, manage signing, or submit apps.

The first controlled emitter is:

```php
app_mobile_wrapper_target_emit_c1_package(array $handoff, string $targetDir): array
```

It writes only the two C1 package files into an artifact directory and refuses to overwrite existing files. It still does not initialize or mutate React, Capacitor, iOS, or Android projects.

## Optional external output / optional external output

After the wrapper app handoff exists, Mtool can also emit an optional external no-code/tool packet:

```text
react-web-capacitor-output/
  external-output.json
  EXTERNAL-OUTPUT.md
```

The builder is:

```php
app_mobile_wrapper_target_build_external_optional_output_packet(array $handoff): array
```

The controlled emitter is:

```php
app_mobile_wrapper_target_emit_external_optional_output_packet(array $handoff, string $targetDir): array
```

CLI:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=external-output \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-web-capacitor-output
```

This output is additive. It does not replace `mtool_no_code`; it gives external React/Web + Capacitor-style consumers a machine-readable `external_no_code` packet with source refs, screen/action/readiness metadata, server authority boundary, ownership boundary, confirmation-required actions, forbidden-without-artifact rules, validation gates, and non-goals.

## AI task packet artifact / AI task packet artifact

The companion `ai-task-packet` artifact gives Codex / Claude style tools a bounded entry point:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=ai-task-packet \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/ai-task-packet
```

It emits only:

```text
task.json
TASK.md
input/external-output.json
input/mobile-app-handoff.json
```

The packet state is `pending_user_confirmation`. It requires the AI to read the declared inputs, explain that `mtool_no_code` remains the supported baseline and `external_no_code` is optional/additive, then ask the declared confirmation question before writing anything.

Before confirmation, allowed writes are empty. Dependency installation, network calls, Capacitor init / `cap sync`, native project generation, file overwrite, token-storage choices, offline sync, native plugin selection, native build, signing, store submission, Mtool metadata writes, and DB writes are forbidden without explicit user confirmation.

## Output mode config artifact / output mode config artifact

The `output-mode-config` artifact records the selected mobile output mode and the artifact keys that should be followed for that mode.

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=output-mode-config \
  --output-mode=hybrid \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/output-mode-config
```

Supported modes are:

- `mtool_no_code`
- `external_no_code`
- `hybrid`

This is a selection/config packet only. It emits `output-mode-config.json` and `OUTPUT-MODE-CONFIG.md`; it does not create app source, initialize Capacitor, install dependencies, or overwrite existing files.

## PWA readiness artifact / PWA readiness artifact

The `pwa-readiness` artifact records installability, cache, storage, and offline-readiness metadata for Mtool web/no-code and React/Web wrapper consumers.

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=pwa-readiness \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/pwa-readiness
```

It emits only:

```text
pwa-readiness.json
PWA-READINESS.md
```

This is metadata/checklist output. It does not generate `manifest.webmanifest`, a service worker, background sync, offline sync, push notifications, app source code, or native project files.

See [External No-Code Output](external-no-code-output.md) for the stable field guide.

## React Native later-platform packet / React Native later-platform packet

The existing later-platform output includes `react-native-input-packet.json`.
For React Native, that packet now includes `react_native_extension` metadata.

The extension covers:

- navigation expectations and route inputs;
- state management classes and server-state authority;
- form binding and validation/error mapping;
- API client, retry, and idempotency expectations;
- OIDC/auth and secure-storage policy boundaries;
- native module permission policy and Expo vs bare ownership boundary;
- environment/build ownership;
- typecheck/unit/device QA expectations.

This remains a structured input packet only.
It does not initialize Expo or React Native projects, install dependencies, add native modules, write `ios/` or `android/`, choose signing assets, run native builds, or submit apps.

## Boundary details / boundary details

### Mtool owns

- validated handoff packet shape and validation status;
- source artifact refs and hashes;
- endpoint/auth/screen/action/error/native capability metadata;
- existing no-code runtime and optional React bridge reference artifacts;
- consumer notes explaining what must be reviewed before wrapper work begins.

### External wrapper owner owns

- React app shell and routing decisions;
- durable frontend state management;
- SSO/OIDC client configuration and secure token storage implementation;
- API client implementation and retry strategy;
- Capacitor project initialization and plugin selection;
- Xcode/Android Studio configuration;
- app icon/splash production assets;
- signing keys, certificates, provisioning profiles, and store credentials;
- device QA and store submission.

### Shared boundary

- action intent payloads must match the generated runtime/action contract;
- mutating actions must preserve idempotency expectations;
- auth failures, validation failures, network failures, unavailable actions, and success states must map to user-visible UI;
- native capabilities must be declared before plugin work starts;
- offline sync stays disabled unless an explicit sync contract exists.

## Relationship to existing React bridge / 既存 React bridge との関係

The existing `NO-CODE-REACT-BRIDGE` artifact is useful evidence and a reference adapter. It proves that a React + TypeScript scaffold can consume no-code runtime metadata and emit action intents.

For mobile wrapper work, it should be treated as an input/reference, not as the entire app:

- use `bridge-contract.json` to understand runtime/action intent invariants;
- use `src/mtoolNoCodeBridge.ts` as a helper reference when available;
- do not assume the generated scaffold is the production React app shell;
- do not grant mutation authority merely because a button renders in React;
- keep server route authorization, CSRF, idempotency, and Transaction Full behavior on the server side.

## Ready criteria / ready criteria

The wrapper target contract is ready when:

- the source `mobile-app-handoff.json` validates with no blockers;
- the first target is `react_web_capacitor_ios_android`;
- source artifact hashes are present;
- auth, API, screen, navigation, action, validation, and error mappings are explicit;
- native capabilities are declared, including `none`;
- the external owner boundary for Capacitor/native build/signing is explicit;
- non-goals include direct native generation, app-store signing by Mtool, offline sync by default, and production user data in packet;
- consumer notes tell an app creator what to inspect before starting wrapper work.

## Non-goals / 非目標

- Mtool generating a full production React app.
- Mtool initializing or maintaining a production Capacitor project.
- Mtool owning iOS/Android signing, certificates, store credentials, or submission.
- Mtool implementing secure token storage inside native apps.
- Mtool enabling offline sync by default.
- Mtool turning the existing React bridge scaffold into the only supported frontend framework.
