# 2026-05-20 bootstrap_dbclasses Last-Resort Gate

## 結論

- `bootstrap_dbclasses.sh` は host-side legacy staging helper として残すが、無引数では実行できないようにした。
- 実行には `bash mtool/scripts/bootstrap_dbclasses.sh --last-resort` または `make bootstrap-dbclasses ACKNOWLEDGE_LAST_RESORT=1` の明示確認を必須にした。
- これにより、snapshot-backed runtime reference repair を主系に保ったまま、legacy staging 導線をさらに accidental use しにくくした。

## 背景

- `2026-0520-bootstrap-dbclasses-runtime-reference-retirement.md` で、authoritative runtime reference の repair / rollback は `restore-runtime-reference-snapshot` に揃えた。
- その後も `bootstrap_dbclasses.sh` と `make bootstrap-dbclasses` 自体は簡単に叩ける状態で、day-to-day helper のように見える余地が残っていた。
- current 判断では helper 自体はまだ archive しないが、少なくとも casual path として残す理由はない。

## 実装

- `mtool/scripts/bootstrap_dbclasses.sh`
  - `--last-resort` option を追加した。
  - option が無い実行は fail fast し、snapshot restore と explicit rerun 例を案内する。
  - usage / notes も `--last-resort` 前提へ更新した。
  - 実行ログに `acknowledged_last_resort: yes` を追加した。
- `Makefile`
  - `bootstrap-dbclasses` target は `ACKNOWLEDGE_LAST_RESORT=1` を必須にした。
  - 未指定時は fail fast し、`restore-runtime-reference-snapshot` を主系として案内する。
  - `SOURCE_DIR` / `TARGET_DIR` override は、確認変数を付けた時だけ従来通り使える。
- `docs/internal/source-output-path-policy.md`
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
  - current guidance を explicit acknowledgment 前提へ揃えた。

## 検証

- `bash mtool/scripts/bootstrap_dbclasses.sh --help`
- `bash mtool/scripts/bootstrap_dbclasses.sh`
  - fail fast し、`--last-resort` 再実行例と snapshot restore を案内すること
- `make bootstrap-dbclasses`
  - fail fast し、`ACKNOWLEDGE_LAST_RESORT=1` を案内すること
- `bash mtool/scripts/bootstrap_dbclasses.sh --last-resort --target-dir=work/tmp/bootstrap-dbclasses-last-resort-check`
  - staged legacy copy が作成されること

## 含意

- `bootstrap_dbclasses.sh` はまだ archive readiness 未達だが、少なくとも current mainline helper ではなくなった。
- 以後の runtime reference repair は、さらに明確に `promote-runtime-reference` / `restore-runtime-reference-snapshot` 側へ寄る。
- archive 可否の次の判断は、「repo 内 durable rollback source を `mtool/reference/` 外でも持つか」ではなく、「last-resort helper を本当に repo 内へ残す必要があるか」に絞りやすくなる。

## 次

1. `bootstrap_dbclasses.sh` を repo 内に残す最終条件を、reference snapshot と emergency recovery policy の観点でさらに詰める
2. snapshot restore で代替できる host-only helper 導線を引き続き減らす
3. runtime replacement は simple lane の直置換と complex lane の sample gate を並行で広げる
