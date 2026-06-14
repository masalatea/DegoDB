# 2026-05-20 Unused Legacy DBClasses Helper Removal

## 結論

- `mtool/app/runtime_storage_paths.php` から `app_runtime_storage_legacy_dbclasses_relative_path()` / `root()` / `path()` を削除した。
- host-only boundary を維持したまま、current code から `original-codes/` へ到達する runtime path helper をなくした。
- `original-codes/` を読む current 導線は、`bootstrap_dbclasses.sh` と `export_legacy_*_reference.php` などの host-side script helper に限定された。

## 背景

- `2026-0519-original-codes-host-only-enforcement.md` で、`original-codes/` は host-side reference only とし、Docker runtime / test mainline / artifact bundle から外した。
- その後 `tests/fixtures/legacy-dbclasses/` への切り替えと `bootstrap_dbclasses.sh` の隔離を進めた結果、`app_runtime_storage_legacy_dbclasses_*()` は live code / test で使われなくなっていた。
- 未使用 helper を残したままだと、「runtime helper がまだ `original-codes/` を返してよい」という誤読を再び生みやすい。

## 実装

- `mtool/app/runtime_storage_paths.php`
  - `original-codes/mtool_lib/dbclasses` を返す 3 helper を削除した。
  - curated fixture 用の `app_runtime_storage_legacy_dbclasses_fixture_*()` はそのまま残した。
- `docs/reports/2026/2026-0519-original-codes-host-only-enforcement.md`
- `docs/reports/2026/2026-0519-runtime-reference-terminology-cleanup.md`
- `docs/reports/2026/2026-0520-original-codes-helper-inventory.md`
  - helper が「まだ残っている」と読める箇所を same-day 後続状態に合わせて補正した。
- `docs/reports/2026/README.md`
  - 本記録への index を追加した。

## 判断

- 今回消したのは recovery script ではなく、runtime path helper である。
- recovery が必要なときは引き続き host-side script helper を明示実行すればよく、app 共通 helper に `original-codes/` path を残す必要はない。
- これにより「runtime helper は curated fixture / promoted reference / work root だけを返す」という境界がはっきりした。

## 検証

- `rg -n "app_runtime_storage_legacy_dbclasses_(relative_path|root|path)" mtool tests sample`
- `php -l mtool/app/runtime_storage_paths.php`
- `make test`
- `make mtool-self-loop-check`
- `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
- `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`

## 次

- 残る `original-codes/` 参照は host-side helper script と provenance metadata なので、次は helper 自体の更なる縮退か、metadata wording の整理を見ればよい。
