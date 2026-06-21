# AI Operator Contract / AI operator contract

English companion:
This document is the minimum operating contract for AI contributors. It fixes what to read first, which checkpoints to capture, what belongs in handoff payloads, and which boundaries not to cross while working through the existing-DB-to-output journey.

この文書は、AI が current docs だけで repo を再開し、既存 DB 接続から canonical metadata 永続化、設計、output publish までを迷わず辿るための最小 contract です。  
resume prompt や report は補助であり、この文書は `何を正本として読み、どの checkpoint を取り、どこを越えないか` を固定します。

## 最初に読む順番

1. [../../README.md](../../README.md)
2. [../README.md](../README.md)
3. [README.md](README.md)
4. [../start-here.md](../start-here.md)
5. [../choose-your-path.md](../choose-your-path.md)
6. [../existing-db-to-output.md](../existing-db-to-output.md)
7. [../storage-and-state-model.md](../storage-and-state-model.md)
8. [../current-supported-workflow.md](../current-supported-workflow.md)
9. [../common-tasks.md](../common-tasks.md)
10. [../troubleshooting.md](../troubleshooting.md)
11. 個別 rule が必要な時だけ [../project-metadata-bundle.md](../project-metadata-bundle.md) / [../config-db-externalization.md](../config-db-externalization.md)
12. それでも足りない時だけ `docs/reports/`

## source of truth の優先順位

1. date-less な `docs/` と `docs/internal/` の恒久文書
2. current contract test と script usage
3. 実装コード
4. `docs/reports/` の履歴

重要:

- `mtool/reference/legacy-*` は curated legacy reference only
- runtime / generator / Docker container の input には戻さない
- report にしか無い stable rule を見つけたら、まず恒久文書へ昇格すべきものか判断する

## run 開始時の標準 checkpoint

topology を選ぶ前後で、少なくとも次を取ります。

```bash
make help | sed -n '1,80p'
php mtool/scripts/show_runtime_reference_status.php --require-current
php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
make config-db-preflight
```

external `config_db` lane を選ぶ時は `make config-db-preflight-external-config-db` に切り替えます。  
local default と external lane の target は混ぜません。

## existing DB journey の実行 contract

既存 DB を接続して output まで進める時は [../existing-db-to-output.md](../existing-db-to-output.md) を正本にします。

その時の current contract:

- new named database source の一般用途登録は admin UI `/settings/database-sources` を supported lane とする
- current supported preview は `/projects/{project_key}/tables/import?...` の UI page を正本にする
- `import_project_tables.php` は apply であり preview ではない
- runtime read まで使う source は `supports_proxy_runtime_read=1` が必要
- OpenAPI share lane は authenticated viewer と admin artifact download のみ
- raw public alias key / raw `openapi.json` route は current supported lane に含めない

## bundle / secret の contract

- metadata import は `preview -> apply` の順で使う
- `database_sources` sidecar は optional
- source password は bundle に入れず `database-source-secrets` sidecar で渡す
- existing source は password preserve、new source + `has_password=true` は secret 未指定で fail-closed

<a id="a1-handoff-payload"></a>
## handoff / resume に残す最小 payload

resume prompt や issue comment が短くても、少なくとも次は 1 つの block で残します。

- `chosen lane`
  - `local-default` か `external-config-db`
- `project_key`
- `source_key`
  - source 登録済みか、runtime read に `supports_proxy_runtime_read=1` が必要かも含める
- `current_stage`
  - `stage4-previewed` / `stage5-applied` / `stage8-published` / `stage9-verified` / `stage10-handoff-ready`
- `artifact_key`
  - publish 後なら最後に確認した `artifact_key`
- `db_source_key`
  - Swagger / proxy verify に使った runtime source
- `config_db target`
  - local `db-config` か、external `APP_CONFIG_DB_*` target か
- `bundle_dir` と `database-source-secrets`
  - Stage 10 まで進んだ時だけ
- `latest_checks`
  - `make config-db-preflight`
  - docs test や full suite の最新結果

この payload があれば、AI は report を横断しなくても current docs と実環境確認だけで再開しやすくなります。

## docs を更新する条件

次のどれかを 2 回以上踏んだら、AI は report ではなく恒久文書更新を検討します。

- 同じ topology confusion が recurring する
- 同じ warning / error の切り分けを繰り返す
- primary journey の stage success marker が伝わらない
- report にしか current rule が無い

更新先の原則:

- 入口 / 主導線の不足: `README.md`、`docs/start-here.md`、`docs/choose-your-path.md`
- stable な操作手順: `docs/existing-db-to-output.md`、`docs/common-tasks.md`
- state / persistence の混乱: `docs/storage-and-state-model.md`
- warning / error の recurring issue: `docs/troubleshooting.md`
- 短い用語差: `docs/glossary.md`
- 恒久文書は日本語本文を正本にしつつ、冒頭に英語 companion を添える
- `docs/reports/` 配下の progress / handoff / resume prompt は日本語のみでよい

## run 終了時の標準 checkpoint

docs だけを変えた時は、少なくとも docs contract test を通します。

```bash
docker compose exec -T web-admin phpunit \
  --configuration /var/www/tests/phpunit.xml \
  /var/www/tests/Integration/DocsEntranceContractTest.php
```

code と supported lane も変えた時は、必要な focused test と full suite まで拡張します。

## AI が避けること

- report だけを読んで current rule を再構成すること
- `mtool/reference/legacy-*` を runtime input に戻すこと
- local default lane と external lane の target を混ぜること
- preview を見ずに bundle apply を first action にすること
- supported ではない public OpenAPI route を前提に進めること

## 関連文書

- [../existing-db-to-output.md](../existing-db-to-output.md)
  - 既存 DB から output までの主導線
- [../storage-and-state-model.md](../storage-and-state-model.md)
  - 何がどこに保存されるか
- [../troubleshooting.md](../troubleshooting.md)
  - stage ごとの warning / error 切り分け
