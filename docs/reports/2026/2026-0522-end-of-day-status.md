# 2026-05-22 End Of Day Status

## 結論

- 2026-05-22 の停止点として、tutorial runtime lane を `sample10-dbaccess-mini-crud-flow` まで current 化し、`sample09` / `sample10` の pack report、catalog / README / roadmap / Makefile / PHPUnit 導線まで揃えた。
- tutorial lane の current baseline は `sample01` から `sample10` までの 10 pack で、`make sample10-pack-runtime-test` が `OK (1 test, 26 assertions)`、`ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` が `OK (77 tests, 2297 assertions)`。
- runtime reference 自体は今日触っておらず、promoted artifact は引き続き `20260521-023351-d52e8c8b`。ただし `work/` 側の artifact history が無いため、`php mtool/scripts/show_runtime_reference_status.php --require-current` の見え方は 2026-05-21 の `up-to-date` ではなく、2026-05-22 時点では `reference-snapshot-only` になっている。

## 2026-05-22 の最終状態

- `original-codes/` は host-side reference only のまま維持
- `sample/tutorials/` は `sample01-simple-table-runtime` から `sample10-dbaccess-mini-crud-flow` まで current
- `sample10-dbaccess-mini-crud-flow`
  - `SupportTicket` 1 table
  - `GetSupportTicketList` / `GetSupportTicket` / `InsertSupportTicket` / `UpdateSupportTicket` / `DeleteSupportTicket`
  - `reference/` は actual published output を durable copy 済み
- `sample09-dbaccess-aggregate-report` の pack report を backfill 済み
- `mtool/app/sample_pack_catalog.php`、`tests/bootstrap.php`、`tests/Integration/SamplePackCatalogTest.php`、`sample/README.md`、`sample/tutorials/README.md`、`tests/README.md`、`tests/Integration/README.md`、`docs/sample-tutorial-roadmap.md` は sample10 反映済み
- Makefile は tutorial lane 用に `sample10-pack-runtime-test` / `sample10-runtime-output-test` を追加済み
  - historical な `sample10-output-test` は `pattern05` 互換 layer としてそのまま維持
- `make help | sed -n '1,80p'` に `sample10-pack-runtime-test` が表示される
- full suite は `77 tests / 2297 assertions` に増加

## runtime / rollout の現状

- promoted runtime reference artifact
  - `20260521-023351-d52e8c8b`
- `php mtool/scripts/show_runtime_reference_status.php --require-current`
  - JSON 上は `ok=true`
  - current status は `reference-snapshot-only`
  - `--require-current` 付きなので CLI exit code は non-zero
  - `needs_promote=false`
  - `durable_recovery_ready=true`
  - `latest_artifact=null`
  - `work artifact history is absent, but a durable snapshot of the promoted artifact is still available.`
- durable snapshot
  - `mtool/reference/runtime-reference-snapshots/MTOOL/RUNTIME-DBCLASSES/20260521-023351-d52e8c8b`
  - manifest / runtime manifest とも整合
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
  - `non_plain_items=36`
  - `unclassified_non_plain_items=0`
  - gate type はすべて `sample-test`
  - lane count は `companion-declarations=13`、`default-property=2`、`method-and-enum=12`、`method-only=5`、`top-level-declaration=3`、`wrapper-property-method=1`

## 2026-05-22 に確定した補足

- sample tutorial lane は当初 planned だった `sample10` まで current 化した
- `sample10` 追加後も tutorial / pattern lane の naming collision は起きていない
  - tutorial 側: `sample10-pack-runtime-test`
  - historical pattern 側: `sample10-output-test`
- local port 競合は依然ありうる
  - full suite は `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` を成功例として使う
  - focused `sample10` は local で `43191` も埋まっていたため、`18391/18392/43391/43392` で実行した
- sample10 確認用に一時的に上げた dedicated stack は最後に `down --remove-orphans` 済み

## 次回最初に見るべきこと

1. tutorial lane は `sample10` まで current なので、次は `sample11+` を何にするか決める
2. 候補は `proxy` / `HTML` / `LanguageResource` tutorial を user-facing lane に足すか、real project 由来 sample 棚卸しへ進むか
3. historical な `Sample9-22` / `check_sample*` 名は compat layer として残っているので、rename を触るなら target 衝突回避方針を先に決める
4. `runtime_reference_status` の current `reference-snapshot-only` をどう読むかを README や handoff で誤解なく書く
5. dbclass/runtime output と self-output artifact の zero-copy 判定 1 行説明を固める

## 参照

- `docs/reports/2026/2026-0522-sample09-dbaccess-aggregate-report-pack.md`
- `docs/reports/2026/2026-0522-sample10-dbaccess-mini-crud-flow-pack.md`
- `docs/sample-tutorial-roadmap.md`
- `tests/README.md`
- `docs/reports/2026/2026-0521-end-of-day-status.md`
- `docs/reports/2026/2026-0521-phase2-core-done-checklist.md`
