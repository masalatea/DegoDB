# 2026-05-15 Progress Snapshot

> Latest broad-scope progress reading after the 2026-05-15 `LanguageResource` policy reset. This snapshot supersedes `docs/reports/2026/2026-0514-progress-snapshot.md` as the current percentage baseline.

## 前提

- この進捗率は file 数ではなく、機能マイルストーン重みで見た概算である。
- `Project 1 = MTOOL` の parity 完了度と、広い意味での「旧 `dev web/db` 全体の再構築完了度」は分けて扱う。
- 計画は引き続き `Phase 1. 機能移植完了` と `Phase 2. self-host / runtime 置換完了` に分ける。
- `LanguageResource` は 2026-05-15 以降、「旧 DB editor を current route で再現する slice」ではなく、「optional module + file-based source of truth + inspector-only current route」として評価する。

## 完了判定の現在地

| 観点 | 現在の判定 | 補足 |
| --- | --- | --- |
| 設定画面 parity | partial | 主要 module の current route は実用段階に入ったが、page security / host assignment / runtime まわりに未整理部分が残る。 |
| current source of truth | partial | `LanguageResource` は file-based へ fixed したが、broad scope 全体では HTML/runtime/security 系の bootstrap / bridge debt が残る。 |
| Output parity | partial | `Project 1 = MTOOL` は `36/36 success` を維持しているが、broad scope 全体ではまだ代表ケース中心である。 |
| 再現性 | partial | export / validate / wrapper smoke の再実行導線は固まったが、full self-host までは未到達。 |
| 旧画面不要化 | partial | 日常運用の主系は current 側へ寄ったが、debug bridge / compatibility wrapper と一部 legacy 調査導線はまだ残る。 |

## 概算進捗

- broad scope 全体
  - 約 `80-82%`
  - 根拠: admin/lab の主要 route 群、metadata schema、build/compare/source-output 基盤、Project 1 parity、directory/policy 整理に加え、`LanguageResource` は file-only / tableless 方針まで固定した。残る主因は `HTML` generator/runtime bootstrap dependency、page security の route policy 連携、host assignment の infra split、sample 固有 output 実装、runtime self-host 準備である。
- `Project 1 = MTOOL` parity / bridge scope
  - 約 `92-93%`
  - 根拠: `36/36 success` の build/publish parity は維持しており、wrapper/current route handoff も広く入っている。`LanguageResource` も旧 editor parity を追わず、file canonical + inspector-only + generated wrapper compatibility へ設計を固定できた。残るのは主に bootstrap / fallback / compatibility wrapper の圧縮である。
- `Project 1 = MTOOL` canonical replacement scope
  - 約 `68-70%`
  - 根拠: `LanguageResource` の source of truth は file-based に置き換わったが、`HTML` / runtime / source-output では bootstrap dependency と partial self-generation がまだ残る。`36` outputs のすべてが「bridge なしの canonical/self-generated」に到達したわけではない。
- `LanguageResource` slice
  - 約 `88-90%`
  - 根拠: source of truth、current read path、generated wrapper behavior、sample export/validate、DB retirement まで方針と実装が揃った。残るのは compatibility wrapper / debug bridge の削除判断と、同パターンを他 pilot へどこまで広げるかである。
- sample / directory reorganization scope
  - 約 `85%`
  - 根拠: layout、命名、責務分離、sample-only initdb policy、`sample1..7` runtime 実検証までは完了した。未了は project-specific metadata 増強と、必要な sample だけに buildable output を持たせる整理である。

## Step 管理表

| Step | 目的 | 現在の目安 | いま出来ていること | 完了までの主な残り |
| --- | --- | --- | --- | --- |
| Step 1 / Phase 1 | 全機能の移植・再現。current route / canonical metadata / file workflow で日常運用を成立させる。 | 約 `80-82%` | `Project 1 = MTOOL` は `36/36 success`、主要 current route は実用段階、`LanguageResource` は file-based source of truth へ移行済み。 | page security の route policy 接続、host assignment の infra split、HTML/runtime bootstrap dependency 圧縮、sample 側の buildable output 整理。 |
| Step 2 / Phase 2 | 自己出力 Runtime への置換。Mtool 自身の Output を Runtime 本体へ差し替えて継続運用する。 | 約 `20-25%` | self-host import/sync loop の first slice、`RUNTIME-DBCLASSES` の partial self-generation、proxy artifact の first-pass 生成。 | generated runtime の authoritative 化、app 自身の runtime switch、runtime / loader / generated contract の整理、再編集なし運用の成立。 |

## 2026-05-14 から進んだ点

- `LanguageResource` の最終 source of truth を `mtool/resources/<PROJECT_KEY>/` 配下の JSON file tree に固定した。
- current admin の `LanguageResource` は inspector-only とし、旧 Lang editor は再実装しない前提を明文化した。
- current app の auto-translate route / service は外し、generated `lang_res_auto_translate_ajax.php` は file workflow を案内する legacy-compatible `NG` response に切り替えた。
- `MTOOL` / `SAMPLE2` / `SAMPLE4` / `SAMPLE6` の file tree export / validate を bulk コマンドで再実行できる状態にした。
- `make mtool-html-db-lang-res-wrapper-check` を最新 publish 済み artifact 前提の self-contained smoke entrypoint にした。
- DB bridge / retirement script を `mtool/scripts/debug/language_resource/` 配下へ寄せ、local `config_app` では `project_language_resource_*` table / data を消した。

## 「かなりできている」と言える理由

- `Project 1 = MTOOL` の output parity は `36/36 success` を維持している。
- `Project`、`DB Table`、`Data Class`、`DB Access class/function`、`Proxy`、`Source Output`、`Compare Output`、`Endpoint Test` は current route の主系が使える。
- `HTML` は source binding、live row、template metadata、global settings を current canonical 側へ寄せた。
- `LanguageResource` は旧 DB editor を再現する代わりに、repo file を正本とする新運用へ切り替えた。

## まだ「全部カバー済み」とは言えない理由

- page security landing zone を current route / service policy へどう再投影するかが未固定。
- host assignment landing zone を infra/settings slice へどう split するかが未固定。
- `HTML` generator/runtime 側には bootstrap dependency が残る。
- `RUNTIME-DBCLASSES` は partial self-generation までで、full self-host ではない。
- DB import connector はまだ `MTOOL` first slice 前提で、一般化が未了。
- sample ごとの buildable output はまだ選択的にしか持たせていない。

## 方針の再確認

- 主系は引き続き `Phase 1. 機能移植完了` に置く。
- `LanguageResource` は「旧 editor parity を取るかどうか」ではなく、「file-based source of truth で current 運用が成立しているか」で評価する。
- broad scope の完了判定は、1:1 の GUI 再現ではなく、current route / canonical metadata / file workflow で日常運用が回るかを優先する。
- Phase 2 は、残課題の中心が未移植機能ではなく runtime / loader / generated contract 調整へ移ってから主系へ上げる。
- Step 2 の `%` は broad scope とは別に、self-host / runtime 置換の到達度として管理する。

## 次の優先順

1. `HTML` generator/runtime 側の bootstrap dependency をさらに削る。
2. page security / host assignment を landing zone から運用 policy 側へ接続する。
3. `LanguageResource` compatibility wrapper / debug bridge の出口条件を決める。
4. 次の pilot project に file-only / tableless pattern を広げるか判断する。

## 関連文書

- [docs/internal/mtool-admin-roadmap.md](<repo-root>/docs/internal/mtool-admin-roadmap.md)
- [docs/internal/language-resource-separation.md](<repo-root>/docs/internal/language-resource-separation.md)
- [2026-0515-language-resource-file-source-of-truth-plan.md](<repo-root>/docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md)
- [2026-0515-end-of-day-handoff.md](<repo-root>/docs/reports/2026/2026-0515-end-of-day-handoff.md)
