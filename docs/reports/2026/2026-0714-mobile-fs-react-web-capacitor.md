# Mobile FS: React/Web + Capacitor-style wrapper

Date: 2026-07-14

## Summary / summary

Layer: Layer A, FE/app framework consumers.

Target: React/Web + Capacitor-style wrapper.

Study key: `react-web-capacitor`.

Recommendation: continue to second pass.

Blocker severity:

- low for handoff feasibility;
- medium for production-app readiness.

This is the best first external FE/app framework target because it is closest to Mtool's current web/no-code/runtime output and to the existing mobile wrapper foundation. The current Mtool artifacts already describe enough of the app boundary to let an external React/Web + Capacitor owner start without Mtool generating a native project.

## Input artifacts / input artifacts

Working location:

```text
work/feasibility-studies/mobile-wrapper/20260714-react-web-capacitor/
```

Generated input artifacts:

| Artifact | Path | SHA-256 |
| --- | --- | --- |
| wrapper target contract | `work/feasibility-studies/mobile-wrapper/20260714-react-web-capacitor/input/c1/wrapper-target-contract.json` | `b5cb9cc59e6dc2d441567ebfd7875f4b0c1bcd244869be989345d5838151abc5` |
| React wrapper app handoff | `work/feasibility-studies/mobile-wrapper/20260714-react-web-capacitor/input/react-wrapper-app/react-wrapper-app-handoff.json` | `9fd150417803eda9b6677b12851abbf2d7c371cb2e825976266582f26c6af5b8` |
| mobile wrapper bundle manifest | `work/feasibility-studies/mobile-wrapper/20260714-react-web-capacitor/input/bundle-manifest/mobile-wrapper-bundle-manifest.json` | `5aaa38b948a90e5ff579c2a1fc137fa8278cedd19a789d003b78d4196b44f5de` |

Validation summary:

```text
work/feasibility-studies/mobile-wrapper/20260714-react-web-capacitor/validation/summary.json
```

## Setup assumptions / setup assumptions

This study does not create a production React app or Capacitor project. It evaluates whether Mtool's current artifacts are sufficient as input for an external owner/tool.

Assumptions:

- Mtool owns app/domain handoff metadata.
- Mtool owns server-side API/action/validation/security contract evidence.
- Mtool owns references to its generated web/no-code/runtime artifacts.
- The external owner/tool owns the React app shell, production component system, app routing, client state management, Capacitor initialization, native project files, signing, build, QA, and store submission.

## Required Mtool artifacts / 必要なMtool成果物

Current artifacts are useful:

- `mobile-app-handoff.json`-derived metadata;
- `wrapper-target-contract.json`;
- `react-wrapper-app-handoff.json`;
- `mobile-wrapper-bundle-manifest.json`;
- source artifact refs and hashes for OpenAPI, no-code runtime, screen metadata, auth policy, and React bridge.

The current handoff already includes:

- auth boundary;
- API boundary;
- screen flow boundary;
- action/mutation boundary;
- native capability boundary;
- offline boundary;
- security boundary;
- non-goals.

## Missing or weak metadata / 不足・弱いmetadata

No hard blocker was found for handoff feasibility. For production-app readiness, the weak areas are:

1. environment/base URL matrix;
2. redirect/deep-link policy details;
3. secure token storage recommendation shape;
4. web build output directory and static asset policy;
5. PWA/runtime readiness facts;
6. API retry/network/offline error policy;
7. Capacitor plugin requirement declaration when native capabilities are needed later.

These should become common requirements or target-specific extension points after the full first pass.

## Auth and token storage / auth・token storage

Current state is good for boundary clarity:

- OIDC mode is explicit.
- login/logout routes are explicit.
- handoff packet does not store tokens.
- secure token storage is explicitly owned by the external wrapper owner.
- redirect/deep-link callback policy is left to external owner configuration.

The next requirement is not for Mtool to implement secure token storage. The next requirement is for Mtool to express the expected storage/security class clearly enough that the external owner/tool can select the correct implementation.

## API, action, validation, and error mapping / API・action・validation・error mapping

Current state is good for first-pass feasibility:

- endpoint count is present;
- standard JSON error envelope is named;
- mutating action is identified;
- idempotency requirement is present;
- server-side authorization, CSRF, idempotency, and Transaction Full gates remain authoritative.

Weakness:

- retry strategy and offline/network error taxonomy are not yet detailed enough for a production client.

## Local storage and offline / local storage・offline

Current state is intentionally conservative:

- offline sync is not enabled by default;
- no production user data is placed in the packet;
- offline behavior requires an explicit sync contract.

This matches the earlier Mtool philosophy: do not over-automate parts that app creators must intentionally design.

## Native and plugin responsibility / native・plugin責務

Current state is correct:

- Mtool does not initialize Capacitor;
- Mtool does not create `ios/` or `android/`;
- Mtool does not manage signing;
- Mtool does not manage store submission;
- native plugins are external owner choices.

This boundary should remain stable unless a later product decision explicitly expands Mtool's responsibility.

## Generated files owned by external tool / 外部tool所有生成物

For this target, the external owner/tool should own:

- `package.json`;
- React app shell;
- React routing/state/component structure;
- SSO/OIDC client configuration;
- secure token storage implementation;
- API client/retry implementation;
- `capacitor.config.*`;
- `ios/`;
- `android/`;
- signing files;
- store submission files.

## Cross-layer dependencies / layer間依存

This Layer A target depends on later Layer C clarification:

- PWA/runtime readiness;
- static asset/build output directory policy;
- native wrapper packaging boundary;
- secure storage/plugin checklist.

It may also benefit from Layer B:

- Codex-style task packet that can guide a user through React/Web + Capacitor setup without guessing core behavior.

These are dependencies, not alternative targets.

## Recommendation / recommendation

Continue to second pass.

React/Web + Capacitor-style wrapper should remain the first external FE/app framework path. It is close enough to current Mtool output to be practical, and it preserves the intended ownership boundary: Mtool emits validated handoff artifacts; external tooling owns app/native generation.

Do not harden `external_no_code` mode yet. First, run at least PWA readiness and Codex-style handoff feasibility, then compare common requirements.
