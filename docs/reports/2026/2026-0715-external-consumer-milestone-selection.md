# 2026-0715 External Consumer Milestone Selection

## 目的

942 として、RSS bundle 整理後に進める外部 consumer 連携の次 milestone を選定した。

## 候補

| 候補 | 現状 | 次に進める場合のリスク |
| --- | --- | --- |
| React/Web + Capacitor | `external-output`、AI task packet、sample35 がある | 既に first proof があるため、いきなり実装を増やすと責務過多になりやすい |
| PWA | readiness metadata がある | manifest / service worker 生成まで踏み込むと Mtool 所有範囲が広がる |
| Flutter WebView | wrapper extension metadata がある | Flutter project / source / native build に踏み込みやすい |
| React Native | extension metadata がある | package 選定や native project 生成に踏み込みやすい |
| AI-assisted code builder | task packet と確認導線がある | provider別実装や実行権限の扱いが広がりやすい |

## 判断

次の milestone は、特定 consumer の full implementation ではなく、外部 consumer 全体の handoff readiness inventory とする。

理由:

- React/Web + Capacitor、PWA、Flutter WebView、React Native、AI task packet は既にそれぞれ first slice がある。
- いま必要なのは「どれをさらに実装するか」より、各 consumer がどこまで ready で、どこから先が外部 owner の責務かを横並びで確認すること。
- その整理をしないまま実装に進むと、Mtool が native project、dependency install、signing、store submission、full framework migration を暗黙に持ちやすい。
- 次の bounded slice は、gap inventory の結果を見て選ぶべき。

## Selected milestone / 選定 milestone

`External consumer handoff readiness inventory`

内容:

- 各 consumer が読む artifact / packet を一覧化する。
- 既にある validation evidence を整理する。
- 不足している gap を Mtool-owned / external-owned / parked に分類する。
- 次の first bounded slice を 1つだけ選ぶ。

## 次

943 として handoff gap inventory を行う。

## 状態

`DONE_HANDOFF_READINESS_INVENTORY_SELECTED`
