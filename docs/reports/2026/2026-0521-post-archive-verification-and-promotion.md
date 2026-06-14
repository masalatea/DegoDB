# 2026-05-21 Post Archive Verification And Promotion

## 結論

- `bootstrap_dbclasses.sh` archive 後に `make test` と `make mtool-self-loop-check` を実行し、current baseline は壊れていないことを確認した。
- verification で生成された artifact `20260521-012440-66c2a545` を `make promote-runtime-reference ARTIFACT_KEY=20260521-012440-66c2a545` で promote した。
- promote 後の `php mtool/scripts/show_runtime_reference_status.php --require-current` は `up-to-date`、`needs_promote=false`、`durable_recovery_ready=true`。

## 実行内容

1. helper / runtime 状態の確認
   - `php mtool/scripts/show_runtime_reference_status.php --require-current`
   - `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
   - `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
   - `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`
   - `make bootstrap-dbclasses`
   - `make bootstrap-dbclasses-runtime-reference`
   - `bash mtool/old/archived-bootstrap-dbclasses/bootstrap_dbclasses.sh --help`
2. verification run
   - `make test`
     - `54 tests / 1156 assertions` で pass
   - `make mtool-self-loop-check`
     - pass
     - latest artifact `20260521-012440-66c2a545` を生成
3. promote candidate run
   - `make promote-runtime-reference ARTIFACT_KEY=20260521-012440-66c2a545`
   - promote 後 status を再確認し、`up-to-date` に戻ったことを確認

## 現在状態

- promoted runtime reference artifact
  - `20260521-012440-66c2a545`
- latest artifact
  - `20260521-012440-66c2a545`
- promoted reference status
  - `up-to-date`
- durable recovery
  - `true`
- `mtool/reference/dbclasses/_support/` 直下
  - `runtime-generation-manifest.json` のみ
- snapshot root
  - `mtool/reference/runtime-reference-snapshots/MTOOL/RUNTIME-DBCLASSES/20260521-012440-66c2a545`

## 境界の読み方

- current zero-copy goal は `dbclass/runtime output` と `self-output artifact` を対象にする。
- `mtool` 実処理コードの historical copy はこの goal の対象外である。
- `tests/fixtures/legacy-dbclasses/` は migration gate 用 input fixture として別枠に置き、self-output artifact の zero-copy 判定とは分けて扱う。
