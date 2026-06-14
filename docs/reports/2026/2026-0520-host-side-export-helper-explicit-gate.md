# 2026-05-20 Host-Side Export Helper Explicit Gate

## 結論

- `export_legacy_*_reference.php` 群、`export_legacy_table_schema_reference.php`、`export_mtool_db_access_seed.php` を無意識に叩けないようにした。
- これらの helper は `--host-side` を付けた明示実行だけを許し、未指定時は fail fast して再実行方法を案内する。
- `bootstrap_dbclasses.sh` に入れた last-resort gate と同じ方向で、host-only helper 導線の accidental use をさらに減らした。

## 背景

- `original-codes/` はすでに runtime / Docker / artifact mainline から外れている。
- ただし host-side export helper は still current な更新作業に使うため repo 内に残しており、help 文言だけでは casual use を完全には防げなかった。
- `bootstrap_dbclasses.sh` を explicit acknowledgment 必須にした次の段として、host-side export helper 側も同じ思想で寄せるのが自然だった。

## 実装

- `mtool/scripts/export_legacy_dataclass_reference.php`
- `mtool/scripts/export_legacy_dbtable_reference.php`
- `mtool/scripts/export_legacy_db_access_reference.php`
- `mtool/scripts/export_legacy_html_reference.php`
- `mtool/scripts/export_legacy_language_resource_reference.php`
  - usage example に `--host-side` を追加した。
  - `--host-side` option を必須化し、未指定時は fail fast するようにした。
- `mtool/scripts/export_legacy_table_schema_reference.php`
  - temporary schema helper でも `--host-side` を必須化した。
  - host-side DSN example と explicit execution の読み方を揃えた。
- `mtool/scripts/export_mtool_db_access_seed.php`
  - `--host-side` option を追加し、seed refresh も explicit host-side action として扱うようにした。
- `docs/internal/runtime-architecture.md`
- `docs/internal/generated-code-strategy.md`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - current docs / resume command examples を `--host-side` 前提に更新した。

## 検証

- `php -l` for:
  - `mtool/scripts/export_legacy_dataclass_reference.php`
  - `mtool/scripts/export_legacy_dbtable_reference.php`
  - `mtool/scripts/export_legacy_db_access_reference.php`
  - `mtool/scripts/export_legacy_html_reference.php`
  - `mtool/scripts/export_legacy_language_resource_reference.php`
  - `mtool/scripts/export_legacy_table_schema_reference.php`
  - `mtool/scripts/export_mtool_db_access_seed.php`
- `php ... --help`
  - representative helper で `--host-side` が usage / options / notes に出ること
- `php ...` without `--host-side`
  - fail fast して再実行 guidance を出すこと

## 含意

- host-only helper は残っていても、day-to-day mainline command としてはさらに見えにくくなった。
- runtime / generator / Docker が `original-codes/` を読まないだけでなく、repo 内 helper も `host-side explicit action` として統一的に読める。
- 次に見るべきは helper の archive 可否か、runtime replacement 本体の残 lane であり、「うっかり legacy export を叩く」リスクではなくなる。

## 次

1. host-only helper inventory を `explicit export` / `last-resort staging` / `provenance metadata` に分けて、さらに archive 候補を絞る
2. runtime replacement の simple lane 残件があれば直置換で進める
3. complex/new form は sample gate を足しながら promote 範囲を広げる
