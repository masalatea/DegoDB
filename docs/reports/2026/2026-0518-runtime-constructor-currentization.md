# 2026-05-18 Runtime Constructor Currentization

## Summary

- `RUNTIME-DBCLASSES` generator が `dbaccess-*` の empty constructor を legacy support へ delegate せず、generated no-op constructor を直接出力するようにした。
- self-loop summary の `legacy_delegate_function_count` は `101 -> 0` になり、dbaccess 側の delegate は実質的に解消した。
- self-host の残課題が constructor count に隠れず、data bootstrap / HTML bootstrap / authoritative runtime switch を次の主対象として追いやすくなった。

## Background

- 直前の self-loop では `generated_dbaccess_count=101` / `fallback_dbaccess_count=0` / `warnings=[]` なのに `legacy_delegate_function_count=101` が残っていた。
- manifest を見ると全 101 source で delegate 1 本ずつが残っていたが、実体はすべて `__construct()` だった。
- `mtool/reference/dbclasses/dbaccess-*.php` の constructor は全件 empty であり、legacy support を呼ぶ意味がなかった。

## Change

- `mtool/app/project_output_runtime_sql_generator.php`
  - `__construct()` を `legacy-delegate` ではなく `canonical-constructor` で生成するようにした。
  - body は empty のまま出力する。
- `mtool/app/project_output_runtime_generator.php`
  - `canonical-constructor` を delegate count に足さないようにした。
- `mtool/reference/mtool-self-loop-expected-output.json`
  - generation summary の `legacy_delegate_function_count` を `0` に更新した。
  - representative `dbaccess-Project.php` / `dbaccess-dbtable.php` hash を更新した。

## Verification

- `php -l mtool/app/project_output_runtime_sql_generator.php`
- `php -l mtool/app/project_output_runtime_generator.php`
- `docker compose exec -T web-admin php /var/www/mtool/scripts/check_mtool_self_loop.php --requested-by=codex`

確認結果:

- `generation_summary.mode=canonical-dbaccess-partial-sql-regenerated`
- `generated_dbaccess_count=101`
- `fallback_dbaccess_count=0`
- `sql_regenerated_dbaccess_count=100`
- `sql_regenerated_function_count=518`
- `canonical_helper_function_count=7`
- `canonical_data_class_count=64`
- `legacy_delegate_function_count=0`
- `warnings=[]`

## Implication

- dbaccess 側では bootstrap class を `_support/legacy-dbaccess/` に持ちながらも、method delegate はもう残っていない。
- self-host の blocker は `dbaccess` constructor ではなく、`data-*` の未 currentization と HTML/runtime 側の bootstrap dependency、さらに generated runtime を app 本体へ切り替える authoritative runtime switch に絞られる。
