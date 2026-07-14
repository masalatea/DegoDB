# Mobile Capacitor Wrapper Proof Plan / mobile Capacitor wrapper proof plan

English companion:
This document defines the M4 proof boundary for the first mobile wrapper lane. It intentionally separates wrapper-readiness from native project generation. The first proof should show that Mtool's validated mobile handoff and React/Web adapter artifacts are sufficient inputs for a Capacitor-style iOS/Android wrapper owner.

この文書は、first mobile wrapper lane の M4 proof boundary です。wrapper-readiness と native project generation を意図的に分けます。first proof では、Mtool の検証済み mobile handoff と React/Web adapter artifact が Capacitor 系 iOS/Android wrapper owner に渡せる input になっていることを確認します。

## Proof split / proof分割

| Stage | Name | Mtool owns | External owner owns |
| --- | --- | --- | --- |
| C1 | Wrapper-readiness proof | Validate packet, source refs, React/Web adapter contract, action intent parity, and consumer notes. | Review package and decide whether to create a native wrapper. |
| C2 | Capacitor preparation proof | Provide input package and expected checks. | Initialize or update Capacitor project, choose plugins, configure app IDs, run web build/sync. |
| C3 | Device/native proof | Provide metadata and non-goal boundaries only. | Xcode/Android Studio build, simulator/device QA, signing, certificates, stores. |

Mtool should complete C1 first. C2 and C3 are not automatic Mtool responsibilities.

## C1 target / C1 target

C1 proves that the following are true:

1. `mobile-app-handoff.json` is valid and blocker-free.
2. `wrapper-target-contract.json` can be derived from the validated handoff packet.
3. The React/Web adapter reference is either present or explicitly absent with a reason.
4. Existing no-code runtime and React bridge smokes still prove the web/action-intent surface.
5. Consumer notes explain what the external wrapper owner must inspect before starting Capacitor work.
6. No native build, signing, certificate, store credential, or production user data is required.

The first C1 builder is side-effect-free:

```php
app_mobile_wrapper_target_build_c1_package(array $handoff): array
```

It returns `wrapper-target-contract.json` and `WRAPPER-CONSUMER-NOTES.md` in memory.

The first controlled file-emission helper is:

```php
app_mobile_wrapper_target_emit_c1_package(array $handoff, string $targetDir): array
```

It writes only `wrapper-target-contract.json` and `WRAPPER-CONSUMER-NOTES.md` into the target artifact directory, refuses to overwrite existing files, and does not create `package.json`, `capacitor.config.ts`, `ios/`, or `android/`.

## Suggested C1 input package / C1 input package

```text
mobile-wrapper-target/
  wrapper-target-contract.json
  WRAPPER-CONSUMER-NOTES.md
  source-artifacts/
    mobile-app-handoff.json
    mobile-app-handoff.md
    openapi.json
    no-code-runtime.json
    screen-definition.json
    auth-policy.json
    bridge-contract.json        # optional, when NO-CODE-REACT-BRIDGE exists
```

For the first implementation slice, the package can be generated in `work/source-outputs/{PROJECT_KEY}/{SOURCE_OUTPUT_KEY}/mobile-wrapper-target/` or an equivalent artifact directory. It should not write into a user-owned production React/Capacitor project without explicit approval.

## Validation gates / validation gates

C1 should pass these gates:

```text
php -l mtool/app/mobile_app_handoff.php
focused MobileAppHandoffTest
make sample28-no-code-react-bridge-build-smoke
make sample28-no-code-react-bridge-browser-smoke
git diff --check
```

The React bridge smokes are not a native app proof. They prove that the React/Web side can consume the generated contract and emit action intents before a Capacitor owner wraps it.

## C2 handoff checklist / C2 handoff checklist

If an external wrapper owner proceeds to C2, they should confirm:

- app display name and bundle/package ID placeholders;
- login/logout/deep-link assumptions;
- API base URL and environment switching policy;
- secure token storage approach;
- CORS/same-origin or mobile API access policy;
- list/detail/form navigation mapping;
- action intent to API adapter mapping;
- idempotency behavior for mutating actions;
- native capability/plugin needs;
- offline sync remains disabled unless a sync contract exists;
- web build output path and Capacitor `webDir`;
- ownership of app icons, splash assets, signing, certificates, and store credentials.

## C3 stays outside Mtool / C3 は Mtool 外

C3 includes:

- iOS simulator/device build;
- Android emulator/device build;
- native permission prompts;
- secure storage plugin validation;
- App Store / Play Store signing and submission;
- production monitoring, crash reporting, and release process.

These are important, but they belong to the app owner or mobile builder.

## First candidate sample / first candidate sample

The first C1 proof should reuse an existing no-code app sample with React bridge evidence:

```text
sample28-no-code-data-app-mvp
```

Reason:

- it already has `NO-CODE-RUNTIME`;
- it already has `NO-CODE-REACT-BRIDGE`;
- it already has build/browser smoke coverage;
- it exercises list/detail/form and action-intent behavior without requiring a production native project.

Sample29 or Sample31 can be used later to prove the same handoff pattern outside the ticket domain.

## Exit condition / 完了条件

M4 is complete when the C1 proof boundary is explicit and reviewable:

- C1/C2/C3 ownership split is documented;
- first candidate sample is selected;
- validation gates are named;
- native build/signing/store work is excluded from Mtool's first proof;
- the next implementation step can generate or validate a `mobile-wrapper-target/` artifact without guessing the boundary.
