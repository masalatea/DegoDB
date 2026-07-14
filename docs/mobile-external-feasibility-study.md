# Mobile External Feasibility Study / mobile external FS

English companion: This document defines how Mtool evaluates external FE/no-code/app consumers and where those feasibility results are stored.

This document defines how to run and store feasibility studies for external FE/no-code/app consumers of Mtool output.

この文書は、Mtool output を外部 FE / No Code / app consumer に渡す feasibility study の進め方と置き場所を定義する。

## Purpose / 目的

The long-term direction is to move frontend/app-surface ownership toward external FE/no-code frameworks because that is not Mtool's core responsibility.

長期的には frontend / app surface の所有を外部 FE / No Code framework へ寄せる。これは Mtool の中核責務ではない。

However, this is not an immediate full migration. The first work is feasibility-first:

1. compare several external consumers;
2. keep comparisons inside the same layer;
3. record cross-layer dependencies separately;
4. extract common requirements in a second pass;
5. harden output modes only after the common requirements are known.

ただし、すぐ完全移行するわけではない。最初は feasibility-first とする。

## Current evaluation result / 現在の評価結果

The first external mobile feasibility pass is complete for the current scope.

現在 scope の external mobile feasibility pass は完了済み。

Overall result:

- Feasible: Mtool can support external FE/no-code/app-builder handoff.
- Recommended boundary: Mtool should own handoff contracts, source artifact indexes, ownership boundaries, validation maps, target extension packets, and AI/code-builder task packets.
- Not recommended as Mtool-owned default: production frontend architecture, native project generation, signing, store submission, dependency installation, token-storage choice, and offline sync.

全体評価:

- 実現性あり: Mtool は外部 FE / No Code / app builder への handoff を支援できる。
- 推奨境界: Mtool は handoff contract、source artifact index、ownership boundary、validation map、target extension packet、AI / code-builder task packet を所有する。
- Mtool default 所有として非推奨: production frontend architecture、native project generation、signing、store submission、dependency installation、token-storage 選択、offline sync。

Evaluation index:

| Scope | Result | Report |
| --- | --- | --- |
| Target matrix | Layered FS targets and storage paths are defined. | [2026-0714 mobile FS target matrix](reports/2026/2026-0714-mobile-fs-target-matrix.md) |
| React/Web + Capacitor | Best first external FE/app framework candidate; continue. | [2026-0714 mobile FS React/Web + Capacitor](reports/2026/2026-0714-mobile-fs-react-web-capacitor.md) |
| PWA readiness | Useful delivery/runtime dependency; continue as metadata/checklist. | [2026-0714 mobile FS PWA readiness](reports/2026/2026-0714-mobile-fs-pwa-readiness.md) |
| Codex-style code builder | Useful for guided AI review; needs provider-neutral AI task packet for stronger automation. | [2026-0714 mobile FS Codex code builder](reports/2026/2026-0714-mobile-fs-codex-code-builder.md) |
| Flutter | Promising later platform target; needs Flutter extension metadata. | [2026-0714 mobile FS Flutter](reports/2026/2026-0714-mobile-fs-flutter.md) |
| React Native | Promising later platform target; needs React Native extension metadata. | [2026-0714 mobile FS React Native](reports/2026/2026-0714-mobile-fs-react-native.md) |
| Native wrapper boundary | Useful boundary contract; do not expand into native execution by default. | [2026-0714 mobile FS native wrapper boundary](reports/2026/2026-0714-mobile-fs-native-wrapper-boundary.md) |
| Claude-style code builder | Useful provider-neutrality check; durable contract should not be Codex-specific. | [2026-0714 mobile FS Claude code builder](reports/2026/2026-0714-mobile-fs-claude-code-builder.md) |
| Common requirements | Common Mtool-owned artifact set and target extensions are extracted. | [2026-0714 mobile FS common requirements](reports/2026/2026-0714-mobile-fs-common-requirements.md) |
| Ownership boundaries | Mtool-owned, external-owned, user-confirmation, forbidden, and parked scope boundaries are explicit. | [Mobile ownership boundaries](mobile-ownership-boundaries.md) |
| Output modes | `mtool_no_code`, `external_no_code`, and `hybrid` are defined. | [Mobile output modes](mobile-output-modes.md), [2026-0714 mobile output mode hardening](reports/2026/2026-0714-mobile-output-mode-hardening.md) |
| Execution UI policy | UI stays read-only until explicit safety controls exist. | [Mobile artifact execution UI policy](mobile-artifact-execution-ui-policy.md), [2026-0714 mobile artifact execution UI policy](reports/2026/2026-0714-mobile-artifact-execution-ui-policy.md) |

Read this section first when looking for the mobile FS evaluation result. Use the dated reports for detailed evidence and the date-less documents for current commitments.

mobile FS の評価結果を探す時は、この section から読む。詳細根拠は日付付き report、現在有効な約束は日付なし文書を見る。

## Layers / layer

Do not compare different layers as if they were alternatives. A FE/app framework, an AI/code-builder, and a delivery/runtime mode answer different questions.

異なる layer を同列比較しない。FE / app framework、AI / code-builder、delivery / runtime mode は答える問いが違う。

| Layer | Meaning | First targets |
| --- | --- | --- |
| Layer A: FE / app framework consumers | App-surface implementation frameworks. | React/Web + Capacitor-style wrapper, Flutter, React Native |
| Layer B: AI / code-builder consumers | Agents/tools that read Mtool artifacts and help produce implementation. | Codex-style handoff, Claude-style handoff |
| Layer C: delivery / runtime mode consumers | How an already-created web/app surface is delivered or run. | PWA, native wrapper packaging boundary |
| Parked / out of scope | Too broad or provider-specific for first-pass comparison. | Direct native iOS/Android generation, specific commercial no-code platforms |

Cross-layer notes are dependencies, not same-layer alternatives.

Example:

- React/Web + Capacitor belongs to Layer A.
- PWA readiness belongs to Layer C.
- Codex generating a React Native implementation involves Layer B consuming Layer A requirements.

## Repository development storage / repo開発時の置き場所

When working inside this repository, feasibility study working artifacts go under:

```text
work/feasibility-studies/mobile-wrapper/{YYYYMMDD}-{study-key}/
```

Recommended structure:

```text
work/feasibility-studies/mobile-wrapper/{YYYYMMDD}-{study-key}/
  input/
    mobile-app-handoff.json
    mobile-wrapper-bundle-manifest.json
    source-artifact-index.json
  layer-a-fe-app-framework/
    react-web-capacitor/
    flutter/
    react-native/
  layer-b-ai-code-builder/
    codex/
    claude/
  layer-c-delivery-runtime/
    pwa/
    native-wrapper-boundary/
  validation/
    summary.json
  notes/
    draft-findings.md
```

Rules:

- `input/` contains immutable copies or hashes of Mtool handoff artifacts used by the study.
- Layer directories contain raw experiment notes, small generated proof artifacts, and target-specific observations.
- `validation/summary.json` is the compact machine-readable result for the study.
- `notes/draft-findings.md` is the local draft before promotion to a reviewed report.
- Bulky generated apps, dependency folders, native projects, build outputs, credentials, tokens, and signing material must not be committed.
- If a generated app/project is required for a study, put it outside the repository or under an ignored work directory and summarize it in the report.

## User workspace storage / user workspace時の置き場所

For normal user workflows, use the project-local Mtool workspace defined by the workspace onboarding plan.

Default:

```text
{project_root}/mtool-workspace/
```

Recommended feasibility-study locations:

```text
{project_root}/mtool-workspace/
  mtool-project/
    feasibility-studies/mobile-wrapper/{study-key}/
      input/
      layer-a-fe-app-framework/
      layer-b-ai-code-builder/
      layer-c-delivery-runtime/
      validation/
      notes/
  review-artifacts/
    mobile-feasibility/{study-key}.md
  validation/
    mobile-feasibility/{study-key}-summary.json
```

Rules:

- `mtool-project/feasibility-studies/` is Mtool-owned working state.
- User-facing summaries belong in `review-artifacts/mobile-feasibility/`.
- Compact validation evidence belongs in `validation/mobile-feasibility/`.
- Promotion into a real user project remains a separate reviewed action.

## Durable report storage / 永続report置き場

Reviewed, durable findings go under dated reports:

```text
docs/reports/YYYY/YYYY-MMDD-mobile-fs-{study-key}.md
```

Examples:

```text
docs/reports/2026/2026-0714-mobile-fs-target-matrix.md
docs/reports/2026/2026-0715-mobile-fs-react-web-capacitor.md
docs/reports/2026/2026-0715-mobile-fs-ai-code-builder.md
docs/reports/2026/2026-0716-mobile-fs-common-requirements.md
```

Use reports for decisions, comparison results, and stable conclusions. Do not rely on `work/` as the only record of a decision.

## First-pass report template / 1周目 report template

Each first-pass target report should include:

- layer;
- target;
- study key;
- input artifact hashes or paths;
- setup assumptions;
- required Mtool artifacts;
- missing metadata;
- auth/token-storage expectations;
- API/action/validation/error mapping;
- local storage/offline expectations;
- native/plugin responsibility;
- generated files owned by external tool;
- blocker severity;
- recommendation: continue, park, or reject.

## Second-pass common requirements / 2周目 共通要件

After first-pass studies, create a common requirements report:

```text
docs/reports/YYYY/YYYY-MMDD-mobile-fs-common-requirements.md
```

It should extract:

- common Mtool-owned artifact set;
- target-specific extension points;
- shared validation rules;
- fallback behavior;
- output mode implications for `mtool_no_code`, `external_no_code`, and `hybrid`;
- remaining parked items.
