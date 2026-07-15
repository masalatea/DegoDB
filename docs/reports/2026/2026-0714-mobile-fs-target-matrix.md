# Mobile external feasibility target matrix / mobile external FS target matrix

Date: 2026-07-14

## Purpose / 目的

This report records the first feasibility-study matrix for external FE/no-code/app consumers of Mtool mobile wrapper output.

Mtool should not immediately migrate all frontend/app-surface responsibility to an external framework. Instead, it should first compare several external consumers, identify common Mtool-owned handoff requirements, and only then harden output modes such as `mtool_no_code`, `external_no_code`, and `hybrid`.

この report は、Mtool mobile wrapper output を外部 FE / No Code / app consumer に渡すための最初の FS target matrix を記録する。

Mtool は frontend / app surface の責務をすぐ外部 framework へ完全移行しない。まず複数の外部 consumer を比較し、Mtool が所有すべき共通 handoff 要件を抽出し、その後で `mtool_no_code` / `external_no_code` / `hybrid` などの output mode を固める。

Stable FS specification:

- `docs/mobile-external-feasibility-study.md`

## Storage decision / 置き場所

Repository development working artifacts:

```text
work/feasibility-studies/mobile-wrapper/{YYYYMMDD}-{study-key}/
```

User workspace Mtool-owned artifacts:

```text
{project_root}/mtool-workspace/mtool-project/feasibility-studies/mobile-wrapper/{study-key}/
```

User-facing review artifacts:

```text
{project_root}/mtool-workspace/review-artifacts/mobile-feasibility/{study-key}.md
```

Compact validation evidence:

```text
{project_root}/mtool-workspace/validation/mobile-feasibility/{study-key}-summary.json
```

Durable reviewed reports:

```text
docs/reports/YYYY/YYYY-MMDD-mobile-fs-{study-key}.md
```

Bulky generated apps, dependency folders, native projects, build outputs, credentials, tokens, and signing material must not be committed.

## Layer rule / layer rule

Do not compare different layers as if they were direct alternatives.

- Layer A compares FE/app framework consumers.
- Layer B compares AI/code-builder consumers.
- Layer C compares delivery/runtime mode consumers.
- Cross-layer items are dependencies, not same-layer alternatives.

For example, React/Web + Capacitor belongs to Layer A, PWA readiness belongs to Layer C, and a Codex-style builder that generates React Native code belongs to Layer B consuming Layer A requirements.

## First-pass target matrix / 1周目target matrix

### Layer A: FE / app framework consumers

| Study key | Target | Priority | Main question | Expected input | First-pass output report |
| --- | --- | --- | --- | --- | --- |
| `react-web-capacitor` | React/Web + Capacitor-style wrapper | A1 | Can current Mtool web/runtime/mobile handoff artifacts become mobile wrapper app input without Mtool owning native projects? | `mobile-app-handoff.json`, `wrapper-target-contract.json`, `react-wrapper-app-handoff.json`, `mobile-wrapper-bundle-manifest.json` | `docs/reports/2026/2026-0714-mobile-fs-react-web-capacitor.md` |
| `flutter` | Flutter input packet | A2 | What extra Dart/widget/state/navigation metadata is required beyond the framework-neutral handoff? | `mobile-app-handoff.json`, `flutter-input-packet.json`, bundle manifest | `docs/reports/2026/2026-0714-mobile-fs-flutter.md` |
| `react-native` | React Native input packet | A3 | What differs from Flutter and React/Web wrapper, and which requirements can remain shared? | `mobile-app-handoff.json`, `react-native-input-packet.json`, bundle manifest | `docs/reports/2026/2026-0714-mobile-fs-react-native.md` |

### Layer B: AI / code-builder consumers

| Study key | Target | Priority | Main question | Expected input | First-pass output report |
| --- | --- | --- | --- | --- | --- |
| `codex-code-builder` | Codex-style code-builder handoff | B1 | Can an AI builder read the handoff, ask only necessary confirmation questions, and avoid guessing core behavior? | human-readable handoff, structured task packet, validation checklist, ownership boundary | `docs/reports/2026/2026-0714-mobile-fs-codex-code-builder.md` |
| `claude-code-builder` | Claude-style code-builder handoff | B2 | Which instructions must be provider-neutral versus tool-specific? | provider-neutral task packet plus optional tool-specific notes | `docs/reports/2026/2026-0714-mobile-fs-claude-code-builder.md` |

### Layer C: delivery / runtime mode consumers

| Study key | Target | Priority | Main question | Expected input | First-pass output report |
| --- | --- | --- | --- | --- | --- |
| `pwa-readiness` | PWA from Mtool web/no-code output | C1 | Can PWA readiness be expressed as metadata/checklist without becoming a separate app generation lane? | web runtime readiness, manifest/service-worker requirements, auth/storage constraints, validation checklist | `docs/reports/2026/2026-0714-mobile-fs-pwa-readiness.md` |
| `native-wrapper-boundary` | Native wrapper packaging boundary | C2 | Which native preparation facts belong in Mtool metadata versus external tooling? | native capability declaration, plugin checklist, signing/store non-goals | `docs/reports/2026/2026-0714-mobile-fs-native-wrapper-boundary.md` |

## Parked targets / parked targets

| Target | Reason parked | Revisit condition |
| --- | --- | --- |
| Direct native iOS/Android generation | Too broad for Mtool ownership: native build, signing, device QA, and store submission. | Reopen only with concrete product demand and explicit ownership decision. |
| Specific commercial no-code platforms | Provider-specific and unstable before common requirements are known. | Reopen after common handoff requirements are extracted and a real target is selected. |

## Same-layer comparison columns / 同layer比較項目

Each first-pass report should use the same columns within its layer:

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

## Sample input set / sample input set

Use one representative Mtool output set for the first pass so results are comparable:

- source: sample28 or equivalent mobile-app-handoff packet;
- artifacts:
  - `mobile-app-handoff.json`;
  - `wrapper-target-contract.json`;
  - `react-wrapper-app-handoff.json`;
  - `flutter-input-packet.json`;
  - `react-native-input-packet.json`;
  - `mobile-wrapper-bundle-manifest.json`.

The sample input should be copied or hashed into:

```text
work/feasibility-studies/mobile-wrapper/{YYYYMMDD}-{study-key}/input/
```

## First execution order / 最初の実行順

Recommended order:

1. `react-web-capacitor`
   - closest to existing Mtool web/no-code/runtime output;
   - validates the current mobile wrapper foundation directly.
2. `pwa-readiness`
   - useful cross-layer dependency for React/Web wrapper delivery;
   - should remain a delivery/runtime study, not a FE framework alternative.
3. `codex-code-builder`
   - checks whether the handoff is clear enough for AI-assisted implementation without hidden guessing.
4. `flutter` and `react-native`
   - compare later platform packets after the closest React/Web path is understood.
5. `native-wrapper-boundary`
   - extracts signing/store/native-project non-goals and metadata boundary.
6. `claude-code-builder`
   - checks provider neutrality after the Codex-style packet shape is understood.

## Exit condition for MW-9 / MW-9完了条件

MW-9 is complete when:

- layer definitions are explicit;
- first-pass target list is explicit;
- same-layer comparison columns are explicit;
- storage locations are explicit;
- first execution order is explicit;
- no single target is hardened before comparable evidence exists.

This report and `docs/mobile-external-feasibility-study.md` satisfy those conditions. The next active step is MW-10: run first-round external consumer feasibility studies.
