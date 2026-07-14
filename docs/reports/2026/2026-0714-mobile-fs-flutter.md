# Mobile FS: Flutter input packet

Date: 2026-07-14

## Summary / summary

Layer: Layer A, FE/app framework consumers.

Target: Flutter input packet.

Study key: `flutter`.

Recommendation: continue to second pass as a later platform target.

Blocker severity: medium.

The current Flutter input packet is useful as a framework-neutral app contract carrier. It already contains auth, API, screen flow, action/mutation, idempotency, implementation guidance, non-goals, and ownership boundary. It is not yet sufficient to generate a production Flutter app without additional platform extension metadata.

This is the right shape for the current stage: Mtool should provide structured input only, not Dart source code or native project files.

## Input artifacts / input artifacts

Working location:

```text
work/feasibility-studies/mobile-wrapper/20260714-flutter/
```

Generated input artifacts:

| Artifact | Path | SHA-256 |
| --- | --- | --- |
| Flutter input packet | `work/feasibility-studies/mobile-wrapper/20260714-flutter/input/platform-input-packets/flutter-input-packet.json` | `5667f0f3113ba35b740b8268a20d4ea83b3db9088f98ca43e320a69bba739b5d` |
| mobile wrapper bundle manifest | `work/feasibility-studies/mobile-wrapper/20260714-flutter/input/bundle-manifest/mobile-wrapper-bundle-manifest.json` | `5aaa38b948a90e5ff579c2a1fc137fa8278cedd19a789d003b78d4196b44f5de` |

Validation summary:

```text
work/feasibility-studies/mobile-wrapper/20260714-flutter/validation/summary.json
```

## Required Mtool artifacts / 必要なMtool成果物

Current useful fields:

- source artifact refs and hashes;
- shared app contract;
- OIDC/auth mapping;
- API mapping;
- screen flow mapping;
- action/mutation mapping;
- idempotency requirement;
- implementation guidance;
- non-goals;
- `not_generated_by_mtool`.

These are enough for a first-pass Flutter feasibility handoff.

## Missing or weak metadata / 不足・弱いmetadata

Flutter-specific readiness needs additional extension metadata:

1. widget/layout intent;
2. navigation stack model;
3. state management expectation;
4. form validation binding;
5. theming/design-token mapping;
6. HTTP/client package expectation;
7. OIDC/client package expectation;
8. secure storage plugin expectation;
9. platform permission mapping;
10. build flavor/environment mapping;
11. test/smoke command expectations.

Without these, a Flutter builder can consume the app contract but must still make many architecture choices.

## Auth and token storage / auth・token storage

The packet correctly avoids token storage and leaves secure storage to the downstream app owner. For Flutter, the second pass should decide whether Mtool should express:

- storage security class;
- whether refresh tokens are allowed;
- whether browser-like web mode is in scope;
- whether native secure storage is expected for iOS/Android;
- whether package choice remains fully external.

Mtool should not select Flutter packages automatically at this stage.

## API, action, validation, and error mapping / API・action・validation・error mapping

The shared contract is sufficient for first-pass feasibility:

- endpoint count is present;
- mutating action is identified;
- idempotency is required;
- server-side authority is explicit.

The Flutter extension needs:

- typed request/response model hints;
- validation message binding;
- retry/network error category mapping;
- loading/empty/error state mapping for list/detail/form screens.

## Local storage and offline / local storage・offline

Offline sync should stay disabled unless an explicit sync contract exists. Flutter does not change this rule.

The packet should eventually express whether the app may store:

- non-sensitive view cache;
- user-entered draft form data;
- authenticated user profile data;
- server business data.

For now, this remains a second-pass common requirement candidate.

## Native and plugin responsibility / native・plugin責務

Current boundary is correct:

- Mtool does not generate Dart source;
- Mtool does not generate iOS/Android projects;
- Mtool does not manage signing;
- Mtool does not manage store submission;
- plugin/package choices are downstream owner responsibility.

## Recommendation / recommendation

Continue to second pass, but keep Flutter as a later platform target.

The current packet proves that Mtool can carry the shared app contract into Flutter-oriented handoff. The next work is not Flutter app generation; it is identifying which Flutter-specific extension fields are common enough to standardize.
