# Capacitor artifact import sample plan / Capacitor artifact import sample plan

Date: 2026-07-14

## Summary / summary

This report plans a sample that proves a Capacitor-ready React app can directly import Mtool artifacts without requiring AI in the runtime path and without making Mtool initialize a Capacitor project.

この report は、Capacitor-ready React app が Mtool artifact を直接 import できることを確認する sample の計画である。runtime path に AI を必須にせず、Mtool が Capacitor project を初期化する責務も持たない。

## Decision / 判断

Create a new tutorial/sample lane:

```text
sample35-capacitor-artifact-import
```

This sample is not Mtool-generated output. It is an external-owner sample app placed in the repository to prove consumption of Mtool artifacts.

この sample は Mtool の生成出力そのものではない。Mtool artifact を消費する外部 owner 側 sample app として repository に置く。

## Goal / 目的

Prove this path:

```text
Mtool source outputs / mobile wrapper artifacts
  -> copied or referenced JSON artifacts
  -> React/Vite app imports them directly
  -> Capacitor-ready app shell can render screens and action intent draft
  -> optional Capacitor sync/native build remains external owner responsibility
```

The sample should cover the full set of operations Mtool expects an external app surface to consume. It does not need to cover every Capacitor feature.

この sample は、外部 app surface が消費すると想定される Mtool 側操作を一通り網羅する。Capacitor の全機能を網羅する必要はない。

## Non-goals / non-goals

This sample must not make Mtool own:

- Capacitor initialization;
- `ios/`;
- `android/`;
- native build;
- signing;
- store submission;
- production React architecture;
- app deployment;
- automatic dependency installation.

This sample also does not need to cover every external-tool feature:

- Capacitor plugin matrix;
- native permission matrix;
- push notification;
- app store flow;
- device-specific QA matrix;
- external routing/state-management libraries beyond what is needed for the Mtool operation proof.

The sample may include:

- `package.json`;
- `capacitor.config.ts`;
- React/Vite source;
- sample copied JSON artifacts;
- static validation script.

Those files belong to the sample app, not to Mtool's artifact generator.

## Ownership boundary / ownership boundary

| Area | Owner |
| --- | --- |
| Mtool artifact generation | Mtool |
| `mobile-app-handoff.json` / wrapper handoff / bundle manifest | Mtool-owned input artifacts |
| Capacitor-ready sample app | external app owner sample |
| `package.json` / `capacitor.config.ts` | sample app |
| `ios/` / `android/` | not generated in first sample |
| native sync/build/signing | external owner, optional later |
| AI assistance | optional, not required |

## Mtool operation coverage scope / Mtool操作網羅scope

The sample should cover Mtool-intended operations end-to-end inside the external app shell.

| Operation | Required in sample | Notes |
| --- | --- | --- |
| Artifact import | Yes | Import checked-in Mtool artifact fixtures directly from TypeScript/React. |
| Artifact index review | Yes | Show bundle manifest order and source artifact refs. |
| Project/app identity display | Yes | Show project key/name/title from imported artifacts. |
| Screen rendering | Yes | Render list, detail, and form screens from runtime/bridge metadata. |
| Field rendering | Yes | Render readonly/display fields and editable fields with required markers. |
| Navigation/selection | Yes | Allow moving between list/detail/form-like views or equivalent screen sections. |
| Local form draft | Yes | Maintain local form state derived from Mtool runtime metadata. |
| Required-field validation | Yes | Block action intent when required input is missing. |
| Action intent draft | Yes | Build and display `no-code-runtime-action-intent-v0` locally. |
| Submit handoff boundary | Yes | Provide a mock/disabled submit handoff that shows what would be sent, without mutating real server state in the first slice. |
| Error/blocked state | Yes | Show blocked/unavailable action feedback from Mtool metadata or local validation. |
| Ownership boundary display | Yes | Show that Capacitor/native/signing/store work is external owner responsibility. |
| Reconnect/offline/realtime | No | Out of this sample; requires separate sync contract. |
| Native build/sync | Optional later | `npx cap sync` may be a later optional gate, not first mandatory proof. |

Completion for this sample means these Mtool operations are represented. It does not mean full Capacitor/native feature coverage.

## Proposed sample structure / sample構造案

```text
sample/tutorials/sample35-capacitor-artifact-import/
  README.md
  package.json
  tsconfig.json
  vite.config.ts
  capacitor.config.ts
  index.html
  src/
    App.tsx
    main.tsx
    mtoolNoCodeBridge.ts
    mtoolArtifacts.ts
    MtoolArtifactSummary.tsx
    MtoolScreenRenderer.tsx
    MtoolActionIntentPanel.tsx
    mtool-artifacts/
      bridge-contract.sample.json
      react-wrapper-app-handoff.sample.json
      mobile-wrapper-bundle-manifest.sample.json
  scripts/
    validate-sample.mjs
```

## Artifact strategy / artifact strategy

First slice should use small checked-in sample artifact fixtures derived from sample28 shapes.

Reason:

- the sample can be inspected and statically validated without requiring a prior Mtool run;
- it demonstrates direct JSON import clearly;
- it avoids coupling the sample's source tree to disposable `work/` output;
- later slices can add a `prepare-from-sample28` script to copy live artifacts from `work/source-outputs/SAMPLE28/...`.

## App behavior / app behavior

The first app should:

1. import Mtool artifact JSON directly;
2. render project/app identity;
3. render artifact index and bundle manifest order;
4. render list/detail/form screens from bridge contract runtime preview;
5. support local selection/navigation between screens or screen sections;
6. render editable form fields and required markers;
7. validate required fields locally;
8. create and display an action intent draft locally;
9. show mock/disabled submit handoff boundary;
10. render ownership boundary from mobile wrapper handoff;
11. show that real API/native execution is not performed by the first sample slice.

## Validation / validation

First validation should be static and dependency-light:

```sh
node sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs
```

The static validation should check:

- `package.json` exists and includes Capacitor dependencies/scripts;
- `capacitor.config.ts` exists;
- `ios/` and `android/` do not exist;
- sample artifact JSON files parse;
- sample artifacts contain expected schema/version keys;
- React source imports the artifacts;
- React source contains list/detail/form rendering path;
- React source contains action intent draft path;
- React source contains required-field validation path;
- React source contains mock/disabled submit handoff boundary;
- README states that Mtool does not initialize Capacitor.

Optional later validation:

```sh
npm install
npm run build
npx cap sync
```

The optional validation may require network/dependency install and should not be part of the first mandatory gate.

## Suggested implementation sequence / 実装順

| Step | Work unit | Exit condition |
| --- | --- | --- |
| CAP-S1 | Plan and boundary | This report exists; current plan references the sample as a planned slice. |
| CAP-S2 | Scaffold sample source | Sample directory exists with README, package config, Capacitor config, React source, fixture artifacts, and components for artifact summary, screen rendering, form draft, action intent, and submit boundary. |
| CAP-S3 | Static validation | `validate-sample.mjs` checks boundary, artifact import shape, list/detail/form coverage, action intent draft, required validation, and no `ios/` / `android/` with no npm install. |
| CAP-S4 | Docs/index update | Tutorial README and relevant mobile docs link to the sample. |
| CAP-S5 | Optional build smoke | If dependencies are available, run `npm install` / `npm run build`; keep `npx cap sync` optional. |

## Relationship to active no-code standalone lane / no-code単体laneとの関係

This is separate from standalone Mtool no-code completion.

Standalone no-code remains the immediate mainline. This Capacitor sample is a bounded external app consumption proof and should not expand the standalone no-code scope.

If both are worked in the same branch, commits should keep them separate:

1. no-code standalone scope/evidence work;
2. Capacitor artifact import sample scaffold.

## Status / status

Status: `FIRST_SLICE_DONE` for CAP-S1 through CAP-S4 static sample scope.

Implementation record: [2026-0714 Capacitor Artifact Import Sample First Slice](2026-0714-capacitor-artifact-import-sample-first-slice.md).
