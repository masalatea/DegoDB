# Mobile App Handoff / Wrapper Roadmap / mobile app handoff・wrapper roadmap

Date: 2026-07-13
Status: `ACTIVE_AFTER_FIREBIRD_CHECKPOINT`

## Purpose / 目的

Mtool already produces web-facing endpoints, OpenAPI-like contracts, no-code runtime metadata, auth boundaries, validation rules, and action metadata. The mobile roadmap should use those artifacts as inputs for mobile app tools instead of trying to generate complete iOS/Android applications inside Mtool.

Mtool はすでに Web 向け endpoint、OpenAPI 的 contract、No Code runtime metadata、auth boundary、validation rule、action metadata を持っている。mobile roadmap では、Mtool 自身が完全な iOS / Android app を生成するのではなく、それらの artifact を mobile app tool への input として使う。

## Product position / 位置づけ

| Layer | Mtool responsibility / Mtoolの責任 | External tool responsibility / 外部toolの責任 |
| --- | --- | --- |
| Endpoint/API | Export endpoint, OpenAPI, auth, action, validation, and error contracts. / endpoint・OpenAPI・auth・action・validation・error contractを出す。 | Generate or implement client calls. / client callを生成・実装する。 |
| Screen intent | Export screen/layout intent, list/detail/form/action metadata, and navigation hints. / screen・layout intent、list/detail/form/action metadata、navigation hintを出す。 | Render with mobile framework conventions. / mobile frameworkの流儀でrenderする。 |
| Native features | Declare required capabilities such as camera, notification, file, location, or offline storage. / camera・notification・file・location・offline storageなど必要capabilityを宣言する。 | Implement through Capacitor plugin, Flutter package, React Native module, or native code. / Capacitor plugin、Flutter package、React Native module、native codeで実装する。 |
| Build/deploy | Provide reviewable app packet and validation checklist. / review可能なapp packetとvalidation checklistを出す。 | Own Xcode/Android Studio/build signing/store distribution. / Xcode・Android Studio・build signing・store distributionを担う。 |

## Preferred first route / 最初の推奨経路

Start with a creator-friendly mobile handoff spec output, then validate that spec, and only then build a web-wrapper proof. Do not start by coding the React/Capacitor wrapper.

まず React / Capacitor wrapper を書き始めるのではなく、app 作成者が迷わない mobile handoff spec output を出し、その spec を検証してから web-wrapper proof に進む。

This roadmap should be promoted after the Firebird local durable DB profile lane reaches a checkpoint, unless a higher-priority product need appears first.

この roadmap は、より優先度の高い product need が出ない限り、Firebird local durable DB profile lane が checkpoint に達した後に昇格する。

Candidate first route:

1. Produce `mobile-app-handoff.json` and `mobile-app-handoff.md`. / `mobile-app-handoff.json` と `mobile-app-handoff.md` を出す。
2. Validate that endpoint, auth, screen, action, validation, error, platform, and native capability information is complete enough for an app creator or AI agent. / endpoint・auth・screen・action・validation・error・platform・native capability 情報が app 作成者または AI agent に十分か検証する。
3. Define the React/web wrapper target contract from the validated spec. / 検証済みspecから React/Web wrapper target contract を定義する。
4. Prove the Capacitor-like iOS/Android wrapper with login, endpoint call, list/detail, validation display, and one safe submit path. / login、endpoint call、list/detail、validation表示、安全なsubmit pathを1つ持つ Capacitor 系 iOS/Android wrapper を実証する。
5. Keep app-store packaging, signing, native plugin breadth, and offline sync out of the first slice. / app store packaging、signing、native pluginの広範対応、offline syncはfirst sliceから外す。

Capacitor is attractive because it is designed to wrap existing web apps into iOS, Android, and PWA targets. Flutter and React Native remain good downstream targets, but Mtool should first produce structured input packets for them rather than owning their whole codebase.

Capacitor は既存 Web app を iOS / Android / PWA へ持っていく設計なので相性がよい。Flutter と React Native も後続targetとして有力だが、Mtool はそれらのcodebase全体を所有せず、まず structured input packet を出すのがよい。

## Platform priority / platform 優先順位

| Priority | Platform route / platform経路 | Status / 扱い | Reason / 理由 |
| --- | --- | --- | --- |
| 1 | React/web no-code runtime -> Capacitor-like iOS/Android wrapper | First target / first target | Reuses current web/no-code runtime and endpoint contracts; avoids owning native UI generation too early. / 現在のWeb・No Code runtimeとendpoint contractを再利用でき、native UI生成を早く抱えすぎない。 |
| 1b | PWA from the same web artifact | Shared optional target / 共有optional target | Usually falls out of the web-wrapper path; useful as a low-friction proof but not a substitute for iOS/Android wrapper validation. / web-wrapper pathから自然に派生しやすい。低摩擦proofとして有用だが、iOS/Android wrapper検証の代替ではない。 |
| 2 | Flutter app input packet | Later input-packet target / 後続input packet target | Good cross-platform app target, but Mtool should hand off a framework-neutral app spec before generating Dart code. / cross-platform app targetとして有力だが、Dart code生成前にframework-neutral app specを渡すべき。 |
| 3 | React Native app input packet | Later input-packet target / 後続input packet target | Useful if native UI is required, but should consume the same app spec rather than becoming the first path. / native UIが必要な場合に有用だが、first pathではなく同じapp specを消費する位置づけ。 |
| 4 | Direct native iOS/Android generation | Non-goal for now / 当面非目標 | Build signing, store distribution, platform QA, and native feature breadth belong outside Mtool's first supported contract. / build signing・store distribution・platform QA・native feature全般はMtoolのfirst supported contract外。 |

Reference URLs:

- Capacitor: https://capacitorjs.com/
- Flutter: https://flutter.dev/development
- React Native: https://reactnative.dev/

## Roadmap candidate slices / roadmap候補slice

| Order | Candidate / 候補 | Exit condition / 完了条件 |
| --- | --- | --- |
| M1 | Mobile app handoff spec output / mobile app handoff spec output | Emit creator-friendly `mobile-app-handoff.json` and `mobile-app-handoff.md` with endpoint, auth, screens, navigation, actions, validation, error states, platform priority, native capability declarations, and non-goals. |
| M2 | Mobile app handoff spec validation / mobile app handoff spec validation | Validate that the spec is complete and unambiguous enough for an app creator, Codex/Claude, or a mobile builder to proceed without guessing core app behavior. |
| M3 | React/web wrapper target contract / React・Web wrapper target contract | Derive the exact React/web wrapper inputs and constraints from the validated spec; no native build yet. |
| M4 | Capacitor iOS/Android wrapper proof / Capacitor iOS・Android wrapper proof | Existing web/no-code runtime can be wrapped or prepared for iOS/Android with one endpoint-backed list/detail/submit flow; PWA may share the artifact. |
| M5 | Flutter/React Native input packet / Flutter・React Native input packet | Produce framework-neutral app spec variants or guidance that Flutter/React Native/Codex/Claude can consume after the wrapper path is proven. |
| M6 | Native capability boundary / native capability boundary | Camera/notification/file/location/offline needs are declared as metadata, not automatically implemented by Mtool. |
| M7 | Mobile lane checkpoint / mobile lane checkpoint | Decide whether to promote implementation, keep as external-tool handoff, or park. |

## Non-goals / 非目標

- Mtool directly generating full production iOS/Android apps. / Mtool が完全な production iOS / Android app を直接生成すること。
- Owning Xcode/Android Studio setup, signing, store submission, or device-specific QA. / Xcode・Android Studio setup、signing、store submission、device-specific QA をMtoolが所有すること。
- Implementing every native device feature. / 全native device featureを実装すること。
- Replacing existing web no-code runtime. / 既存Web No Code runtimeを置き換えること。
- Treating Flutter, React Native, Capacitor, or native apps as a single mandatory path; only the first target is ordered. / Flutter・React Native・Capacitor・native appを単一必須pathとして扱うこと。順序を固定するのはfirst targetだけ。

## Current decision / 現在の判断

After the Firebird narrow checkpoint, this roadmap is now the active product direction. The first deliverable is the mobile handoff spec output; the first platform proof after that is React/web no-code runtime wrapped for iOS/Android through Capacitor-like tooling.

Firebird narrow checkpoint 後、この roadmap は active product direction になった。最初の deliverable は mobile handoff spec output で、その後の first platform proof は React/Web No Code runtime を Capacitor 系 tooling で iOS/Android wrapper にする方向。

## M1 mobile handoff spec output / M1 mobile handoff spec output

Date: 2026-07-13

Status: `DONE_SPEC_SHAPE`

Added the stable creator-facing spec:

- `docs/mobile-app-handoff-spec.md`

This defines the v1 output shape before wrapper implementation:

- `mobile-app-handoff.json` as the machine-readable packet;
- `mobile-app-handoff.md` as the app-creator-facing explanation and checklist;
- first target: React/Web no-code runtime -> Capacitor-style iOS/Android wrapper;
- optional shared target: PWA;
- later input-packet targets: Flutter and React Native;
- direct native iOS/Android generation remains a non-goal;
- required sections for project/source refs, platform targets, auth, API, screens, navigation, actions, validation, error states, native capabilities, offline/local storage, security/privacy, build handoff, verification checklist, and non-goals.

M1 は wrapper 実装に進む前の spec shape を固定した。次は M2 として、この spec が app 作成者、Codex/Claude、mobile builder にとって十分かを representative fixture で validation する。

## M2 mobile handoff spec validation / M2 mobile handoff spec validation

Date: 2026-07-13

Status: `DONE_VALIDATOR_FIRST_SLICE`

Added the first side-effect-free validator and representative fixture test:

- `mtool/app/mobile_app_handoff.php`
- `tests/Integration/MobileAppHandoffTest.php`

The validator exposes:

```php
app_mobile_app_handoff_validate(array $packet): array
```

The return shape includes `ready`, `validation_version`, `mutation_performed: false`, `blockers`, and `warnings`.

The first slice validates the packet enough for an app creator, Codex/Claude, or a mobile builder to proceed without guessing the core behavior:

- first platform target must be `react_web_capacitor_ios_android`;
- source artifacts must name OpenAPI/API contract, no-code runtime metadata, screen metadata, and auth policy refs with SHA-256 hashes;
- auth, API, app identity, navigation, validation, security/privacy, build handoff, and verification checklist sections are required;
- the screen set must include a list plus a detail/form screen;
- at least one safe submit/custom action is required, and mutating actions must declare idempotency;
- success, validation failure, auth failure, network failure, and unavailable-action states are required;
- native capabilities must be declared even when the value is explicit `none`;
- offline sync stays disabled unless a sync contract ref exists;
- direct native generation, app-store signing, offline sync by default, and production user data in the packet are explicit non-goals;
- real secrets such as passwords, access/refresh/id tokens, credentials, DSNs, signing keys, and certificates are rejected, while policy fields such as `token_storage_policy` and `secret_policy` remain allowed.

Focused verification:

- `php -l mtool/app/mobile_app_handoff.php`
- `php -l tests/Integration/MobileAppHandoffTest.php`
- focused PHPUnit in the sample runtime container: `OK (6 tests, 16 assertions)`

M2 does not generate a wrapper or native app. It closes the ambiguity check before M3, where the React/Web wrapper target contract should define the exact inputs and constraints for a Capacitor-like iOS/Android preparation lane.

## M3 React/web wrapper target contract / M3 React・Web wrapper target contract

Date: 2026-07-13

Status: `DONE_CONTRACT_SHAPE`

Added the permanent wrapper target contract:

- `docs/mobile-react-wrapper-target-contract.md`

This contract bridges validated `mobile-app-handoff.json` packets to the first wrapper target:

```text
react_web_capacitor_ios_android
```

The M3 decision is to keep the existing `NO-CODE-REACT-BRIDGE` artifact as reference evidence and optional input, not as the whole mobile app. The mobile wrapper lane consumes:

- `mobile-app-handoff.json` / `.md`;
- OpenAPI/API contract ref;
- no-code runtime metadata ref;
- screen metadata ref;
- auth policy ref;
- optional `NO-CODE-REACT-BRIDGE/bridge-contract.json`.

The contract explicitly assigns ownership:

- Mtool owns validated metadata, source artifact refs/hashes, endpoint/auth/screen/action/error/native capability metadata, and consumer notes.
- External wrapper owner owns React app shell, routing, frontend state, secure token storage implementation, API client/retry strategy, Capacitor setup, native build configuration, signing, device QA, and store distribution.
- The shared boundary is action intent payload parity, idempotency preservation, user-visible error mapping, native capability declaration, and no offline sync unless an explicit sync contract exists.

M3 remains a contract/documentation slice. It does not initialize a Capacitor project, generate a production React app, add signing support, or enable mobile mutation authority. The next slice is M4: define the narrow Capacitor-style wrapper proof boundary before any implementation-heavy work.

## M4 Capacitor wrapper proof boundary / M4 Capacitor wrapper proof boundary

Date: 2026-07-13

Status: `DONE_PROOF_BOUNDARY`

Added the permanent proof plan:

- `docs/mobile-capacitor-wrapper-proof-plan.md`

M4 splits the mobile wrapper proof into three stages:

| Stage | Meaning | Boundary |
| --- | --- | --- |
| C1 | Wrapper-readiness proof | Mtool validates packet, source refs, React/Web adapter contract, action intent parity, and consumer notes. |
| C2 | Capacitor preparation proof | External owner initializes or updates a Capacitor project and chooses plugins/configuration. |
| C3 | Device/native proof | External owner handles Xcode/Android Studio, simulator/device QA, signing, certificates, stores, and release process. |

The first Mtool-owned target is C1 only. This avoids prematurely turning Mtool into a native mobile build tool.

The selected first candidate is:

```text
sample28-no-code-data-app-mvp
```

Reasons:

- it already has `NO-CODE-RUNTIME`;
- it already has `NO-CODE-REACT-BRIDGE`;
- it already has React bridge build/browser smoke coverage;
- it exercises list/detail/form and action-intent behavior without requiring a production native project.

Named C1 gates:

- `php -l mtool/app/mobile_app_handoff.php`
- focused `MobileAppHandoffTest`
- `make sample28-no-code-react-bridge-build-smoke`
- `make sample28-no-code-react-bridge-browser-smoke`
- `git diff --check`

Next slice: generate or validate a first `mobile-wrapper-target/` C1 package containing `wrapper-target-contract.json`, `WRAPPER-CONSUMER-NOTES.md`, and source artifact refs from the validated mobile handoff packet.

## M5 mobile wrapper target C1 package first slice / M5 mobile wrapper target C1 package first slice

Date: 2026-07-13

Status: `DONE_IN_MEMORY_PACKAGE_BUILDER`

Added the first side-effect-free C1 package builder and focused tests:

- `mtool/app/mobile_wrapper_target.php`
- `tests/Integration/MobileWrapperTargetTest.php`

The builder exposes:

```php
app_mobile_wrapper_target_build_c1_package(array $handoff): array
```

It validates the input handoff packet first using `app_mobile_app_handoff_validate()`. If the handoff is not ready, no package is built. If it is ready, it returns an in-memory package with:

- `wrapper-target-contract.json`
- `WRAPPER-CONSUMER-NOTES.md`

The first contract shape includes:

- `contract_schema_version: mobile-react-wrapper-target-v1`;
- `target_key: react_web_capacitor_ios_android`;
- `proof_stage: C1_WRAPPER_READINESS`;
- source artifact refs copied from the validated handoff;
- React bridge availability/ref when present;
- Capacitor boundary declaring Mtool owns C1 only and external owner owns C2/C3;
- auth/API/screen/action/native/offline/security boundaries;
- C1 verification gates;
- non-goals from the handoff packet.

Focused verification:

- `php -l mtool/app/mobile_wrapper_target.php`
- `php -l tests/Integration/MobileWrapperTargetTest.php`
- focused PHPUnit in the sample runtime container: `OK (4 tests, 31 assertions)`

M5 still performs no file writes, no Capacitor initialization, no native build, no signing, and no app submission. The next slice is M6: emit the same C1 package to a controlled artifact directory without touching a user/native project.

## M6 mobile wrapper target artifact emission / M6 mobile wrapper target artifact emission

Date: 2026-07-13

Status: `DONE_CONTROLLED_EMITTER`

Extended the C1 package helper with controlled file emission:

```php
app_mobile_wrapper_target_emit_c1_package(array $handoff, string $targetDir): array
```

The emitter:

- validates the handoff before writing;
- writes only `wrapper-target-contract.json` and `WRAPPER-CONSUMER-NOTES.md`;
- creates the target artifact directory when needed;
- refuses unsafe relative paths and existing package files;
- does not create `package.json`, `capacitor.config.ts`, `ios/`, or `android/`;
- does not initialize React, Capacitor, iOS, or Android projects.

Focused verification:

- `php -l mtool/app/mobile_wrapper_target.php`
- `php -l tests/Integration/MobileWrapperTargetTest.php`
- focused PHPUnit in the sample runtime container: `OK (7 tests, 47 assertions)`

Next slice: connect this controlled emitter to the first candidate sample28 artifact proof.

## M7 sample28 mobile wrapper target artifact integration / M7 sample28 mobile wrapper target artifact integration

Date: 2026-07-13

Status: `DONE_SAMPLE28_C1_HELPER`

Added sample28-oriented C1 helper functions:

```php
app_mobile_wrapper_target_sample28_c1_handoff(): array
app_mobile_wrapper_target_emit_sample28_c1_package(string $targetDir): array
```

This creates a sample28 proof packet for:

- `NO-CODE-RUNTIME` runtime preview;
- `NO-CODE-RUNTIME` screen definition;
- optional `NO-CODE-REACT-BRIDGE` bridge contract;
- SSO/user standard reference;
- React/Web + Capacitor-style first target.

The emitted package still contains only:

- `wrapper-target-contract.json`
- `WRAPPER-CONSUMER-NOTES.md`

It does not create a React app, `package.json`, `capacitor.config.ts`, `ios/`, `android/`, signing assets, or store-submission files.

Focused verification:

- `php -l mtool/app/mobile_wrapper_target.php`
- `php -l tests/Integration/MobileWrapperTargetTest.php`
- focused PHPUnit in the sample runtime container: `OK (9 tests, 65 assertions)`

Next decision: either wire this package into a user-facing CLI/source-output route, or checkpoint/park the mobile wrapper lane with C1 evidence complete.

## M8 mobile wrapper target lane checkpoint / M8 mobile wrapper target lane checkpoint

Date: 2026-07-13

Status: `DONE_C1_CHECKPOINT_PARK_CLI_ROUTE`

Decision:

The mobile wrapper lane is checkpointed at C1 wrapper-readiness. Do not add a CLI/source-output route yet.

Reason:

- The C1 evidence is complete enough to prove the intended boundary: Mtool can define, validate, build, and emit a mobile wrapper readiness package without touching native projects.
- A user-facing CLI/source-output route would turn the proof into a product surface and needs separate adoption UX decisions.
- C2/C3 work remains external-owner scope: Capacitor setup, React app shell hardening, native build, signing, stores, device QA, and secure token storage implementation.
- Flutter and React Native remain later input-packet targets; they should consume the same validated app/mobile handoff idea rather than becoming first implementation paths.

Completed C1 evidence:

- `docs/mobile-app-handoff-spec.md`
- `docs/mobile-react-wrapper-target-contract.md`
- `docs/mobile-capacitor-wrapper-proof-plan.md`
- `app_mobile_app_handoff_validate()`
- `app_mobile_wrapper_target_build_c1_package()`
- `app_mobile_wrapper_target_emit_c1_package()`
- `app_mobile_wrapper_target_sample28_c1_handoff()`
- `app_mobile_wrapper_target_emit_sample28_c1_package()`
- focused and full PHPUnit evidence recorded in the M2/M5/M6/M7 sections above.

Reopen condition:

Reopen this lane when one of these is true:

- a user needs a visible Mtool source-output route for `mobile-wrapper-target/`;
- a CLI command is needed to emit wrapper packages in real projects;
- a downstream Codex/Claude/mobile-builder workflow needs a stable file package rather than helper functions;
- Flutter or React Native input-packet support becomes the selected next platform slice.
