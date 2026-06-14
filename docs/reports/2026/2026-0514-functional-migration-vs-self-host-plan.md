# 2026-05-14 Functional Migration Vs Self-Host Plan

## Status

- status: `PENDING`
- status updated at: `2026-05-27`
- role:
  - broad rewrite の completion boundary と gate を定義する parent/reference plan
  - current wave の active execution は `2026-0527-broad-rewrite-temporary-closure-plan.md` が担う
- current read:
  - current wave は `Phase 1. 機能移植完了` を閉じやすくする安定化までを扱う
  - `Phase 2. self-host / runtime 置換完了` は next wave の gate として保持する

## 結論

計画は次の 2 段階に分ける。

1. Phase 1. 機能移植完了
2. Phase 2. self-host / runtime 置換完了

この 2 つは同じ「完了」ではない。

## Phase 1. 機能移植完了

目的:

- 旧 Mtool の設定画面でできたことを、新画面で一通り設定できるようにする。
- 設定の正本を current canonical schema / file に寄せる。
- その設定から出る Output が新旧一致する状態まで持っていく。

完了条件:

- 旧設定画面でできた設定操作を current route で行える。
- current canonical data が source of truth になっている。
- current metadata から出る Output が旧実装と一致する。
- 日常運用で旧画面が不要になる。

この段階で残っていてよいもの:

- bootstrap copy
- partial self-generation
- runtime 内部の legacy delegate
- generated runtime が authoritative source ではないこと

## Phase 2. self-host / runtime 置換完了

目的:

- Mtool 自身が出力した Output を Runtime 本体へそのまま差し替える。
- 差し替え後も再編集なしで起動、設定変更、再出力、再比較ができるようにする。

完了条件:

- generated runtime が authoritative source になっている。
- Mtool 自身が出力した Runtime へ差し替えても app が起動する。
- 差し替え後も current route で設定変更と再生成が回る。
- 置き換えのための差分吸収が、場当たりではなく設計として整理されている。

## なぜ分けるか

- 先に self-host 都合へ寄せすぎると、機能移植そのものが遅くなる。
- まず Phase 1 で current 実装として綺麗に機能を揃える方が、欠けている機能と設計差分を見つけやすい。
- その後に Phase 2 で、機能は壊さずに Runtime 本体を generated Output の contract に寄せる方が安全である。

## 現在の位置付け

- broad scope の現在値は主に Phase 1 の進捗として読む。
- `36/36 success` の Project 1 parity は、Phase 1 における Output parity の強い材料である。
- ただし Phase 2 の意味での self-host 置換完了にはまだ達していない。

## 当面の優先順位

1. Phase 1 を優先する。
2. 旧設定機能の網羅と current canonical 化を進める。
3. その後に Phase 2 として runtime self-generation と authoritative runtime switch を進める。

## Phase 2 へ切り替えるゲート

次の状態になるまでは、Phase 2 を主系へ上げない。

- 旧設定画面でできた主要操作が current route へ吸収されている。
- current canonical data を正本として、日常運用を旧画面なしで回せる見通しが立っている。
- `LanguageResource`、page security / host assignment、`HTML` / `Source Output` bridge debt の残件が、「未着手の大機能」ではなく current 側で詰める残作業になっている。
- 主要な parity 確認、とくに `Project 1 = MTOOL` の Output parity が current route 更新後も安定して維持できている。

このゲートを超えた後に、runtime self-generation、authoritative runtime switch、generated Output による自己置換を主系へ上げる。
