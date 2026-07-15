# Mobile FS: React Native input packet

Date: 2026-07-14

## Summary / summary

Layer: Layer A, FE/app framework consumers.

Target: React Native input packet.

Study key: `react-native`.

Recommendation: continue to second pass as a later platform target.

Blocker severity: medium.

The current React Native input packet is useful as a framework-neutral app contract carrier. It includes the same shared app contract as Flutter: auth, API, screen flow, action/mutation, idempotency, implementation guidance, non-goals, and ownership boundary. It is not yet sufficient to generate a production React Native app without additional platform extension metadata.

This matches the intended product boundary: Mtool emits structured input packets, while an external React Native owner/tool owns source code, native project files, signing, build, QA, and store submission.

## Input artifacts / input artifacts

Working location:

```text
work/feasibility-studies/mobile-wrapper/20260714-react-native/
```

Generated input artifacts:

| Artifact | Path | SHA-256 |
| --- | --- | --- |
| React Native input packet | `work/feasibility-studies/mobile-wrapper/20260714-react-native/input/platform-input-packets/react-native-input-packet.json` | `38d483e234649bf1a52a3227a2b1e7ee3701d8906571447d40985ed0c08f8cee` |
| mobile wrapper bundle manifest | `work/feasibility-studies/mobile-wrapper/20260714-react-native/input/bundle-manifest/mobile-wrapper-bundle-manifest.json` | `5aaa38b948a90e5ff579c2a1fc137fa8278cedd19a789d003b78d4196b44f5de` |

Validation summary:

```text
work/feasibility-studies/mobile-wrapper/20260714-react-native/validation/summary.json
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

These are enough for first-pass React Native feasibility.

## Missing or weak metadata / 不足・弱いmetadata

React Native-specific readiness needs additional extension metadata:

1. navigation library expectation;
2. state management expectation;
3. form binding and validation display mapping;
4. component/design-token mapping;
5. API client package expectation;
6. OIDC/auth package expectation;
7. secure storage module expectation;
8. native module/plugin policy;
9. iOS/Android permission mapping;
10. environment/build variant mapping;
11. Expo versus bare React Native boundary, if relevant.

Without these, a React Native builder can consume the app contract but must still make many architecture and platform choices.

## Auth and token storage / auth・token storage

The packet correctly states that token storage is downstream owner responsibility. For React Native, this must remain explicit because token storage often implies native module/package choices.

Second-pass candidates:

- storage security class;
- refresh-token allowance;
- secure storage module requirement;
- deep-link/callback handling;
- Expo/bare compatibility boundary.

## API, action, validation, and error mapping / API・action・validation・error mapping

The shared contract is sufficient for first-pass feasibility:

- endpoint count is present;
- mutating action is identified;
- idempotency is required;
- server-side authority is explicit.

React Native extension metadata should eventually add:

- typed request/response model hints;
- validation message binding;
- retry/network error category mapping;
- loading/empty/error state mapping;
- mobile-specific unavailable-state handling.

## Local storage and offline / local storage・offline

Offline sync should stay disabled unless an explicit sync contract exists. React Native does not change this rule.

Because React Native usually has easier access to native storage than web output, the handoff should explicitly forbid adding offline mutation, background sync, or local business-data persistence without a sync contract.

## Native and plugin responsibility / native・plugin責務

Current boundary is correct:

- Mtool does not generate React Native source;
- Mtool does not generate iOS/Android projects;
- Mtool does not manage signing;
- Mtool does not manage store submission;
- plugin/package choices are downstream owner responsibility.

## Recommendation / recommendation

Continue to second pass, but keep React Native as a later platform target.

The current packet proves that Mtool can carry the shared app contract into React Native-oriented handoff. The next work is not React Native app generation; it is identifying which React Native-specific extension fields are common enough to standardize.
