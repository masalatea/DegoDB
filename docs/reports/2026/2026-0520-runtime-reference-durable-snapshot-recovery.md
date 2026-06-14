# 2026-05-20 Runtime Reference Durable Snapshot Recovery

## 結論

- `make promote-runtime-reference` / `promote_runtime_reference.php` は、promoted runtime reference とは別に durable snapshot を `mtool/reference/runtime-reference-snapshots/MTOOL/RUNTIME-DBCLASSES/{artifact_key}/` へ保存するようにした。
- `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` と `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` を追加し、`work/` が消えた後でも promoted artifact を host-side reference root へ戻せるようにした。
- `show_runtime_reference_status.php` / UI は `durable_recovery_ready` と snapshot summary を返すようにし、latest artifact 同期と recovery 可否を同時に見られるようにした。
- restore 時は snapshot metadata file を current reference root に残さない。`mtool/reference/dbclasses/_support/` に残るのは `runtime-generation-manifest.json` だけである。

## 実装

- `mtool/app/runtime_storage_paths.php`
  - runtime reference snapshot root helper を追加した。
- `mtool/app/runtime_reference_promotion.php`
  - promote 後に snapshot capture を必須化した。
  - snapshot capture / restore helper を追加した。
  - `promote_tree` は callback 後の実 file count / bytes を返すようにした。
  - restore 時に `_support/runtime-reference-snapshot.json` を target root から除去するようにした。
- `mtool/scripts/promote_runtime_reference.php`
  - app reference root 基準で snapshot root を解決し、出力 JSON に snapshot path を含めるようにした。
- `mtool/scripts/restore_runtime_reference_snapshot.php`
  - durable snapshot から authoritative runtime reference を戻す CLI を追加した。
- `mtool/app/runtime_reference_status.php`
  - `reference_snapshot` summary、`durable_recovery_ready`、`durable_recovery_note` を追加した。
  - work artifact history が消えていても snapshot があれば `reference-snapshot-only` を返すようにした。
- `mtool/app/project_source_output_detail_page.php`
  - runtime reference status card に durable recovery 行を追加した。
- `mtool/scripts/bootstrap_dbclasses.sh`
  - self-generated snapshot restore を first choice とし、legacy bootstrap は last-resort helper だと help に明記した。

## 影響

- `work/artifacts/...` 自体は引き続き disposable だが、runtime reference manifest が指す promoted artifact は `work/` 外の durable snapshot から recover できる。
- これで「promote は通るが `make clean` 後の rollback source が無い」という gap は解消した。
- ただし `bootstrap_dbclasses.sh` を実際に archive / 削除するかは別判断にする。current では self-generated snapshot restore が使えない場合の last-resort helper として残す。

## 検証

- `make test`
  - `45 tests / 1102 assertions`
- `make mtool-self-loop-check`
  - pass
  - latest artifact: `20260520-031636-2131b210`
- `php mtool/scripts/promote_runtime_reference.php --artifact-key=20260520-031636-2131b210 --requested-by=codex`
- `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=20260520-031636-2131b210 --requested-by=codex`
- `php mtool/scripts/show_runtime_reference_status.php --require-current`
  - `status=up-to-date`
  - `durable_recovery_ready=true`
- `find mtool/reference/dbclasses/_support -maxdepth 1 -type f | sort`
  - `runtime-generation-manifest.json` のみ
- `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
- `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`

## 次

1. `bootstrap_dbclasses.sh` を本当に archive へ退避してよいか、snapshot restore 導線を前提に再評価する
2. `show_runtime_reference_status.php` の `reference-snapshot-only` を運用上どう扱うか決める
3. 残る host-only helper を、self-generated snapshot restore が代替できるものから縮退する
