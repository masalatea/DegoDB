# AGENTS.md

## 対象範囲

- このリポジトリでは、旧実装コードを `original-codes/` 配下に置く。
- `original-codes/` は調査・差分確認・一時的な参照 export 用の host-side reference としてのみ扱う。
- 新実装の runtime / generator / Docker container は `original-codes/` を直接入力として使わない。
- `original-codes/` を Docker image / container / artifact bundle に含めない。必要な legacy 情報は curated reference、copied snapshot、placeholder、または host から明示指定した dump path に落として使う。
- 新実装コードは `mtool/` 配下の `mtool/admin/`、`mtool/lab/`、`mtool/shared/`、`mtool/scripts/` と、リポジトリ直下の `docs/` を中心に置く。
- 旧実装の調査資料・設計書は `original-codes/docs/` 配下に配置する。

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
