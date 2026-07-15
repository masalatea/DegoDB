# Mobile FS: PWA readiness

Date: 2026-07-14

## Summary / summary

Layer: Layer C, delivery/runtime mode consumers.

Target: PWA readiness for Mtool web/no-code output.

Study key: `pwa-readiness`.

Recommendation: continue as a Layer C dependency.

Blocker severity: medium until runtime readiness metadata exists.

PWA readiness should not be treated as a FE framework alternative to React/Web + Capacitor, Flutter, or React Native. It is a delivery/runtime capability that can support Mtool's existing web/no-code output, React/Web wrapper paths, and possible `mtool_no_code` / `hybrid` modes.

## Input artifacts / input artifacts

Working location:

```text
work/feasibility-studies/mobile-wrapper/20260714-pwa-readiness/
```

Generated input artifacts:

| Artifact | Path | SHA-256 |
| --- | --- | --- |
| wrapper target contract | `work/feasibility-studies/mobile-wrapper/20260714-pwa-readiness/input/c1/wrapper-target-contract.json` | `b5cb9cc59e6dc2d441567ebfd7875f4b0c1bcd244869be989345d5838151abc5` |
| React wrapper app handoff | `work/feasibility-studies/mobile-wrapper/20260714-pwa-readiness/input/react-wrapper-app/react-wrapper-app-handoff.json` | `9fd150417803eda9b6677b12851abbf2d7c371cb2e825976266582f26c6af5b8` |
| mobile wrapper bundle manifest | `work/feasibility-studies/mobile-wrapper/20260714-pwa-readiness/input/bundle-manifest/mobile-wrapper-bundle-manifest.json` | `5aaa38b948a90e5ff579c2a1fc137fa8278cedd19a789d003b78d4196b44f5de` |

Validation summary:

```text
work/feasibility-studies/mobile-wrapper/20260714-pwa-readiness/validation/summary.json
```

## Setup assumptions / setup assumptions

This study does not generate a PWA implementation. It checks whether Mtool should express PWA readiness as metadata and checklist.

Assumptions:

- Mtool may own a PWA readiness declaration for its generated web/no-code/runtime outputs.
- Mtool may expose static asset/runtime references needed by a PWA-capable external tool.
- Mtool should not silently enable offline sync.
- Mtool should not own app store signing or native packaging through PWA readiness.

## Required Mtool artifacts / 必要なMtool成果物

Current artifacts are useful but incomplete:

- web/no-code runtime refs;
- screen metadata refs;
- auth policy ref;
- API/action/error boundary;
- non-goals and external ownership boundary.

Additional PWA-specific metadata likely needed:

- app manifest metadata:
  - app name;
  - short name;
  - icons;
  - theme/background colors;
  - start URL;
  - display mode;
- service worker/cache policy:
  - cacheable static assets;
  - non-cacheable API endpoints;
  - update strategy;
- installability checklist;
- browser storage class:
  - whether local persistent storage is allowed;
  - whether sensitive data is forbidden;
  - whether IndexedDB/localStorage/sessionStorage are allowed;
- offline behavior:
  - offline view-only;
  - offline disabled;
  - explicit sync contract required.

## Missing or weak metadata / 不足・弱いmetadata

The current mobile wrapper artifacts do not yet declare:

1. web app manifest fields;
2. service worker scope and cache policy;
3. asset build directory;
4. static asset versioning;
5. installability requirements;
6. browser storage class;
7. explicit PWA offline mode;
8. API cacheability and stale-data policy.

These are not native-app blockers, but they are necessary for a reliable PWA or React/Web + Capacitor preparation path.

## Auth and token storage / auth・token storage

Current mobile wrapper metadata correctly avoids storing tokens in packets. For PWA readiness, Mtool should also express:

- whether browser persistent token storage is allowed;
- whether refresh tokens are forbidden in browser storage;
- whether session-only storage is required;
- whether secure storage is delegated to a native wrapper plugin when packaged.

Mtool does not need to choose the final token storage implementation for every external framework. It should expose the security expectation.

## API, action, validation, and error mapping / API・action・validation・error mapping

PWA readiness needs a stronger cacheability/error distinction than the current mobile wrapper packet:

- read-only API endpoints may be cacheable only when explicitly marked;
- mutating endpoints must remain online/server-authoritative unless an explicit sync contract exists;
- validation errors should never be cached as successful state;
- authentication failures must trigger re-auth flow;
- network failures should map to explicit unavailable/offline UI states.

## Local storage and offline / local storage・offline

Current state is intentionally conservative and should remain so:

- offline sync is disabled by default;
- production user data is not embedded in packets;
- local storage of user data requires explicit app-level decision;
- sync requires a separate contract.

Recommended PWA readiness modes:

| Mode | Meaning |
| --- | --- |
| `pwa_disabled` | No PWA metadata emitted. |
| `pwa_installable_online_only` | App can be installable, but online API behavior remains required. |
| `pwa_static_cache_only` | Static shell/assets can be cached, but business data is online-only. |
| `pwa_sync_contract_required` | Offline data mutation/read support is blocked until an explicit sync contract exists. |

These modes should be considered during MW-11 common requirement extraction, not hardened immediately.

## Native and plugin responsibility / native・plugin責務

PWA itself does not require native project ownership. If the same web output is later wrapped by Capacitor:

- native plugins remain external owner choices;
- signing remains external owner responsibility;
- app store submission remains external owner responsibility;
- secure native storage plugin selection remains external owner responsibility unless Mtool defines a future explicit support scope.

## Cross-layer dependencies / layer間依存

Layer A dependency:

- React/Web + Capacitor benefits from PWA/static runtime readiness metadata because it clarifies build output, asset scope, cache policy, and browser storage assumptions.

Layer B dependency:

- AI/code-builder packets should include PWA readiness facts so an AI builder does not invent manifest/service-worker/offline behavior.

## Recommendation / recommendation

Continue as a Layer C dependency.

PWA readiness is worthwhile, but it should be represented as metadata/checklist rather than a separate app-generation lane. It should feed both:

- Mtool's own web/no-code/runtime output;
- React/Web + Capacitor-style wrapper handoff.

Do not harden PWA output modes yet. Carry the missing PWA metadata into MW-11 common requirement extraction after more first-pass studies.
