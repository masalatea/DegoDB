# Troubleshooting

English companion:
This guide explains the most common warnings, errors, and confused states on the current supported workflow. It is organized by symptom so you can quickly distinguish lane mixups, import issues, config DB problems, OpenAPI visibility assumptions, and reference-state mismatches.

この文書は、current supported workflow 上で遭遇しやすい warning / error / confused state を短く切り分けるための恒久文書です。  
古い helper や archived lane の例外対応はここには入れず、今の mainline で再現しやすいものだけを残します。

existing DB から output までの stage 順は [existing-db-to-output.md](existing-db-to-output.md) を参照してください。  
ここでは stage ごとに「詰まりやすい点」だけを残します。

## 先に確認すること

- まず `mtool/reference/legacy-*` を runtime input に戻していないか確認する
- local default なのか external config DB lane なのかをはっきりさせる
- report の断片ではなく、まず date-less な恒久文書を source of truth として読む

<a id="t1-lane-mixups"></a>
## T1. Lane Mixups

よくある症状:

- `make up-external-config-db` で起動したあとに `make config-db-preflight` を打つ
- local overlay lane の `start/stop/ps/logs` target を external lane に混ぜる

意味:

- compose topology と target の前提がずれている
- `config_db` 自体の failure ではなく lane selection の失敗であることが多い

対処:

- local default lane なら `make up` / `make config-db-preflight` / `make db-config-migrate`
- external lane なら `make up-external-config-db` / `make config-db-preflight-external-config-db` / `make db-config-migrate-external-config-db`
- advanced operation が必要な時だけ raw `docker compose -f compose.yaml ...` を使う

<a id="t2-source-missing-from-import-options"></a>
## T2. Source Missing From Import Options

よくある症状:

- `/settings/database-sources` には row がある
- しかし `/projects/{project_key}/tables/import?source=named-live-schema:{source_key}` が期待どおりに使えない

まず確認すること:

- `supports_live_schema_import=1` になっているか
- source key が built-in key (`db` / `config_db` / `lab_db`) と衝突していないか
- `config_db` preflight が current か

対処:

- source row を edit して `supports_live_schema_import` を有効にする
- source 一覧で対象 row が保存されていることを確認する
- 先に `make config-db-preflight` か `make config-db-preflight-external-config-db` を通す

<a id="t3-import-preview-apply-confusion"></a>
## T3. Import Preview / Apply Confusion

よくある症状:

- `table stale` / `column stale` を削除予定と読めばよいのか迷う
- preview を見ずに CLI apply を打ってしまう

意味:

- current supported preview は UI page
- current CLI の `import_project_tables.php` は apply

対処:

- 先に `/projects/{project_key}/tables/import?source=named-live-schema:{source_key}` を開く
- `source tables` / `canonical tables` / `table new` / `table changed` / `table stale` を確認する
- scope が大きすぎる時は `table=` または `--table=` で 1 table に絞る

<a id="t4-config-db-preflight"></a>
## T4. Config DB Preflight

よくあるコマンド:

```bash
make config-db-preflight
make config-db-preflight-external-config-db
```

意味:

- `schema_current=false` は required table / required column / dropped legacy column のどれかが current ではない
- `APP_DB_*` と `APP_CONFIG_DB_*` の mismatch warning は、schema が current なら即 failure ではない

まず確認すること:

- local default lane なのか external lane なのか
- 直前に migration を当てたか
- warning が schema mismatch なのか env mismatch なのか

対処:

- local default lane:

```bash
make up
make db-config-migrate
make config-db-preflight
```

- external lane:

```bash
make db-config-migrate-external-config-db
make config-db-preflight-external-config-db
```

補足:

- host/shared env で admin lane を直接 debug する時は、必要に応じて `APP_DB_*` と `APP_CONFIG_DB_*` を同じ target に寄せる
- preflight warning だけで `schema_current=true` なら、canonical metadata repository 自体は `config_db` を読めている可能性が高い

<a id="t5-missing-secret-env-in-bundle-preview"></a>
## T5. Missing Secret Env In Bundle Preview

よくあるコマンド:

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php \
  --bundle=/tmp/mtool-project-metadata-bundle-MTOOL \
  --mode=preview \
  --database-source-secrets=/tmp/mtool-project-metadata-secrets.json \
  --requested-by=manual
```

意味:

- `database-source-secrets` file の `password_env` が未解決
- parse error ではなく preview warning に落としている

対処:

- secret file の `password_env` / `env` / `env_name` が指す環境変数を export してから rerun する
- local-only の一時運用なら literal password file を使ってよいが、commit はしない
- generated `database-source-secrets.template.json` を元に env reference file を作るのが current

重要な読み方:

- existing source は secret 未指定でも current password を preserve する
- new source で `has_password=true` の場合だけ apply 時に fail-closed になる

<a id="t6-runtime-source-selection-in-swagger-and-proxy"></a>
## T6. Runtime Source Selection In Swagger And Proxy

よくある症状:

- viewer に `db_source_key=...` を付けても notice 付きで auto-select に戻る
- published proxy route に `db_source_key=...` を付けると `422` が返る

意味:

- 指定した source が unknown
- または `supports_proxy_runtime_read=1` の source ではない

対処:

- runtime DB source に使う key は `supports_proxy_runtime_read=1` の source に限定する
- viewer では URL query を消して selector から選び直す
- direct proxy call では invalid key を送らない

current rule:

- viewer の invalid query は notice 付きで auto-select に戻す
- published proxy route の invalid query は `422` を返す

<a id="t7-openapi-visibility-and-raw-route-assumptions"></a>
## T7. OpenAPI Visibility And Raw Route Assumptions

意味:

- `project_source_outputs.spec_visibility=disabled` なら authenticated viewer からも隠れる
- default の `internal-only` は authenticated viewer 向けであり public static file ではない
- current では raw alias route や public `openapi.json` delivery は supported lane に入れていない

対処:

- viewer で見せたい時は `spec_visibility` を `internal-only` にする
- 共有は authenticated viewer か admin artifact download を使う
- docroot 直下や `/artifacts/openapi/...` のような public raw route を前提にしない

<a id="t8-external-lane-advanced-operations"></a>
## T8. External Lane Advanced Operations

意味:

- これは不足ではなく current decision
- external lane は `up/ps/logs/health/config-db-preflight/db-config-migrate/down` までを supported target にしている

対処:

- advanced operation が必要な時だけ raw base compose を使う

```bash
docker compose -f compose.yaml exec web-admin bash
COMPOSE_PROFILES=lab-db-ui docker compose -f compose.yaml stop
```

<a id="t9-reference-snapshot-only"></a>
## T9. Reference Snapshot Only

よくあるコマンド:

```bash
php mtool/scripts/show_runtime_reference_status.php --require-current
```

意味:

- durable snapshot は残っている
- ただし `work/` 側の latest artifact history が current mainline として揃っていない
- strict check では non-zero exit になる

まず確認すること:

- promoted artifact key が何か
- `work/artifacts/source-outputs/` に latest artifact history があるか
- 今ほしいのが「recover」なのか「latest artifact の再 promote」なのか

対処:

- latest artifact を authoritative reference に戻したい時は `make promote-runtime-reference ARTIFACT_KEY=...`
- durable snapshot から recover したい時は `make restore-runtime-reference-snapshot ARTIFACT_KEY=...`
- これは `config_db` や DB schema failure ではなく、runtime reference の同期状態の問題として読む

## 関連文書

- [existing-db-to-output.md](existing-db-to-output.md)
  - stage 順そのものの正本
- [common-tasks.md](common-tasks.md)
  - よく使う短いコマンド集
- [current-supported-workflow.md](current-supported-workflow.md)
  - current mainline 全体
- [storage-and-state-model.md](storage-and-state-model.md)
  - 何がどこに保存されるか
- [project-metadata-bundle.md](project-metadata-bundle.md)
  - bundle export / import / secret policy
- [config-db-externalization.md](config-db-externalization.md)
  - `config_db` external lane と preflight / migrate
- [internal/README.md](internal/README.md)
  - runtime reference / promote / restore を含む内部 reference
