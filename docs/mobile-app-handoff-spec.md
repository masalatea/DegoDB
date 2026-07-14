# Mobile App Handoff Spec / mobile app handoff spec

English companion:
This document defines the first app-creator-facing mobile handoff output. It is not a native-app generator contract. It tells an app creator, Codex/Claude, or a downstream mobile builder what Mtool must export before React/Web + Capacitor-style wrapper work begins.

この文書は、app 作成者向け mobile handoff output の first spec です。native app generator contract ではありません。React/Web + Capacitor 系 wrapper 作業を始める前に、Mtool が何を出せば app 作成者、Codex/Claude、下流 mobile builder が迷わないかを定義します。

## Output files / 出力ファイル

A mobile handoff package should contain both machine-readable and human-readable forms:

- `mobile-app-handoff.json`
- `mobile-app-handoff.md`

The JSON is the authoritative structured packet. The Markdown is a creator-facing explanation and checklist generated from the same source.

`mobile-app-handoff.json` が構造化 packet の正本です。`mobile-app-handoff.md` は同じ source から作る app 作成者向け説明と checklist です。

Suggested artifact location:

```text
work/artifacts/{project_key}/{source_output_key}/{artifact_key}/mobile-app-handoff/
```

The package must not contain secrets, access tokens, signing keys, App Store / Play Store credentials, private native certificates, or real production user data.

## Supported first target / 最初の対応 target

The first supported target is:

```text
React/Web no-code runtime -> Capacitor-style iOS/Android wrapper
```

PWA may share the same web artifact as an optional target. Flutter and React Native are later input-packet targets. Direct native iOS/Android generation is out of scope for this first contract.

## JSON shape v1 / JSON 形状 v1

The packet should use a stable version:

```json
{
  "schema_version": "mobile-app-handoff-v1",
  "mutation_performed": false,
  "project": {},
  "source_artifacts": {},
  "platform_targets": [],
  "app_identity": {},
  "auth": {},
  "api": {},
  "screens": [],
  "navigation": [],
  "actions": [],
  "validation": {},
  "error_states": [],
  "native_capabilities": [],
  "offline_and_local_storage": {},
  "security_and_privacy": {},
  "build_handoff": {},
  "verification_checklist": [],
  "non_goals": []
}
```

### Required top-level fields / 必須 top-level fields

| Field | Required meaning |
| --- | --- |
| `schema_version` | Must be `mobile-app-handoff-v1`. |
| `mutation_performed` | Must be `false`; the handoff packet does not build, deploy, sign, or publish an app. |
| `project` | Project key/name and human title. |
| `source_artifacts` | References and hashes for OpenAPI/API contracts, no-code runtime metadata, screen metadata, auth policy, and generated runtime URLs. |
| `platform_targets` | Ordered target matrix. First target is React/Web + Capacitor-style iOS/Android wrapper. |
| `app_identity` | App display name, bundle/package ID placeholders, icon/splash placeholders, environment labels. |
| `auth` | SSO/OIDC/bearer/session assumptions, login/logout route hints, token handling boundary, redirect/deep-link expectations. |
| `api` | Base URL policy, endpoint list, method/path/body/response refs, auth requirement, CORS/same-origin note, error envelope. |
| `screens` | List/detail/form/dashboard screen intent, fields, filters, sort, empty/loading/error states, safe route IDs. |
| `navigation` | Screen-to-screen transitions, URL/deep-link hints, guarded routes. |
| `actions` | Submit/update/delete/custom action metadata, confirmation, CSRF/idempotency, dry-run/disabled/default-off state, success/error mapping. |
| `validation` | Field and action validation rules and where they are enforced. |
| `error_states` | Auth errors, network errors, validation errors, conflict/stale state, server failure, unavailable actions. |
| `native_capabilities` | Camera, file, notification, geolocation, offline storage, biometric, or other native needs; `none` is explicit. |
| `offline_and_local_storage` | Whether local draft/offline cache is allowed; first target should default to no offline sync unless explicitly qualified. |
| `security_and_privacy` | Secret handling, PII notes, storage policy, token persistence policy, logging restrictions. |
| `build_handoff` | What external tooling owns: Capacitor setup, Xcode/Android Studio, signing, stores, device QA. |
| `verification_checklist` | App creator / AI / builder checks before implementation. |
| `non_goals` | Boundaries that must not be inferred. |

## Platform target matrix / platform target matrix

The first packet should include this ordered target matrix:

| Priority | Target key | Required now | Role |
| --- | --- | --- | --- |
| 1 | `react_web_capacitor_ios_android` | yes | First implementation/proof target. Wrap the existing web/no-code runtime with Capacitor-style tooling. |
| 1b | `pwa` | optional | Shared web artifact target; useful proof, not a substitute for iOS/Android wrapper validation. |
| 2 | `flutter_input_packet` | no | Later consumer of the same app spec. |
| 3 | `react_native_input_packet` | no | Later consumer when native UI is needed. |
| 4 | `direct_native_generation` | no | Explicit non-goal for now. |

## Minimal screen/action coverage / 最小 screen・action coverage

The first valid handoff packet should cover at least one representative flow:

1. login or authenticated entry expectation;
2. endpoint-backed list screen;
3. detail or form screen;
4. validation display;
5. one safe submit or custom action path;
6. success, validation failure, auth failure, network failure, and unavailable-action states.

The packet may describe more screens, but the first proof should not require broad app completion.

## Native capability declaration / native capability declaration

Native capabilities are declarations, not automatic implementation promises.

Each item should include:

- `capability_key`
- `required` / `optional`
- reason
- consuming screen/action
- suggested plugin/package family if known
- fallback behavior when unavailable
- privacy / permission text requirement

If no native feature is needed, the packet should say:

```json
{
  "native_capabilities": [
    {
      "capability_key": "none",
      "required": false,
      "reason": "First wrapper proof uses web/API behavior only."
    }
  ]
}
```

## Markdown shape / Markdown 形状

`mobile-app-handoff.md` should be readable by an app creator without opening the JSON first:

1. summary and target platform;
2. what is already generated by Mtool;
3. what the mobile builder must implement;
4. auth and endpoint assumptions;
5. screens and actions;
6. validation and error behavior;
7. native capabilities;
8. first proof checklist;
9. non-goals and explicit exclusions.

## Validation checklist / validation checklist

A handoff packet is not ready if any of these are missing:

- schema version;
- source artifact refs/hashes;
- platform target order;
- auth boundary;
- API endpoint refs;
- at least one screen flow;
- action/validation/error mapping;
- native capability declaration, even if `none`;
- security/privacy notes;
- non-goals;
- build/deploy ownership boundary.

The first validator is:

```php
app_mobile_app_handoff_validate(array $packet): array
```

It is side-effect-free and returns `ready`, `blockers`, `warnings`, `validation_version`, and `mutation_performed: false`. The validator treats the following as required for the first slice:

- first platform target is `react_web_capacitor_ios_android`;
- source artifact refs include OpenAPI/API contract, no-code runtime metadata, screen metadata, and auth policy refs with SHA-256 hashes;
- the packet includes list plus detail/form screen intent, one safe submit/custom action, and idempotency for mutating actions;
- success, validation failure, auth failure, network failure, and unavailable-action states are explicit;
- native capabilities are declared, including explicit `none`;
- offline sync is disabled unless a sync contract ref exists;
- direct native generation, app-store signing, offline sync by default, and production user data in packet are explicit non-goals;
- real secrets such as passwords, access tokens, refresh tokens, signing keys, credentials, DSNs, or private certificates are not present.

## Non-goals / 非目標

- Mtool building a production iOS/Android app directly.
- App signing, store submission, device-lab QA, or native certificate management.
- Implementing all native device features.
- Offline sync by default.
- Treating Flutter or React Native as first implementation targets.
- Inferring secrets, tokens, or production user data into the handoff packet.
