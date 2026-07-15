# Mobile ownership boundaries / mobile ownership boundary

English companion: This document defines which mobile/app handoff responsibilities belong to Mtool, external tools, app owners, or future parked scope.

This document defines the ownership boundaries for Mtool mobile/app handoff.

この文書は、Mtool mobile / app handoff の ownership boundary を定義する。

## Purpose / 目的

The boundary must be explicit so Mtool, AI builders, external app frameworks, and app creators do not silently take over each other's responsibilities.

Mtool、AI builder、外部 app framework、app 作成者が互いの責務を暗黙に引き受けないように、境界を明確にする。

Related references:

- [Mobile External Feasibility Study / mobile external FS](mobile-external-feasibility-study.md)
- [Mobile Output Modes / mobile output modes](mobile-output-modes.md)
- [Mobile Artifact Execution UI Policy / mobile artifact execution UI policy](mobile-artifact-execution-ui-policy.md)
- [2026-0714 Mobile FS Common Requirements](reports/2026/2026-0714-mobile-fs-common-requirements.md)

## Boundary classes / 境界区分

| Class | Meaning | Default decision |
| --- | --- | --- |
| Mtool-owned | Mtool may generate, validate, document, and version these artifacts. | In scope. |
| External owner/tool-owned | External FE/no-code/app framework, AI builder, or app owner owns these outputs. | Out of Mtool generation scope by default. |
| User-confirmation required | Possible, but only after explicit user/project approval. | Not implicit. |
| Forbidden without explicit artifact | Must not be invented by Mtool or AI. | Fail closed. |
| Parked/new product scope | Not part of current supported scope. | Reopen only through new plan. |

## Mtool-owned by default / Mtool default 所有

Mtool owns these by default:

| Area | Mtool-owned artifacts / responsibilities |
| --- | --- |
| Handoff contract | app handoff packet, schema version, project/app identity, non-goals |
| Source artifact index | OpenAPI ref/hash, no-code runtime ref/hash, screen metadata ref/hash, auth policy ref/hash, bridge/runtime refs |
| App behavior metadata | screens, navigation, endpoints, actions, validation/error categories |
| Server authority metadata | authorization boundary, CSRF boundary, idempotency, Transaction Full statement |
| Ownership boundary metadata | Mtool-owned list, external-owner list, confirmation-required list, not-generated list |
| Target extension packets | React/Web + Capacitor extension, Flutter extension, React Native extension, PWA/runtime extension, native wrapper boundary extension |
| AI/code-builder support | provider-neutral task packet, optional provider companion notes, questions-to-ask, forbidden-guess list |
| Validation | validation command map, schema checks, source artifact hash checks, selected-target readiness checks |
| Bundle/index | bundle manifest, artifact order, target refs, validation summary refs |
| Read-only UI | guidance page, artifact descriptions, CLI hints, non-execution warnings |

Mtool may improve these artifacts without becoming the owner of app source code or native project execution.

## External owner/tool-owned by default / 外部owner default 所有

External owner/tool owns these by default:

| Area | External-owned artifacts / responsibilities |
| --- | --- |
| Production frontend | React app shell, Flutter app, React Native app, production component system |
| Frontend architecture | routing library, state management, form binding, design system, theming implementation |
| Client implementation | API client, retry strategy, OIDC client, secure token storage implementation |
| Framework project | `package.json`, Flutter project, React Native project, Capacitor project |
| Native project | `ios/`, `android/`, native modules/plugins, native permissions |
| Delivery/build | dependency installation, web build, native build, simulator/device QA |
| Release | app IDs, signing certificates, provisioning, store submission |
| Runtime operations | production deployment, monitoring, external app rollback/cleanup |

Mtool can describe the expected boundary or required metadata, but does not create or own these by default.

## User-confirmation required / user確認必須

These actions require explicit user/project confirmation:

| Action | Why confirmation is required |
| --- | --- |
| Create an external app project | Creates a new codebase and dependency surface. |
| Install dependencies | Mutates environment and may download external code. |
| Initialize Capacitor / Flutter / React Native | Creates project-specific files and long-lived ownership. |
| Create or overwrite files outside Mtool artifact roots | Can affect user project source. |
| Choose persistent token storage | Security-sensitive app behavior. |
| Choose OIDC client/deep-link/callback behavior | Security and deployment-specific behavior. |
| Add native plugins/modules | Platform permission and build-risk decision. |
| Enable offline storage or sync | Data consistency and conflict-resolution decision. |
| Configure signing/store submission | Release/legal/account responsibility. |
| Add UI-triggered artifact execution | Requires CSRF, output-dir, overwrite, audit, and failure controls. |

AI/code-builder packets should expose this list as `requires_user_confirmation`.

## Forbidden without explicit artifact / 明示artifactなしは禁止

Mtool and AI consumers must not invent:

| Behavior | Required explicit artifact |
| --- | --- |
| Offline sync | sync contract |
| Local persistent business-data storage | app data storage policy |
| Refresh-token persistence | auth/token storage policy |
| Native plugin selection | native capability/plugin policy |
| App signing | release/signing policy |
| Store submission | release/store policy |
| Production frontend architecture | selected frontend architecture policy |
| External app overwrite | overwrite approval or output policy |
| Automatic dependency installation | dependency/install approval |
| Provider-specific AI behavior as core contract | provider-neutral task packet plus companion note |

If the explicit artifact is absent, validation should fail closed or require user confirmation.

## Output mode boundary / output mode別境界

| Mode | Mtool owns | External owner owns | Confirmation-sensitive edge |
| --- | --- | --- | --- |
| `mtool_no_code` | Mtool web/no-code/runtime output, app handoff, runtime readiness, validation | Native/app framework project only if separately selected | PWA/offline/storage choices |
| `external_no_code` | Handoff/input artifacts, target extension, ownership boundary, validation map | App project, source code, dependencies, native project, signing, QA, store submission | Project creation, dependencies, token storage, native plugins |
| `hybrid` | Mtool output plus external handoff boundary | External app/project for selected targets | Which surface is canonical; avoiding false sync claims |

## Layer boundary / layer別境界

| Layer | Mtool role | Not Mtool's default role |
| --- | --- | --- |
| Layer A: FE/app framework | Emit framework input packets and extension metadata. | Generate production React/Flutter/React Native apps. |
| Layer B: AI/code-builder | Emit provider-neutral task packet and validation rules. | Let AI guess project choices or perform unsafe writes silently. |
| Layer C: delivery/runtime | Emit PWA/runtime/native-wrapper readiness metadata and checklist. | Build/sign/submit native apps or enable offline sync automatically. |

## UI boundary / UI境界

Current UI boundary:

- read-only guide route is allowed;
- artifact descriptions are allowed;
- CLI hints are allowed;
- generation buttons are not allowed yet.

Execution UI requires:

- CSRF protection;
- authentication and authorization;
- output directory allow-list;
- overwrite policy;
- dry-run/preview mode;
- audit log;
- failure/partial-output handling;
- validation after generation.

## Validation rule / validation rule

Validation should check boundary violations explicitly:

- selected mode is known;
- selected target extension exists;
- external-owned files are not generated unless explicitly authorized;
- confirmation-required actions are listed;
- forbidden behaviors have required artifacts;
- native project/signing/store generation is not implied;
- offline sync is disabled unless a sync contract exists;
- AI task packet includes `allowed_without_confirmation`, `requires_user_confirmation`, and `forbidden_without_artifact`.

## Reopen rule / 再開rule

Any expansion beyond this boundary is new product scope.

Examples:

- generating production Flutter source;
- initializing Capacitor projects from UI;
- managing signing;
- app store submission;
- automatic offline sync.

Reopen only with a concrete plan, owner, validation gate, and rollback/safety policy.
