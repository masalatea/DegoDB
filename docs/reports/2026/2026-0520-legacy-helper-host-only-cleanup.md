# 2026-05-20 Legacy Helper Host-Only Cleanup

## 結論

- `mtool/scripts/bootstrap_dbclasses.sh` は削除せず残した。ただし current runtime の通常導線ではなく、host-side の `legacy recovery helper` として位置づけを固定した。
- `export_legacy_*_reference.php` 群の usage / option help / validation message を「host filesystem 上の dump path を明示して使う helper」に揃えた。
- `apply_config_sample_seed.sh` の repo root 解決から `original-codes` 文字列を外し、`original-codes/` 依存の棚卸しでノイズになる incidental match を減らした。
- `Makefile` と current docs も同じ boundary に合わせ、`original-codes/` を Docker runtime input に戻さない前提を補強した。

## 背景

- `2026-0519-original-codes-host-only-enforcement.md` で、`original-codes/` は host-side reference only とし、Docker runtime / current runtime input / artifact bundle から外した。
- その後も `bootstrap_dbclasses.sh` と `export_legacy_*_reference.php` の help は、host-side helper であることが十分に見えず、`rg -n "original-codes|bootstrap_dbclasses|sql-dump|legacy_dbclasses_path"` でも incidental な残骸が混ざっていた。
- 特に `apply_config_sample_seed.sh` は `original-codes` を使っていないのに repo root 判定文字列として含んでおり、次の棚卸しで誤認しやすかった。

## 実装

- `mtool/scripts/bootstrap_dbclasses.sh`
  - `--help` / `--source-dir` / `--target-dir` を追加した。
  - default source が host-side `original-codes/mtool_lib/dbclasses` であること、base Docker runtime では使わないこと、mainline は `make promote-runtime-reference` であることを help と実行ログに明記した。
  - repo root 解決を `SCRIPT_DIR/../..` ベースへ単純化した。
- `mtool/scripts/export_legacy_dataclass_reference.php`
- `mtool/scripts/export_legacy_dbtable_reference.php`
- `mtool/scripts/export_legacy_db_access_reference.php`
- `mtool/scripts/export_legacy_html_reference.php`
- `mtool/scripts/export_legacy_language_resource_reference.php`
  - `--sql-dump` の説明を `host filesystem 上の legacy SQL dump path` に揃えた。
  - usage に `original-codes/` は host-side reference only であり、base Docker runtime には mount しないことを追記した。
  - `--sql-dump` 未指定や dump file 未発見時の error も host-side path 前提が分かる文言に揃えた。
- `mtool/scripts/apply_config_sample_seed.sh`
  - repo root 解決から `original-codes` 判定を除去した。
- `Makefile`
  - `bootstrap-dbclasses` target を host-side legacy recovery helper と説明するようにした。
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
  - `bootstrap-dbclasses` と `export_legacy_*_reference.php` の位置づけを host-side helper 前提へ揃えた。

## 判断

- `bootstrap_dbclasses.sh` は current default runtime には不要だが、legacy recovery のためにまだ価値がある。
- ただし「便利だから残す」ではなく、「host 側で明示実行する recovery helper」としてしか使わないことを可視化しておく必要がある。
- `export_legacy_*_reference.php` も同様で、current app runtime が参照するのは生成済み catalog だけにし、dump 読み出しは host-side の更新作業に限定する。
- incidental な `original-codes` 文字列は audit の質を下げるため、意味のないものから先に消す。

## 検証

- `bash mtool/scripts/bootstrap_dbclasses.sh --help`
- `php mtool/scripts/export_legacy_dataclass_reference.php --help`
- `php mtool/scripts/export_legacy_dbtable_reference.php --help`
- `php mtool/scripts/export_legacy_db_access_reference.php --help`
- `php mtool/scripts/export_legacy_html_reference.php --help`
- `php mtool/scripts/export_legacy_language_resource_reference.php --help`
- `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
- `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`
- `make test`
- `make mtool-self-loop-check`

## 次

- `original-codes/` をまだ読んでいる helper は、今回のように `host-only recovery/export` と `runtime input` を明確に分けて判断する。
- 次段では、`bootstrap_dbclasses.sh` 自体をさらに縮退できるか、または curated recovery snapshot へ置き換えられるかを検討する。
