# 2026-05-19 Resume Prompt

最新版のコピペ用再開 prompt。これは `docs/reports/2026/2026-0519-original-codes-host-only-enforcement.md` と同日までの self-generated runtime / seed export 関連 report から切り出した派生文書であり、背景と確認ログの正本は各 report 側にある。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。

今日の到達点:
- `ApacheHostSetting` / `ApacheHostSettingTemplate` は runtime/self-loop scope から明示除外済み
- legacy blob/file contract は `legacy-delegate` 固定で、UI / repository / seed export guard も追加済み
- `export_mtool_db_access_seed.php` は `--sql-dump=original-codes/mtool.sql` を受け取り、host-side dump 直読で seed 3 file を再生成できる
- `mtool/docker/mariadb/config-seed/019_*`, `020_*`, `022_*` は current canonical state に更新済み
- `make mtool-self-loop-check` は pass
- `make test` は `35 tests / 212 assertions` で pass
- `original-codes/` は host-side reference only に固定した
- root `compose.yaml` から `original-codes/` mount は削除済みで、`web-admin` / `web-lab` container から `/var/www/original-codes` は見えない
- migration sample / top-level declaration test は `tests/fixtures/legacy-dbclasses/` の curated copy を入力に使う

重要な前提:
- `original-codes/` を Docker runtime / artifact bundle / current runtime input に戻さない
- legacy dump が必要な export は host-side 明示実行で扱う
- file-based sample / migration test の入力追加が必要なら `tests/fixtures/legacy-dbclasses/` に必要最小限だけコピーする
- `original-codes/` の ZIP 化は今は不要。boundary enforcement を維持する

次のタスク候補:
1. `original-codes/` をまだ前提にしている dev-time helper を棚卸しし、host-only のまま残すものと retire するものを分ける
2. 特に `mtool/scripts/bootstrap_dbclasses.sh` を current 運用で残すべきか、legacy recovery helper として隔離すべきか判断する
3. `export_legacy_*_reference.php` 群の help / docs も「host-side dump path only」に揃える
4. 上の整理が済んだら、self-generated runtime 置換後の残っている legacy recovery 導線をさらに減らす

最初に読むべき文書:
- docs/reports/2026/2026-0519-original-codes-host-only-enforcement.md
- docs/reports/2026/2026-0519-file-blob-runtime-delegate-decision.md
- docs/reports/2026/2026-0519-self-generated-runtime-reference-promotion.md
- docs/reports/2026/2026-0519-runtime-reference-terminology-cleanup.md
- docs/internal/generated-code-strategy.md
- docs/internal/runtime-architecture.md
- tests/README.md

最初に確認するコマンド:
- rg -n "original-codes|bootstrap_dbclasses|sql-dump|legacy_dbclasses_path" mtool tests docs sample compose.yaml
- docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'
- docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'
- make test
- make mtool-self-loop-check

必要なら再実行する host-side export:
- php mtool/scripts/export_mtool_db_access_seed.php --project-key=MTOOL --host=127.0.0.1 --port=33061 --db-password=config_root_local_2026 --output-dir=mtool/docker/mariadb/config-seed --dbclasses-root=mtool/reference/dbclasses --sql-dump=original-codes/mtool.sql
```
