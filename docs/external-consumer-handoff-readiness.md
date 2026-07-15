# External Consumer Handoff Readiness / 外部 consumer handoff readiness

この文書は、Mtool の外部 consumer / 外部 tool 連携について、現在どの consumer がどこまで ready で、何が不足しているかを整理する。

これは production app / native project / framework 全移行の計画ではない。Mtool が所有する handoff artifact、packet、manifest、validation の readiness を見るための正本である。

## 判定区分

| 区分 | 意味 |
| --- | --- |
| Ready | Mtool artifact / packet / docs / validation が揃っており、外部 owner が読める |
| Mtool gap | Mtool が追加で持つべき packet / manifest / checklist / sample proof |
| External-owned | 外部 owner / app creator / AI が担うべき実装 |
| Parked | 今は広すぎる、または明示需要が出るまで進めない |

## Consumer readiness matrix

| Consumer | Layer | 現在の ready evidence | Mtool gap | External-owned / parked |
| --- | --- | --- | --- | --- |
| React/Web + Capacitor | FE / app framework | `external-output.json`、`EXTERNAL-OUTPUT.md`、sample35 consumer proof、mobile wrapper target contract | Cross-consumer readiness index は追加済み。この consumer 固有の次 gap は現時点では小さい | React app shell、Capacitor init、dependency install、native build、signing、store submission |
| PWA | delivery / runtime mode | `pwa-readiness.json`、`PWA-READINESS.md`、output mode config | PWA readiness と external-output の関係を横断 checklist に入れる程度 | manifest/service worker/offline sync/push/background sync 生成 |
| Flutter WebView | FE / app framework wrapper | `flutter-input-packet.json` の `flutter_webview_wrapper_extension`、app surface config | React/PWA source との handoff checklist を次 slice 候補にできる | Flutter project/source/native files、dependency install、build/signing/store |
| React Native | FE / app framework | `react-native-input-packet.json` の `react_native_extension` | React Native固有 validation checklist は将来候補 | React Native source、package selection、native module setup、build/signing/store |
| Codex / Claude AI code builder | AI / code-builder | `ai-task-packet`、AI-assisted execution route、confirmation-required flow | General external-consumer task packet checklist は将来候補 | Provider execution、app source write、dependency install、network/API use は taskごとに明示確認 |
| Shared-state sync external runtime owners | server/client external owner | `sync-server-input.json`、`sync-client-input.json`、RSS bundle manifest、validation checklist | Combined generated bundle artifact は今は不要。外部consumer要求が出たら再検討 | Production Node.js runtime、client SDK、SSO setup、token storage |

## Cross-consumer common requirements

外部 consumer に共通して Mtool が持つべきものは次。

- source artifact reference
- ownership boundary
- validation command map
- forbidden implicit actions
- confirmation-required actions
- output mode / surface selection
- non-goals
- sample or static consumer proof when feasible

Mtool が暗黙に持たないものは次。

- production frontend architecture
- native project initialization
- dependency installation
- signing
- store submission
- app-specific token storage
- production hosting
- user source overwrite
- full framework migration

## Current gap assessment

現時点で最も有用な gap は、「特定 consumer の大きな実装」ではなく、外部 consumer handoff readiness を横断的に見られる索引である。

この文書でその gap は first slice として埋める。

次に実装する bounded slice は、以下のどちらかが自然である。

1. Flutter WebView handoff checklist
   - React/PWA app を WebView shell が読むための確認項目を整理する
   - 実装に踏み込まず、Flutter project 生成はしない
2. AI-assisted external app task packet checklist
   - AI が external-output / output-mode-config / app-surface-config を読んで確認する手順を整理する
   - 実行 packet 実装までは進めない

この段階では、どちらも docs/checklist first がよい。

## 推奨 first bounded slice

次の first bounded slice は、`AI-assisted external app handoff checklist` を推奨する。

理由:

- 既に AI task packet と AI-assisted execution route がある
- React/Web + Capacitor、PWA、Flutter WebView、React Native のどれにも横断的に効く
- 具体的な app framework 実装に踏み込まずに価値が出る
- ユーザー確認、出力先、上書き、禁止 action、validation の考え方を統一できる

ただし、これは AI に production app を作らせる意味ではない。AI が Mtool artifact を読み、確認して、外部 owner の作業を安全に始めるための checklist である。
