# 2026-05-20 Legacy Table Schema Helper Classification

## 結論

- `export_legacy_table_schema_reference.php` は `export_legacy_*_reference.php` 群の中でも例外で、dump-path helper ではなく temporary legacy schema helper として扱う。
- したがって current docs / prompt では、`dataclass` / `dbtable` / `db_access` / `html` / `language_resource` を dump-path helper、`table_schema` を `--dsn` / `--schema-name` helper に分けて記述する。
- script 自体の help も host-side 前提へ補強し、`original-codes/` を直接読まないことを usage に明記した。

## 背景

- `original-codes/` host-only boundary の整理で、`export_legacy_*_reference.php` 群を host-side helper としてまとめて説明していた。
- ただし `export_legacy_table_schema_reference.php` だけは `--sql-dump` を受けず、temporary imported legacy schema を `information_schema` 経由で読む実装だった。
- この違いを埋めたままだと、「全部 dump-path helper」と読めてしまい、運用や resume prompt で誤読しやすい。

## 実装

- `mtool/scripts/export_legacy_table_schema_reference.php`
  - usage / option help に host-side helper 前提を追記した。
  - `original-codes/` は base Docker runtime に mount しないことを notes に追記した。
  - `--sql-dump` ではなく、temporary imported legacy schema を `--dsn` / `--schema-name` で読む helper であることを明記した。
  - required option error も `host-side temporary legacy schema 前提` と読める文言へ補強した。
- `docs/reports/2026/2026-0520-original-codes-helper-inventory.md`
- `docs/internal/runtime-architecture.md`
- `docs/internal/generated-code-strategy.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - table schema helper を dump-path helper 群の例外として読めるように補正した。

## 検証

- `php mtool/scripts/export_legacy_table_schema_reference.php --help`
- `php -l mtool/scripts/export_legacy_table_schema_reference.php`
- `rg -n "table_schema|temporary legacy schema|dump path helper" docs mtool/scripts`

## 次

1. Docker 復旧後は `make test` を再開し、sample22 追加後の full suite count を確定する
2. host-only helper inventory の次段では、`bootstrap_dbclasses.sh` を archive へ退避できる条件整理を続ける
