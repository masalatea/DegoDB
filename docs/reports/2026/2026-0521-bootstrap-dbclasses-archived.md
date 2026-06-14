# 2026-05-21 bootstrap_dbclasses Archived

## 結論

- `bootstrap_dbclasses.sh` は `mtool/scripts/` から外し、`mtool/old/archived-bootstrap-dbclasses/bootstrap_dbclasses.sh` へ archive した。
- `make bootstrap-dbclasses` / `make bootstrap-dbclasses-runtime-reference` は current supported workflow から外し、archive path と snapshot-backed recovery を案内して fail fast する alias に変更した。
- これにより、current live host-only helper inventory から `last-resort staging` lane を外し、主系は `explicit export` / `provenance metadata` の 2 lane に絞った。

## 背景

- `2026-0521-bootstrap-dbclasses-staged-copy-use-inventory.md` の棚卸しで、current repo-supported workflow に `bootstrap_dbclasses.sh` 必須の具体例が残っていないことを確認していた。
- current runtime reference repair / rollback は `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` を主系に固定済みであり、legacy copy を authoritative runtime reference へ直接戻す導線は既に retire 済みだった。
- そのため `bootstrap_dbclasses.sh` を current mainline helper として残す理由はなく、必要になった時だけ手動で戻す archive 扱いへ寄せる方が妥当だった。

## 今回の変更

- `mtool/scripts/bootstrap_dbclasses.sh`
  - `mtool/old/archived-bootstrap-dbclasses/bootstrap_dbclasses.sh` へ移動
  - archive 済み helper であることが分かる文言に調整
- `Makefile`
  - `bootstrap-dbclasses`
  - `bootstrap-dbclasses-runtime-reference`
  - いずれも実行 helper ではなく fail-fast alias へ変更
- current docs
  - `docs/internal/generated-code-strategy.md`
  - `docs/internal/runtime-architecture.md`
  - `docs/internal/source-output-path-policy.md`
  - `docs/internal/mtool-admin-roadmap.md`
  - `docs/reports/2026/2026-0521-end-of-day-status.md`
  - `docs/reports/2026/2026-0521-resume-prompt.md`

## 境界

- これは `bootstrap_dbclasses.sh` の archive であり、dbclass/runtime output と self-output artifact の zero-copy 化とは別の helper cleanup である。
- current tool/runtime 側では `original-codes/` direct load zero を維持している。
- current target で zero にしたいのは dbclass/runtime output と self-output artifact 側の copied output であり、`mtool` 実処理コードの historical copy まで含めて消すことではない。
- sample / migration test 側の `tests/fixtures/legacy-dbclasses/` は migration gate 用 input fixture として別枠に置き、self-output artifact の zero-copy 判定とは分けて扱う。
