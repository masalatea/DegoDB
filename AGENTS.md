# AGENTS.md

## 対象範囲

- 旧実装の丸ごと snapshot は原則として repo に置かず、文脈ごとに整理済み参照へ分割して置く。
- 旧生成 DB class の比較・移行確認は `mtool/reference/legacy-dbclasses/` に置く。
- 旧 mtool build ロジックの確認は `mtool/reference/legacy-mtool-build/` に置く。
- 旧 mtool template / project setting の確認は `mtool/reference/legacy-mtool-templates/` に置く。
- 新実装の runtime / generator / Docker container は、整理済み legacy reference を直接の実行入力として使わない。
- 整理済み legacy reference を Docker image / container / artifact bundle に含める場合は、明示的な用途と除外方針を確認する。
- 新実装コードは `mtool/` 配下の `mtool/admin/`、`mtool/lab/`、`mtool/shared/`、`mtool/scripts/` と、リポジトリ直下の `docs/` を中心に置く。
- 旧実装の調査資料・設計書は、現在有効なものは `docs/` または `docs/reports/` 配下に置く。

## ドキュメント命名ルール

- レポート系、調査メモ系、履歴として残す文書は、日付付きのファイル名にする。
- レポート系・履歴系のファイル名形式は以下とする。
  - `YYYY-MMDD-<slug>.md`
  - 例: `2026-0507-plan.md`
- 恒久的に更新していく設計書・参照文書は、ファイル名に日付を入れない。
  - 例: `overview.md`
  - 例: `runtime-architecture.md`

## 命名の意図

- 日付付きファイル名は、特定時点の記録、進捗、調査結果、作業履歴を残す文書に使う。
- 日付なしファイル名は、継続的に更新する正式な設計書や基準文書に使う。

## 現在の計画リスト

- ユーザーが「計画リスト」「現在の計画」「残件」「次にやること」「ロードマップ」などを尋ねた場合は、まず `docs/current-plans.md` を確認する。
- その場合は、最初に `docs/current-plans.md` の `Quick Plan List / 計画リスト` を作業の塊・コミット単位として答える。必要な場合だけ detailed plan / 個別 status も補足する。
- `docs/current-plans.md` を、active / TODO / parked の現在地を示す正本インデックスとして扱う。
- `docs/reports/` 配下の日付付き文書は、履歴、判断経緯、詳細記録として読む。active plan の唯一の所在として扱わない。
- report 内で active になった計画は、必要に応じて `docs/current-plans.md` へ昇格する。
- 「計画がない」と答える前に、`docs/current-plans.md` と `docs/README.md` の current plan 導線を確認する。

## コミット方針

- 作業の進行単位ではなく、後から読む意味単位でコミットする。
- 実装、対応するテスト、関連ドキュメントは基本的に同じコミットに含める。
- コード変更を含むコミットの前には、必ずテストを実行する。原則として `make test` を実行し、やむを得ず絞る場合も対象範囲と理由を記録する。
- status / wording などの小さな調整は、近い意味のコミットへ amend / squash する。
- sample 追加のような大きな変更は、主題ごとに単独コミットとして扱う。
- push 前にコミット列を確認し、小粒すぎるコミットはまとめる。
- push 後は原則として履歴を書き換えない。ただし private repository で利用者が明示した場合は `--force-with-lease` で同期してよい。
