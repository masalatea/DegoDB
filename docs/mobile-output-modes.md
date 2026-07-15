# Mobile output modes / mobile output mode

English companion: This document defines the supported mobile output modes and keeps production app/native ownership outside Mtool by default.

This document defines Mtool mobile output modes after the external feasibility study pass.

この文書は、external feasibility study 後の Mtool mobile output mode を定義する。

## Principle / 原則

Mtool should support external FE/no-code/app-framework handoff, but it should not silently become the owner of production frontend architecture, native project creation, signing, store submission, or offline sync.

Mtool は外部 FE / No Code / app framework への handoff を支援する。ただし、production frontend architecture、native project 作成、signing、store submission、offline sync を暗黙に所有しない。

## Evaluation basis / 評価根拠

These modes are based on the mobile external feasibility pass.

この mode は mobile external feasibility pass の評価結果に基づく。

Start here for the evaluation index:

- [Mobile External Feasibility Study / mobile external FS](mobile-external-feasibility-study.md)
- [Mobile Ownership Boundaries / mobile ownership boundary](mobile-ownership-boundaries.md)

Key durable reports:

- [2026-0714 Mobile FS Common Requirements](reports/2026/2026-0714-mobile-fs-common-requirements.md)
- [2026-0714 Mobile Output Mode Hardening](reports/2026/2026-0714-mobile-output-mode-hardening.md)

## Modes / mode

| Mode | Meaning | Mtool owns | External owner/tool owns |
| --- | --- | --- | --- |
| `mtool_no_code` | Use Mtool's own generated web/no-code/runtime output as the primary app surface. | generated web/no-code/runtime output, app handoff metadata, runtime validation boundary | native/app framework project only if separately selected |
| `external_no_code` | Emit handoff/input artifacts for an external no-code/app framework/code-builder. | structured input packet, source artifact index, ownership boundary, validation map | app project, source code, dependencies, native project, signing, QA, store submission |
| `hybrid` | Keep Mtool output and also emit external handoff artifacts for selected targets. | Mtool output plus external handoff boundary | external app/project for selected targets |

## Output mode config artifact / output mode config artifact

Mtool can emit a small mode-selection packet before users or AI consumers choose the next artifact path.

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=output-mode-config \
  --output-mode=hybrid \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/output-mode-config
```

The artifact emits only:

```text
output-mode-config.json
OUTPUT-MODE-CONFIG.md
```

Supported `--output-mode` values:

- `mtool_no_code`
- `external_no_code`
- `hybrid`

The config packet records:

- selected mode;
- supported modes;
- selected artifact keys;
- target extension status;
- warnings;
- actions forbidden without explicit confirmation.

It does not generate an app project, install dependencies, initialize native tooling, or overwrite existing app files.

## Required artifacts by mode / mode別必須artifact

### `mtool_no_code`

Required:

- app handoff packet;
- source artifact index;
- runtime/delivery readiness metadata;
- validation command map;
- non-goals.

Optional:

- PWA readiness extension;
- AI/code-builder task packet for guided review.

### `external_no_code`

Required:

- app handoff packet;
- source artifact index;
- ownership boundary;
- selected target extension packet;
- validation command map;
- confirmation-required action list;
- non-goals.

Required when AI/code-builder is selected:

- provider-neutral AI task packet;
- optional provider-specific companion notes.

### `hybrid`

Required:

- all required `mtool_no_code` artifacts;
- all required selected `external_no_code` artifacts;
- canonical-surface statement:
  - which surface is user-facing;
  - which surface is preview/reference;
  - whether both are expected to stay aligned manually.

## Target extensions / target extension

Supported target extension categories:

| Target category | Examples | Contract status |
| --- | --- | --- |
| FE/app framework | React/Web + Capacitor, Flutter, React Native | input packet only; no app project generation by default |
| AI/code-builder | Codex-style, Claude-style | provider-neutral task packet plus optional companion notes |
| delivery/runtime | PWA readiness, native wrapper boundary | metadata/checklist; no native build by default |

## Validation rules / validation rule

Validation should fail or warn when:

- selected mode is unknown;
- selected target has no extension packet;
- source artifact refs are missing;
- source artifact hashes are missing for copied artifacts;
- mutating actions lack idempotency policy;
- token storage policy is not explicit;
- offline sync is requested without sync contract;
- native project generation is implied without explicit confirmation;
- signing/store submission is implied;
- AI/code-builder task packet lacks confirmation-required and forbidden-guess lists.
- output mode is not one of `mtool_no_code`, `external_no_code`, or `hybrid`.

## User-facing wording / UI文言

Recommended labels:

| Mode | Short label | Explanation |
| --- | --- | --- |
| `mtool_no_code` | Mtool web/no-code output | Use Mtool's generated web/no-code/runtime output as the main app surface. |
| `external_no_code` | External app/framework handoff | Generate structured input for an external app framework, no-code tool, or AI builder. |
| `hybrid` | Mtool + external handoff | Keep Mtool output and also prepare external handoff artifacts. |

Recommended warning for external targets:

```text
Mtool will generate handoff/input artifacts only. The external tool or app owner is responsible for app source code, native project files, dependencies, signing, QA, and store submission.
```

Recommended warning for hybrid:

```text
Hybrid mode creates more than one app surface. Mtool does not automatically keep independent frontend implementations synchronized unless a separate sync/contract path is defined.
```

## Long-term direction / 長期方針

Long term, app-surface ownership may move toward external FE/no-code frameworks where that is better for users.

Short-to-mid term, Mtool keeps its own working web/no-code/runtime output and emits external handoff artifacts only where the target has been validated.

## Non-goals / non-goals

These are not implied by any mode:

- direct native iOS/Android generation;
- automatic dependency installation;
- automatic Capacitor/Flutter/React Native project initialization;
- automatic signing/certificate management;
- automatic store submission;
- offline sync by default;
- production user/business data embedded in packets;
- provider-specific AI behavior as the durable core contract.
