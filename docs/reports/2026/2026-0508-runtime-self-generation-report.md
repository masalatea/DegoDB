# 2026-0508 Runtime Self-Generation Report

## 結論

- `RUNTIME-DBCLASSES` は、`dbaccess-*` 側では non-constructor delegate が `0` のまま維持され、`data-*` 側の canonical overlay は `58 -> 64` まで伸ばせた。
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da` も intentional derived DTO として canonical 生成へ寄せられ、data-side mismatch は解消した。
- その後の first slice 実装により、`MTOOL` では upstream の `dbtable` / `dbtablecolumns` import と `dataclass` / `dataclassfields` sync が実行可能になった。したがって次段の優先は upstream 未実装ではなく、connector 拡張と generator 側の正式接続整理である。

## 今日の更新点

- `shared/project_output_runtime_generator.php`
  - upstream `dbtable` / `dataclass` metadata が存在する class では、`source_of_truth=sync-bootstrap` の designer row から導出した property を upstream property set で絞り込むよう更新した。
  - これにより `CompareOutputSearchCache`、`ProjectSourceOutputSavedFiles`、`TestPatternSelection`、`UploadDropboxPathCache` は class 個別 supplement なしで canonical plain DTO 化できるようになった。
  - `TestCondition` では physical table / dataclass metadata に存在しない stale property `ConditionOrder` が自動的に除外されるようになった。
  - `daCustomProxyFunc_leftouterjoin_dafunc_and_da` では `AuthType` / `SingleGetFuncPID` を intentional derived DTO の追加 property として扱い、bootstrap copy ではなく generated class へ寄せた。
- `shared/project_db_access_sync_service.php`
  - method catalog に存在しない `sync-bootstrap` function row は stale metadata として prune するよう更新した。
  - `MTOOL` の sync では `GetLanguageResourceListWithAdditionalGroup` と `UpdatedbtablecolumnsColumnListOrderSupposedToBe` の 2 row を prune できることを確認した。
- `project_source_outputs.target_binding_type`
  - source output の用途区分を explicit metadata として持つ列を追加した。
  - `MTOOL` では `RUNTIME-DBCLASSES=runtime`、`DBIMPORT-PROXY-SERVER/CLIENT=custom-proxy` を backfill し、UI / helper はこの値を優先する。
  - 未設定 row だけは互換のため `artifact_strategy` / `class_type` から effective scope を fallback 判定する。
- `shared/db_access_repository_pdo.php` / `shared/project_db_access_function_detail_page.php` / `shared/project_source_output_detail_page.php`
  - legacy `dafuncSimpleProxySourceOutputTarget` の canonical 受け皿として `project_db_access_function_source_output_targets` を追加した。
  - function detail から single-function proxy target source output key を保存できるようにし、source output detail から target function 一覧を見られるようにした。
  - ただし current canonical source output は 3 件だけで、legacy temporary DB 上の target row は 374 件ある。`Project 1 (Mtool)` に絞っても `17` 件中 `ApacheHostSetting` が `8` 件、残りが `Project` 6 / `PaypalSubscription` 1 / `DropboxUploadToken` 1 なので、backfill / remap はまだ保留にした。
  - self-loop 上の project discovery は `DB-GETPROJECTLIST` custom proxy が `GetProjectbyOwnerOrUserSecurityList` を `DBIMPORT-PROXY-*` へ載せているため、single-function proxy target の未統合は current loop の blocker ではない。
- `shared/legacy_table_schema_reference.php` / `shared/project_table_import_service.php`
  - `project_db_access_function_source_output_targets` を legacy `dafuncSimpleProxySourceOutputTarget` 対応として self-host managed scope へ追加した。
  - `tables/import` apply は managed source tables だけを更新対象にするよう修正し、live schema に unmanaged table が増えても duplicate insert しないようにした。
- `scripts/check_mtool_self_loop.php` / `Makefile` / `shared/reference/mtool-self-loop-expected-output.json`
  - `MTOOL` 専用の self-loop smoke/acceptance check を追加した。
  - live import `21 tables / 239 columns same`、data class sync `40 classes / 387 fields same`、`RUNTIME-DBCLASSES` manifest expected summary に加えて、representative generated file digest を baseline JSON と照合できる。
- `scripts/check_mtool_proxy_outputs.php` / `Makefile` / `shared/reference/mtool-proxy-expected-output.json`
  - `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` 専用の proxy output acceptance check を追加した。
  - build plan summary、artifact summary、representative proxy file digest を baseline JSON と照合できる。
- `scripts/export_mtool_db_access_seed.php` を再実行し、`019_project_db_access_class_function_seed.sql` を class `101` / function `626` の状態へ更新した。
- `RUNTIME-DBCLASSES` を再生成し、latest artifact を `20260511-053817-487dac40` に更新した。
- `README.md`、`docs/internal/generated-code-strategy.md`、`2026-0508-rebuild-status.md` を latest count に合わせて更新した。

## 最新確認値

- artifact key: `20260511-053817-487dac40`
- generation mode: `canonical-dbaccess-partial-sql-regenerated`
- `generated_dbaccess_count=101`
- `fallback_dbaccess_count=0`
- `canonical_function_count=626`
- `sql_regenerated_dbaccess_count=100`
- `sql_regenerated_function_count=518`
- `canonical_helper_function_count=7`
- `canonical_data_class_count=64`
- `legacy_delegate_function_count=101`

補足:

- legacy delegate `101` は constructor のみであり、non-constructor delegate は `0`。
- warning は `0`。
- live import steady state は `21 tables / 239 columns same`。
- data class sync steady state は `40 classes / 387 fields same`。
- representative generated file digest baseline 8 件も一致した。
- `sync_project_db_access.php --project-key=MTOOL` の結果は class updated `101` / function updated `626` / stale sync-bootstrap functions pruned `2`。
- 最新 manifest は `generated/source-outputs/MTOOL/20260511-053817-487dac40/.../_support/runtime-generation-manifest.json` にある。
- proxy output acceptance check では `DBIMPORT-PROXY-SERVER` artifact key `20260511-053821-0511609e`、`DBIMPORT-PROXY-CLIENT` artifact key `20260511-053821-8616e3c5` を確認した。
- proxy build plan summary は両方とも `custom_proxy_count=4` / `step_count=8` / `unresolved_step_count=0`、generated catalog は `101 paired / 101 total`。
- proxy artifact summary は server `23 files / 88724 bytes`、client `29 files / 26127 bytes`、custom layer は両方 `bundle-scaffold`。
- representative proxy file digest baseline は server/client 各 `5` 件一致した。

## 今回 canonical 化できた class 群

- `CompareOutputSearchCache`
- `ProjectSourceOutputSavedFiles`
- `TestCondition`
- `TestPatternSelection`
- `UploadDropboxPathCache`
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da`

これらは主に次の 3 理由で今回取り込めた。

- upstream へ import / sync した `dbtable` / `dataclass` metadata を plain DTO 導出へ接続した。
- `sync-bootstrap` 由来でも upstream metadata に存在しない stale property は plain DTO 導出対象から除外した。
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da` のような derived DTO は、親 DTO 継承と追加 property の形で generator から再現した。

## 分類結果

- upstream metadata 追加で取り込んだもの
  - `CompareOutputSearchCache`: `CheckedDT`
  - `ProjectSourceOutputSavedFiles`: `PID`, `CreatedDateTime`
  - `TestPatternSelection`: `TestConditionPID`
  - `UploadDropboxPathCache`: `PID`
- stale `sync-bootstrap` property を upstream metadata で除外して取り込んだもの
  - `TestCondition`: `ConditionOrder`
    - physical table / dataclass metadata には存在しない。
- intentional derived DTO として取り込んだもの
  - `daCustomProxyFunc_leftouterjoin_dafunc_and_da`: `AuthType`, `SingleGetFuncPID`
    - physical table ではなく `daCustomProxyFuncData` 継承の derived DTO として扱い、bootstrap と同じ declared property list を generator で再現した。

## 次の作業

1. upstream canonical slice を runtime generator 側の supplement 置換にどこまで接続できるか確認する。
2. `MTOOL` 固定の live import を、将来の connector 拡張を見据えた DB product boundary へ整理する。
3. `daCustomProxyFunc_leftouterjoin_dafunc_and_da` の `AuthType` / `SingleGetFuncPID` を長期的に `daCustomProxy` / auth policy metadata 側へ寄せるか整理する。
4. `sync` 後の warning `0` 状態を維持できるか確認し、self-generated runtime 切り替え条件を再評価する。
5. `project_db_access_function_source_output_targets` の legacy backfill / remap 方針を決める。`PaypalSubscription` / `DropboxUploadToken` は後段へ回し、`Project` 6 件については canonical Mtool proxy source output を新設するか、legacy 導線として defer するかを整理する。

## 参考コマンド

```bash
docker compose exec -T web-admin php /var/www/scripts/check_mtool_self_loop.php --requested-by=codex
docker compose exec -T web-admin php /var/www/scripts/check_mtool_proxy_outputs.php --requested-by=codex
```
