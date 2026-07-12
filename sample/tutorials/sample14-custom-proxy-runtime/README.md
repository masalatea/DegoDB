# sample14-custom-proxy-runtime

## 役割

- 役割: custom proxy metadata から PHP proxy server artifact を publish する tutorial sample pack
- project key: `SAMPLE14`
- custom proxy keys: `CATALOG-SUMMARY`, `TRANSACTION-PAIR`
- source output key: `CUSTOM-PROXY-SERVER`
- runtime root: `work/sample-packs/sample14-custom-proxy-runtime/`
- reference output: `reference/CUSTOM-PROXY-SERVER/`

`sample13` は single-function proxy target から `OPENAPI-JSON` を作る tutorial です。`sample14` は custom proxy metadata を使い、複数 DBAccess step を 1 つの generated proxy endpoint に束ねる流れに絞ります。`TRANSACTION-PAIR` は、2つの通常の generated DBAccess INSERT を1つの共有トランザクションで実行する Transaction Full の最小例です。

## 読み方

まず `make sample14-pack-runtime-test` を実行します。manual flow は `CUSTOM-PROXY-SERVER` publish だけを取り出して、custom proxy metadata と generated PHP server artifact の対応を見るためのものです。

## 起動

```sh
./sample/tutorials/sample14-custom-proxy-runtime/run.sh up
```

seed を再適用する場合:

```sh
./sample/tutorials/sample14-custom-proxy-runtime/run.sh apply-seed
```

## 検証

```sh
make sample14-pack-runtime-test
```

`sample14-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample14CustomProxyRuntimeOutputTest.php` を実行します。生成artifactのreference比較に加え、生成した `TRANSACTION-PAIR` endpoint を実際のMariaDBへHTTP実行し、2件commitと2 step目失敗時の全rollbackを確認します。

SQLite config store profile で同じ tutorial を検証:

```sh
make sample14-pack-runtime-test-sqlite
```

手元で軽く動かす場合は、DegoDB 自身の設計メタデータを `APP_CONFIG_STORE_DIR` 配下の SQLite file に保存できます。

```sh
APP_CONFIG_STORE_DIR=work/config-store-sample14-sqlite \
  ./sample/tutorials/sample14-custom-proxy-runtime/run-sqlite-config.sh up
```

## Seed 内容

- `projects`
  - `project_key=SAMPLE14`
- `project_source_outputs`
  - `source_output_key=CUSTOM-PROXY-SERVER`
  - `artifact_strategy=custom-proxy-server`
  - `target_binding_type=custom-proxy`
- `project_custom_proxies`
  - `custom_proxy_key=CATALOG-SUMMARY`
  - `basename=Catalog`
  - `name=Summary`
  - `auth_type=NoSecurity`
  - `custom_proxy_key=TRANSACTION-PAIR`
  - `basename=Transaction`
  - `name=Pair`
  - `in_transaction=1`
  - `continue_even_if_failed_to_insert=0`
- `project_custom_proxy_steps`
  - `dbtable.GetdbtableList`
  - `project_source_output.GetProjectSourceOutputList`
  - `sample14_transaction_item.InsertSample14TransactionItem` を2 step

`TRANSACTION-PAIR` では呼び出し側である generated Custom Proxy handler が transaction を開始します。各 generated DBAccess class は transaction を意識せず、共有 `$mtooldb` connection 上で通常の INSERT を実行します。両 step が成功した場合だけ commit され、必須 step が1つでも失敗すれば全体が rollback されます。

## 手動 flow

```sh
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample14-custom-proxy-runtime/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE14 --source-output-key=CUSTOM-PROXY-SERVER --requested-by=sample14-pack --publish
```

## Scope

- `sample14` は custom proxy server artifact の生成に絞る。
- proxy client generation は扱わない。
- token / auth / fail-closed behavior は `sample16` に分ける。
- generated PHP server bundle と build plan をreference比較する。
- generated `TRANSACTION-PAIR` endpointを実DBへ実行し、成功時は2件ともcommit、失敗時は先行INSERTもrollbackされることを検査する。

次は `sample15-project-metadata-export-import` で generated code ではなく project metadata bundle を扱います。
