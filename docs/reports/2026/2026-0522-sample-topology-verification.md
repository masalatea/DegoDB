# 2026-05-22 Sample Topology Verification

## 結論

- sample pack の user-facing lane は `sample/tutorials/`、internal migration guard lane は `sample/internal-patterns/` として current live 導線を揃えた。
- `sample/tutorials/sample01-simple-table-runtime/README.md` の起動例 / seed 適用例を tutorial lane の canonical path に更新し、`sample/` / `tests/` / `mtool/` / `Makefile` の live 範囲から旧 `sample/patterns/` 参照は消えた。
- `make help` は `sample01-pack-runtime-test` と `pattern01-output-test` ... `pattern14-output-test` を current canonical target として表示する。
- `make test ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092` は `68 tests / 1922 assertions` で pass した。

## 実施内容

- `sample01` tutorial README の `run.sh up` / `run.sh apply-seed` 例を `./sample/tutorials/sample01-simple-table-runtime/run.sh ...` へ更新した。
- `rg -n "sample/patterns/sample01-simple-table-runtime|sample/patterns" sample tests mtool Makefile -g '!docs/**'` で live 範囲の旧 path 参照が残っていないことを確認した。
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only` で complex lane が `sample-test=36` のまま維持されていることを確認した。
- `make help | sed -n '1,80p'` で tutorial / internal pattern の canonical target が help 出力に現れることを確認した。
- `find mtool/app mtool/scripts tests/Integration -type f \\( -name '*.php' \\) -print0 | xargs -0 -n1 php -l` で PHP syntax error が無いことを確認した。

## runtime reference status

- `php mtool/scripts/show_runtime_reference_status.php --require-current` は current では exit code `1` になり、status は `reference-snapshot-only` を返した。
- 理由は `work artifact history is absent` であり、promoted runtime reference 自体が壊れているわけではない。
- promoted reference と durable snapshot はどちらも artifact `20260521-023351-d52e8c8b` を指しており、`needs_promote=false`、`durable_recovery_ready=true` のまま。
- したがって current blocking issue は無く、sample topology 再編と test lane の verification は完了とみなしてよい。
