# 2026-05-26 External Config DB Parity Scope Decision

## 要約

- external config DB lane の `start/stop/reset/shell` parity target は current では追加しない。
- supported lane は `up-external-config-db` と、すでに追加済みの `ps/logs/health/config-db-preflight/db-config-migrate/down` で十分と判断した。
- advanced operation が必要な時だけ raw `docker compose -f compose.yaml ...` を使う。

## 判断理由

- `start`
  - convenience としては分かるが、current external lane では `up-external-config-db` で実質代替できる。
  - supported workflow の main path に必須ではない。
- `stop`
  - `start` と対になるが、current lane では `down-external-config-db` があれば運用上は足りる。
  - dedicated target を増やす価値が help のノイズ増加を上回らない。
- `reset`
  - local lane の `reset` は volume 削除を意味する。
  - external config DB lane で同名 target を置くと、external config DB 自体を reset するように誤読されやすい。
  - 実際には local `db-lab` volume しか消さず、名前と実体がずれるので current では避ける。
- `shell`
  - `admin-shell` / `lab-shell` / `db-lab-shell` は作れても、external lane に `db-config-shell` の対になる target は作れない。
  - 半端な parity を Makefile に持ち込むより、必要時だけ raw compose を明示する方が責務が明確である。
- `make help`
  - すでに target 数が多く、low-frequency debug target を増やし続ける価値が低い。

## current rule

- external config DB lane の current supported target
  - `make up-external-config-db`
  - `make ps-external-config-db`
  - `make logs-external-config-db`
  - `make health-external-config-db`
  - `make config-db-preflight-external-config-db`
  - `make db-config-migrate-external-config-db`
  - `make down-external-config-db`
- advanced operation が必要な時
  - 例: `docker compose -f compose.yaml exec web-admin bash`
  - 例: `COMPOSE_PROFILES=lab-db-ui docker compose -f compose.yaml stop`
  - current workflow には昇格しない

## 見直し条件

- external config DB lane を daily mainline として継続的に使う運用が増えた時
- `up-external-config-db` の build/start コストが問題になり、`start-external-config-db` の実益が明確になった時
- external lane 用 shell target を docs や smoke script から繰り返し使う要件が出た時

## docs note

- `README.md`
- `docs/start-here.md`
- `docs/common-tasks.md`
- `docs/current-supported-workflow.md`
  - external lane の advanced operation は raw `docker compose -f compose.yaml ...` を使う note を追加した。

## 検証

```bash
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `DocsEntranceContractTest`: `OK (6 tests, 106 assertions)`
- full suite: `OK (124 tests, 4470 assertions)`
