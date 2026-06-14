# 2026-05-20 Bootstrap DBClasses Runtime Reference Retirement

## later update

- same-day の `2026-0520-bootstrap-dbclasses-runtime-reference-isolation.md` では、`bootstrap_dbclasses.sh` の default blast radius を `work/legacy-recovery/dbclasses` へ閉じ込めつつ、`--apply-to-runtime-reference` / `make bootstrap-dbclasses-runtime-reference` を explicit override として残していた。
- その後 `2026-0520-runtime-reference-durable-snapshot-recovery.md` で、promoted self-generated artifact の durable snapshot capture / restore が入り、`work/` 消失後でも `mtool/reference/dbclasses/` を snapshot-backed に戻せるようになった。
- これで legacy copy を authoritative runtime reference へ直接流し込む emergency path は不要になったため、same-day 後続変更として retire した。

## 結論

- `mtool/scripts/bootstrap_dbclasses.sh` は stage-only helper に固定し、`mtool/reference/` 配下を target にできないようにした。
- `--apply-to-runtime-reference` は retired option とし、実行すると `restore_runtime_reference_snapshot.php` / `make restore-runtime-reference-snapshot` へ誘導して fail fast する。
- `make bootstrap-dbclasses-runtime-reference` も retired alias に変更し、authoritative runtime reference の repair / rollback は snapshot-backed recovery だけに揃えた。

## 背景

- `mtool/reference/dbclasses/` は current mainline で promoted self-generated runtime reference の durable root である。
- ここへ host-side `original-codes/mtool_lib/dbclasses` を直接戻せる経路を残すと、boundary 上は host-only でも current canonical reference を legacy copy で巻き戻せてしまう。
- 以前は `work/artifacts/...` が durable rollback source ではなかったため、その emergency path を完全には消せなかった。
- しかし same-day の durable snapshot restore 導線が入ったことで、「authoritative runtime reference を壊したら legacy copy を直上書きするしかない」という前提は消えた。

## 実装

- `mtool/scripts/bootstrap_dbclasses.sh`
  - usage/help から `--apply-to-runtime-reference` を外した。
  - retired option を受けたら、snapshot restore コマンドを案内して即座に失敗するようにした。
  - `--target-dir` を含め、`mtool/reference/` 配下を target にしようとした場合は拒否する guard を追加した。
  - 出力は staged recovery copy のみを前提にし、default staged target を明示するようにした。
- `Makefile`
  - `bootstrap-dbclasses-runtime-reference` は削除せず retired alias として残し、既存習慣から来た呼び出しでも誤って runtime reference を書き換えないようにした。
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
- `docs/internal/source-output-path-policy.md`
  - authoritative runtime reference の restore は snapshot-backed recovery だけを正規導線とするように表現を更新した。
- `docs/reports/2026/2026-0520-resume-prompt.md`
- `docs/reports/2026/README.md`
  - current 状態と本 report への導線を反映した。

## 判断

- 今回 retire したのは `legacy copy -> authoritative runtime reference` の direct overwrite path であり、`bootstrap_dbclasses.sh` 自体はまだ残している。
- helper の役割は、host-side で legacy dbclasses を staged copy として隔離し、diff / inspection / last-resort recovery preparation に使うことだけである。
- authoritative runtime reference を修復したい場合は、promoted self-generated snapshot を restore する。legacy copy は current canonical baseline へ直接昇格させない。

## 検証

- `bash mtool/scripts/bootstrap_dbclasses.sh --help`
- `bash mtool/scripts/bootstrap_dbclasses.sh --target-dir=mtool/reference/dbclasses`
  - `mtool/reference/` 配下 target が拒否され、snapshot restore guidance が出ることを確認する
- `make bootstrap-dbclasses-runtime-reference`
  - retired alias が fail fast し、`make restore-runtime-reference-snapshot ARTIFACT_KEY=...` を案内することを確認する
- `make test`
- `make mtool-self-loop-check`
- `php mtool/scripts/show_runtime_reference_status.php --require-current`

## 次

1. stage-only 化した `bootstrap_dbclasses.sh` 自体を archive へ退避できる条件を、残る host-only helper inventory と合わせて整理する
2. simple form direct replacement を広げ、complex/new form は sample gate を増やしてから promote する
3. `export_legacy_*_reference.php` 群と provenance metadata wording の残件をさらに減らす
