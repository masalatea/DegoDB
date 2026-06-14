# 2026-05-20 Runtime Reference Status Check

## 結論

- promoted runtime reference と latest runtime artifact の同期状態を、その場で確認できる CLI / Make target を追加した。
- `php mtool/scripts/show_runtime_reference_status.php --require-current` または `make mtool-runtime-reference-status REQUIRE_CURRENT=1` で、latest artifact が未 promote のまま stale になっていれば即座に検出できる。
- current state は `status=up-to-date`、promoted artifact / latest artifact ともに `20260520-022959-3e593819` で一致している。

## 追加したもの

- `mtool/app/runtime_reference_status.php`
  - runtime reference manifest と latest artifact manifest を読み、`up-to-date` / `stale-reference` / `reference-missing-provenance` / `artifact-history-missing` などを判定する helper を追加した。
- `mtool/scripts/show_runtime_reference_status.php`
  - status を JSON で出す CLI を追加した。
  - `--require-current` を指定すると、status が `up-to-date` 以外で exit 1 になる。
- `Makefile`
  - `make mtool-runtime-reference-status [REQUIRE_CURRENT=1]` を追加した。
- `mtool/app/project_source_output_detail_page.php`
  - `MTOOL / RUNTIME-DBCLASSES` detail に runtime reference status card を追加した。
- `tests/Integration/RuntimeReferenceStatusTest.php`
  - status 判定の分岐を temp fixture で固定した。

## 確認結果

- `php mtool/scripts/show_runtime_reference_status.php`
  - pass
  - `status=up-to-date`
- `make mtool-runtime-reference-status REQUIRE_CURRENT=1`
  - pass
- `make test`
  - `42 tests / 1072 assertions`
- `make mtool-self-loop-check`
  - pass
  - new artifact: `20260520-022959-3e593819`
- `php mtool/scripts/promote_runtime_reference.php --artifact-key=20260520-022959-3e593819 --requested-by=codex`
  - pass
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
  - pass
  - `artifact_key=20260520-022959-3e593819`
  - `unclassified_non_plain_items=0`

## 含意

- self-loop を流した直後に new artifact ができても、promote し忘れを `status=stale-reference` として検出できる。
- runtime manifest に `artifact_key` provenance を入れたことで、promote 状態確認が doc ではなく machine-readable JSON で回るようになった。
- ただし `artifact-history-missing` は依然として起こりうる。`work/` を消した後の durable rollback source 問題は、この status check だけでは解決しない。
