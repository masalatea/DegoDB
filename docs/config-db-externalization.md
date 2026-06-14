# Config DB Externalization

English companion:
This document records the current rules for moving `config_db` away from the local compose service. It explains the supported local and external lanes, the `APP_CONFIG_DB_*` contract, and the preflight and migration commands that keep the metadata store consistent.

この文書は、canonical metadata を保存する `config_db` を local compose service 以外へ逃がす時の current rule をまとめた恒久文書です。  
`make up` と `make up-external-config-db` の違い、`APP_CONFIG_DB_*` の使い方、preflight / migrate、advanced operation の boundary をここに集約します。

existing DB から output までの end-to-end 手順は [existing-db-to-output.md](existing-db-to-output.md) を正本にします。  
この文書は stage を繰り返さず、topology / preflight / migrate の detail だけを残します。

<a id="c0-when-to-read"></a>
## この文書を使う場面

- local default lane と external lane のどちらを使うか決める時
  - [existing-db-to-output.md#e1-choose-topology](existing-db-to-output.md#e1-choose-topology)
- chosen `config_db` が current schema で使えるか確認する時
  - [existing-db-to-output.md#e2-boot-and-preflight](existing-db-to-output.md#e2-boot-and-preflight)
- `schema_current=false` や lane 混在を切り分けたい時
  - [troubleshooting.md#t1-lane-mixups](troubleshooting.md#t1-lane-mixups)
  - [troubleshooting.md#t4-config-db-preflight](troubleshooting.md#t4-config-db-preflight)

この文書だけで mainline を再構成しないで、先に golden path の stage を確定してから detail として使います。

<a id="c1-role-map"></a>
## 役割の整理

- canonical metadata の保存先は `config_db`
- settings と canonical metadata の責務は `admin`
- `lab` は runtime 実験 / compare / Swagger viewer
- built-in `db` は `live-schema` import source と site default DB の意味のまま残る
- `db-lab` は editable import source であり canonical store ではない

<a id="c2-compose-topology"></a>
## compose topology

- base `compose.yaml` は `db-config` service を持たない
- default local は `compose.local-db-config.yaml` を重ねて `db-config` を含める
- `make up` / `make start` / `make ps` / `make logs` / `make config-db-preflight` / `make db-config-migrate` は local overlay lane
- `make up-external-config-db` は base `compose.yaml` だけを使い、local `db-config` を起動しない

manual compose で local default と同じ topology が必要な時だけ `-f compose.local-db-config.yaml` を明示で足します。

<a id="c3-local-default-lane"></a>
## local default lane

```bash
make env
make up
make config-db-preflight
make db-config-migrate
```

- `make up` は `compose.yaml + compose.local-db-config.yaml` を使う
- 起動後の URL 表示には `admin` / `lab` に加えて `lab-db-ui` も含まれる
- `make config-db-preflight` は `ok=true` かつ `schema_current=true` を current ready と読む

<a id="c4-external-lane"></a>
## external lane

external/shared env で local `db-config` を起動せず、external MariaDB を使う時は `APP_CONFIG_DB_*` を指定します。

```bash
APP_CONFIG_DB_HOST=external-db.example \
APP_CONFIG_DB_PORT=3306 \
APP_CONFIG_DB_NAME=config_app \
APP_CONFIG_DB_USER=config_app \
APP_CONFIG_DB_PASSWORD=secret \
make up-external-config-db
```

current supported target は次です。

- `make up-external-config-db`
- `make ps-external-config-db`
- `make logs-external-config-db`
- `make health-external-config-db`
- `make config-db-preflight-external-config-db`
- `make db-config-migrate-external-config-db`
- `make down-external-config-db`

external lane の `start/stop/reset/shell` parity target は current では増やしません。

<a id="c5-preflight-migrate"></a>
## preflight / migrate

official target:

```bash
make config-db-preflight
make db-config-migrate
```

direct CLI:

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=manual
docker compose exec -T web-admin php /var/www/mtool/scripts/migrate_config_db.php --requested-by=manual
```

external lane で同じ確認をしたい時:

```bash
make config-db-preflight-external-config-db
make db-config-migrate-external-config-db
```

preflight は次を current schema marker として見ます。

- required table が全部あること
- `project_source_outputs.spec_visibility` など latest/current column があること
- dropped legacy column が残っていないこと

`db-config-migrate` は current `APP_CONFIG_DB` target に `docker/mariadb/config-initdb/*.sql` を順番どおり apply します。

<a id="c6-app-db-vs-app-config-db"></a>
## `APP_DB_*` と `APP_CONFIG_DB_*`

- canonical metadata repository / admin CRUD は `config_db` を直接読む
- local compose では便宜上 `APP_DB_*` と `APP_CONFIG_DB_*` を同じ target に寄せる
- host/shared env では `APP_DB_*` と `APP_CONFIG_DB_*` を分けてもよい
- preflight は mismatch を warning として返すが、`config_db` schema が current なら fail しない

host/shared env で direct CLI を使う例:

```bash
APP_SITE=admin \
APP_DB_HOST=external-db.example \
APP_DB_PORT=3306 \
APP_DB_NAME=config_app \
APP_DB_USER=config_app \
APP_DB_PASSWORD=secret \
APP_CONFIG_DB_HOST=external-db.example \
APP_CONFIG_DB_PORT=3306 \
APP_CONFIG_DB_NAME=config_app \
APP_CONFIG_DB_USER=config_app \
APP_CONFIG_DB_PASSWORD=secret \
php mtool/scripts/check_config_db_bootstrap.php --requested-by=manual
```

<a id="c7-advanced-operations"></a>
## advanced operation の boundary

external lane で shell や一時 stop が必要な時だけ raw base compose を使います。

```bash
docker compose -f compose.yaml exec web-admin bash
COMPOSE_PROFILES=lab-db-ui docker compose -f compose.yaml stop
```

これは debug / ops 用の fallback であり、current mainline の専用 target には昇格しません。

## sample / scenario の compose merge

- sample pack / scenario helper は current local overlay を前提に merge する
- manual compose で local `db-config` が必要な時は `-f compose.local-db-config.yaml` を追加する

## 関連文書

- [existing-db-to-output.md](existing-db-to-output.md)
  - end-to-end の primary journey
- [current-supported-workflow.md](current-supported-workflow.md)
  - current mainline の中で external lane をどこで使うか
- [common-tasks.md](common-tasks.md)
  - 起動、preflight、migrate の短い手順
- [project-metadata-bundle.md](project-metadata-bundle.md)
  - canonical metadata bundle の export / import rule
