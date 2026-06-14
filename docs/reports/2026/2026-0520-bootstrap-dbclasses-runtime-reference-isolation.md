# 2026-05-20 Bootstrap DBClasses Runtime Reference Isolation

## later update

- same-day の `2026-0520-legacy-helper-host-only-cleanup.md` では `bootstrap_dbclasses.sh` を host-side helper として残すところまで整理した。
- その時点でも default target は `mtool/reference/dbclasses` のままで、誤実行すると authoritative runtime reference を legacy copy で上書きできる状態だった。
- そこで同日後続変更として、legacy recovery helper を runtime reference からさらに隔離した。

## 結論

- `mtool/scripts/bootstrap_dbclasses.sh` の default target を `mtool/reference/dbclasses` から `work/legacy-recovery/dbclasses` へ変更した。
- `mtool/reference/dbclasses` を legacy copy で上書きする経路は、`--apply-to-runtime-reference` または `make bootstrap-dbclasses-runtime-reference` を使う明示 override に限定した。
- これにより、day-to-day の `make bootstrap-dbclasses` は authoritative runtime reference を汚さず、host-side の staged recovery copy を作るだけになった。

## 背景

- `mtool/reference/dbclasses` は 2026-05-19 以降、promoted self-generated tree を置く durable runtime reference である。
- 一方 `bootstrap_dbclasses.sh` は legacy `original-codes/mtool_lib/dbclasses` を丸ごとコピーする helper で、current mainline では主系ではない。
- host-only helper と明記していても、default target が authoritative runtime reference のままだと「実行した瞬間に current baseline を legacy copy に巻き戻す」危険が残る。
- 今回の目的は helper 自体を消すことではなく、誤実行の blast radius を小さくすることである。

## 実装

- `mtool/scripts/bootstrap_dbclasses.sh`
  - default target を `work/legacy-recovery/dbclasses` に変更した。
  - `--apply-to-runtime-reference` を追加し、`mtool/reference/dbclasses` へ書くときだけ明示指定を要求するようにした。
  - `--target-dir` と `--apply-to-runtime-reference` の同時指定は禁止した。
  - 明示 flag なしで `mtool/reference/dbclasses` を target にした場合は拒否する guard を追加した。
  - 実行結果には `authoritative_runtime_reference` と mode を出すようにした。
- `Makefile`
  - `bootstrap-dbclasses` は staged recovery copy を作る安全側 target にした。
  - `bootstrap-dbclasses-runtime-reference` を追加し、legacy copy を authoritative runtime reference へ戻す emergency override を分離した。
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
- `docs/internal/source-output-path-policy.md`
  - `bootstrap-dbclasses` は `work/legacy-recovery/dbclasses` を作るだけであり、durable reference 更新は `promote-runtime-reference` が主系であることを明記した。

## 判断

- `bootstrap_dbclasses.sh` を完全削除すると、self-generated runtime reference が壊れたときの host-side 緊急復旧導線が消える。
- ただし default で `mtool/reference/dbclasses` を上書きできる状態は危険なので、helper は残しつつ default blast radius を `work/` に閉じ込めるのが妥当である。
- `work/legacy-recovery/dbclasses` は disposable だが、必要なら何度でも host-side source から再生成できるため、staged quarantine として十分である。
- durable reference の更新は、通常時は verified artifact promote、緊急時だけ explicit legacy override、という二段構えに分ける。

## 検証

- `bash mtool/scripts/bootstrap_dbclasses.sh --help`
- `bash mtool/scripts/bootstrap_dbclasses.sh`
- `bash mtool/scripts/bootstrap_dbclasses.sh --target-dir=mtool/reference/dbclasses`
  - `--apply-to-runtime-reference` なしでは拒否されることを確認
- `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
- `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`
- `make test`
- `make mtool-self-loop-check`

## 次

- `work/legacy-recovery/dbclasses` の staged copy まで含めて不要にできるなら、最終的には `bootstrap_dbclasses.sh` 自体を archive へ退避する。
- その判断は、self-generated runtime reference の rollback / repair を artifact 履歴だけで完結できるかを見てから行う。
