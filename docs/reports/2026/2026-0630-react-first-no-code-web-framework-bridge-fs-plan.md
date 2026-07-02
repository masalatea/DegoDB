# React-first no-code Web framework bridge FS plan

Status: `DONE`. Result report: [React-first no-code Web framework bridge first slice](2026-0630-react-first-no-code-web-framework-bridge-first-slice.md).

## 背景

no-code runtime の first slice では、Mtool から `screen-definition.json`、`runtime-preview.json`、`runtime-preview.html` を出力し、list / detail / form、action intent、sync hint、operator/admin inspection まで確認できるようになった。

ただし `runtime-preview.html` は検証用 preview であり、本格的な Web UI framework を Mtool が抱え込むためのものではない。次の段階では、Mtool は framework-neutral な設計 metadata / screen definition / action intent / validation / sync hint を出し、実際の rendering / routing / component / client state は frontend framework 側へ渡す境界を決める。

React + TypeScript は frontend の長期利用、採用規模、周辺 form / JSON ecosystem の厚さから first adapter の基本方向にする。Vue / Svelte は比較参照として扱い、Mtool artifact contract が不必要に React 専用へ閉じないことを確認する。

## FS の目的

- 既存の `screen-definition.json` / `runtime-preview.json` を React + TypeScript 側で自然に消費できるか確認する。
- list / detail / form の rendering、form state、action intent dispatch を Mtool 側に UI component 実装を持たせずに成立させる。
- React + JSON Forms、React + rjsf、小さな custom React adapter のどれが Mtool の bridge に合うか比較する。
- Vue + JSON Forms、Svelte / SvelteKit custom adapter を secondary sanity check として見て、contract を React 専用に寄せすぎない。
- FS 後に、Mtool が生成すべき framework-facing artifact contract と first adapter slice を決める。

## 対象

- React + TypeScript first。
- 既存 artifact:
  - `screen-definition.json`
  - `runtime-preview.json`
  - `no-code-runtime-action-intent-v0`
- sample28 または sample30 の既存 no-code runtime artifact。
- small local probe または report-only comparison。正式な generated source output は first slice 側で決める。

## 対象外

- visual builder。
- full generated application shell。
- remote transport。
- conflict resolution。
- native / Flutter target。
- Mtool 内で React component library を恒久所有すること。

## 候補

| Candidate | Role | Notes |
| --- | --- | --- |
| React + JSON Forms | schema-driven form/runtime candidate | JSON Schema / UI schema 寄りに bridge する場合の本命候補。 |
| React + rjsf | React-specific JSON Schema form candidate | React first adapter としては直球。Mtool schema との変換コストを見る。 |
| React custom adapter | Mtool screen schema native candidate | Mtool 独自の `screen-definition.json` を残す場合の最小 adapter。 |
| Vue + JSON Forms | secondary sanity check | contract を React 専用に閉じないための比較参照。 |
| Svelte / SvelteKit custom adapter | secondary sanity check | 軽量 adapter の見通し確認。first target ではない。 |

## 評価軸

| Axis | Question |
| --- | --- |
| JSON bridge | 既存 JSON artifact を大きく崩さず使えるか。 |
| Form state | form input / validation / disabled action を自然に扱えるか。 |
| Action intent | `no-code-runtime-action-intent-v0` を明確に emit できるか。 |
| TypeScript | artifact contract を型として扱いやすいか。 |
| Component ownership | Mtool が UI component を持たずに済むか。 |
| Packaging | generated artifact / external adapter / app shell の分離がしやすいか。 |
| Long-term maintenance | ecosystem、採用、人材、documented API の面で長く続けやすいか。 |

## 完了条件

- React-first bridge が成立するか、または blocker があるかを report に記録する。
- first adapter 候補を 1 つ選ぶ。
- Mtool が生成するべき artifact contract の最小形を決める。
- `runtime-preview.html` は verification-only preview として残すか、役割縮小の方針を決める。
- current plan の次 step を framework bridge selection / first slice へ進める。

## 見積もり

- FS: 1 - 2 days。
- Selection: 0.5 - 1 day。
- First slice: 1 - 3 days。
