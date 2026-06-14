# 2026-05-20 Runtime Manifest Artifact Provenance

## 結論

- self-generated runtime bundle の `_support/runtime-generation-manifest.json` に `artifact_key` を埋めるようにした。
- `make promote-runtime-reference` / `promote_runtime_reference.php` でも target manifest を補正し、durable reference 側に最後に promote した verified artifact provenance が残るようにした。
- current promoted reference は `artifact_key=20260520-022959-3e593819` を保持し、rollout metadata と provenance を同じ manifest で追える。

## 変更

- `mtool/app/project_output_service.php`
  - artifact bundle 作成時に runtime manifest へ `artifact_key` を書き戻す helper を追加した。
- `mtool/app/runtime_reference_promotion.php`
  - promote 完了前に target runtime manifest へ `artifact_key` を補記する hook を入れた。
  - post-promote 補記が失敗した場合も rollback できるよう、promote tree の restore を強化した。
- `mtool/scripts/check_mtool_self_loop.php`
  - self-loop 生成 artifact の runtime manifest が、artifact 自身の `artifact_key` と一致することを検証するようにした。
- `mtool/scripts/show_runtime_replacement_rollout.php`
  - rollout inventory JSON に manifest の `artifact_key` を含めるようにした。
- `mtool/scripts/show_runtime_reference_status.php`
  - promoted runtime reference と latest runtime artifact の一致/乖離を `artifact_key` 単位で返す CLI を追加した。
- `tests/Integration/RuntimeReferencePromotionTest.php`
  - runtime manifest へ `artifact_key` を書けることを固定した。
- `tests/Integration/RuntimeReplacementRolloutLaneTest.php`
  - current reference manifest が stored rollout metadata と promoted artifact provenance を保持していることを固定した。
- `tests/Integration/RuntimeReferenceStatusTest.php`
  - `up-to-date` / `stale-reference` / provenance 欠落 / artifact history 消失の status 判定を固定した。

## 確認結果

- `make mtool-self-loop-check`
  - pass
  - new artifact: `20260520-022959-3e593819`
  - runtime manifest artifact key: `20260520-022959-3e593819`
- `php mtool/scripts/promote_runtime_reference.php --artifact-key=20260520-022959-3e593819 --requested-by=codex`
  - pass
- `php mtool/scripts/show_runtime_reference_status.php --require-current`
  - pass
  - `status=up-to-date`
  - `artifact_key=20260520-022959-3e593819`
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
  - pass
  - `artifact_key=20260520-022959-3e593819`
  - `unclassified_non_plain_items=0`
- `make test`
  - `42 tests / 1072 assertions`
- `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
  - pass
- `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`
  - pass

## 含意

- runtime reference を見れば、どの verified self-loop artifact を最後に promote したかがすぐ分かる。
- `show_runtime_reference_status.php` を見れば、latest artifact が未 promote のまま stale になっていないかもすぐ分かる。
- rollout lane inventory は `data_generation_items` の stored metadata と同じ manifest を見るだけで済み、reference が stale なら test で落ちる。
- これでも `work/artifacts/` 自体は durable rollback source ではないため、`bootstrap_dbclasses.sh` を archive できる条件は引き続き別問題である。
