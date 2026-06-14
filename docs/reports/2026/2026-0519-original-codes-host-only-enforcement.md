# 2026-05-19 original-codes Host-Only Enforcement

## 結論

- `original-codes/` は host-side reference only として扱い、base Docker runtime / current runtime / artifact bundle には含めない方針を明文化した。
- root `compose.yaml` から `original-codes/` の bind mount を外し、`web-admin` / `web-lab` container から `/var/www/original-codes` が見えない状態を確認した。
- migration sample / top-level declaration test は `original-codes/` を直接読まず、`tests/fixtures/legacy-dbclasses/` に置いた curated file copy を使うように切り替えた。
- `original-codes/` を ZIP 化する必要は現時点ではない。誤用の主因は runtime から見えていたことであり、boundary を閉じたので directory のまま host-side reference として残してよい。

## 背景

- 既存 docs には「new runtime / generator は `original-codes/` を直接読まない」と散発的に書いてあった。
- しかし実際の root `compose.yaml` は `./original-codes:/var/www/original-codes:ro` を mount しており、方針と実装がずれていた。
- 同時に migration sample / legacy declaration test は `original-codes/mtool_lib/dbclasses/*.php` を直接入力として使っており、`make test` も Docker mount へ暗黙依存していた。

## 実装

- `compose.yaml`
  - `web-admin` / `web-lab` から `./original-codes:/var/www/original-codes:ro` を削除した。
- `AGENTS.md`
  - `original-codes/` は host-side reference only、Docker / runtime / artifact に含めないことを明記した。
- `mtool/scripts/export_mtool_db_access_seed.php`
  - `--sql-dump` は host filesystem 上の path を前提とし、base Docker runtime には `original-codes/` を mount しないことを help に明記した。
- `mtool/app/runtime_storage_paths.php`
  - `tests/fixtures/legacy-dbclasses/` を指す fixture path helper を追加した。
- `tests/fixtures/legacy-dbclasses/`
  - sample / migration test が使う最小限の legacy `data-*.php` copy を追加した。
  - `data-TestPattern.php`
  - `data-CompareOutput.php`
  - `data-da.php`
  - `data-dataclass.php`
  - `data-dbtablecolumns.php`
  - `data-Req.php`
  - `data-Project.php`
  - `data-ProjectUser.php`
  - `data-SpecContent.php`
  - `data-htmlTemplate.php`
- `mtool/scripts/lib/sample9_*`, `sample10_*`, `sample11_*`, `sample12_*`, `sample13_*`
  - sample input source を fixture copy へ切り替えた。
- `tests/Integration/LegacyTopLevelDeclarationMigrationTest.php`
  - legacy declaration migration の入力を fixture copy へ切り替えた。
- `sample/README.md`, `sample/sample9-13*/README.md`, `tests/README.md`
  - sample/test input の正本が `tests/fixtures/legacy-dbclasses/` 側であることを追記した。
- `docs/internal/generated-code-strategy.md`, `docs/internal/runtime-architecture.md`
  - legacy dump export は host-side 明示実行で扱い、base Docker runtime には `original-codes/` を mount しないことを追記した。

## 検証

- container visibility
  - `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
  - `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`
  - pass
- self-loop
  - `make mtool-self-loop-check`
  - pass
  - artifact key: `20260519-054248-d405c524`
- regression
  - `make test`
  - `35 tests, 212 assertions`
- host-side legacy dump export
  - `php mtool/scripts/export_mtool_db_access_seed.php --project-key=MTOOL --host=127.0.0.1 --port=33061 --db-password=config_root_local_2026 --output-dir=mtool/docker/mariadb/config-seed --dbclasses-root=mtool/reference/dbclasses --sql-dump=original-codes/mtool.sql`
  - pass
  - `class_count=101`
  - `function_count=626`
  - `selectlist_sort_order_backfill_count=76`

## 判断メモ

- `original-codes/` を ZIP 化すると、host-side 調査や dev-time export まで毎回展開手順が必要になる。
- 今回のように
  - runtime からは見えない
  - sample / test は fixture copy を使う
  - legacy dump export は host-side 明示実行に限定する
 という boundary が守られていれば、directory のままでも十分に安全である。
- 将来さらに縮退したくなったら、ZIP 化より先に「残すべき host-side tool を curated dump / fixture / reference へ置き換え終えたか」を確認するべきである。

## 残り

- `mtool/scripts/bootstrap_dbclasses.sh` は依然として `original-codes/mtool_lib/dbclasses` を recovery source として読む。current default runtime では主系ではないため、host-only recovery helper として残すか、別手段へ置き換えるかを次段で決める。
- `export_legacy_*_reference.php` 群も host-side dump path を前提にした文言整理がまだ残る。
- 当日時点では `app_runtime_storage_legacy_dbclasses_*()` helper を host-side recovery / audit 用 path として残していた。
- same-day 後続変更で live code / test から未使用と確認できたため、この helper は `mtool/app/runtime_storage_paths.php` から削除した。test mainline は引き続き fixture path を使う。
