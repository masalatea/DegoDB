# 2026-05-20 Bootstrap DBClasses Archive Readiness

## 結論

- `bootstrap_dbclasses.sh` は現時点では archive へ退避しない。
- 理由は単純で、`make promote-runtime-reference` は既存 artifact からの再 promote を行えるが、その artifact 履歴は `work/` 配下にあり `make clean` で消えるため、durable rollback source ではないからである。
- したがって現在の `bootstrap_dbclasses.sh` は、day-to-day mainline では不要でも、`work/` を失った後の host-side emergency recovery helper としてまだ意味がある。

## 背景

- `2026-0520-bootstrap-dbclasses-runtime-reference-isolation.md` で、`bootstrap_dbclasses.sh` の default target は `work/legacy-recovery/dbclasses` に隔離した。
- 同時に current mainline は `make promote-runtime-reference` へ寄せ、verified self-generated artifact を `mtool/reference/dbclasses/` へ昇格する導線を主系にした。
- ここで次に見るべき論点は、「主系 promote があるなら legacy recovery helper を archive へ退避できるか」だった。

## 確認結果

- `php mtool/scripts/promote_runtime_reference.php --artifact-key=...` は、`work/artifacts/source-outputs/MTOOL/<artifact_key>/bundle/.../mtool/dbclasses` を `mtool/reference/dbclasses/` へ promote できる。
- つまり、artifact が残っている間は `bootstrap_dbclasses.sh` を使わずに runtime reference を repair / rollback できる。
- しかし `work/artifacts/source-outputs/...` は `work/` の一部であり、path policy 上 disposable である。
- 実際に `make clean` は `work/` を完全削除するため、artifact 履歴そのものも durable ではない。
- この状態で `bootstrap_dbclasses.sh` まで消すと、`mtool/reference/dbclasses/` が壊れた直後に `work/` も空になっていた場合、repo 内に残る host-side recovery source がなくなる。

## 判断

- archive readiness はまだ `not ready` とする。
- archive へ退避してよい条件は、少なくとも次のいずれかが満たされた時点である。
  - promoted runtime artifact の rollback source を `work/` 外の durable tree に保持できる
  - `mtool/reference/dbclasses/` 自体の durable snapshot / restore 導線を別に持てる
  - `original-codes/` 非依存の curated recovery snapshot を repo 内に固定できる
- 現時点ではどれも未整備なので、`bootstrap_dbclasses.sh` は「通常運用では使わないが emergency 用には残す」が妥当である。

## 実装

- `mtool/scripts/bootstrap_dbclasses.sh`
  - `make promote-runtime-reference` と artifact history の限界を help に追記した。
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
- `docs/internal/source-output-path-policy.md`
  - promote 導線は主系だが、`work/` が disposable である以上 durable rollback ではないことを追記した。
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - `bootstrap_dbclasses.sh` を archive できない current reason を次回 prompt に反映した。
- `docs/reports/2026/README.md`
  - 本記録への index を追加した。

## 検証

- `bash mtool/scripts/bootstrap_dbclasses.sh --help`
- `rg -n "bootstrap-dbclasses|promote-runtime-reference|work/artifacts|make clean" docs mtool/scripts Makefile`

## 次

- 本当に `bootstrap_dbclasses.sh` を退役させるなら、先に durable rollback source を `work/` 外へ出す設計を切る。
- 逆にそこを急がないなら、この helper は今のまま host-side emergency 用として残し、日常導線だけ `promote-runtime-reference` を主系に保つ。
