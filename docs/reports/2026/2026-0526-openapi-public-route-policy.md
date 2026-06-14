# 2026-05-26 OpenAPI Public Route Policy

## Status

- policy review: `DONE`
- status updated at: `2026-05-27`
- current decision:
  - public alias key / raw delivery route は current slice では実装しない
  - supported lane は authenticated viewer と admin artifact download に固定する
  - 新しい共有要件が出た時だけ再検討する

## 要約

- OpenAPI public alias key / raw delivery route は current slice では実装しない。
- supported lane は `authenticated viewer` と `admin artifact download` の 2 つに固定する。
- `lab_swagger` / `lab_published_single_proxy` は middleware でも auth-required route として扱い、`/artifacts/openapi/...` のような public raw route は持たないことを contract に固定した。

## 判断

- current local stack では `openapi.json` は `work/source-outputs/...` または artifact bundle 内の internal artifact であり、docroot 直下の public static file ではない。
- 共有 needs がある場合も、今すぐ public alias key / revocation / cache policy を増やすより、authenticated viewer と admin artifact download で足りる。
- public raw route を入れると key 発行単位、rotate/revoke、storage boundary、cache invalidation を同時に決める必要があり、現時点では要件に対して重い。

## 変更

- `mtool/app/router.php`
  - `lab_swagger` と `lab_published_single_proxy` を middleware の auth-required route に追加した。
- `mtool/app/project_source_output_new_page.php`
- `mtool/app/project_source_output_detail_page.php`
  - OpenAPI spec は public raw route / public alias key route を持たず、共有は admin artifact download を使う current rule を明記した。
- `tests/Integration/OpenApiSourceOutputContractTest.php`
  - public raw route が未実装であること
  - viewer / published proxy / admin artifact download が auth-required lane であること
    を固定した。
- `docs/current-supported-workflow.md`
- `docs/common-tasks.md`
  - OpenAPI share boundary を `authenticated viewer` / `admin artifact download` に整理した。

## current rule

- viewer: `/runs/swagger/{project_key}` は authenticated lane のみ。
- published proxy: `/runs/proxy/{project_key}/{source_output_key}/{endpoint_filename}` も authenticated lane のみ。
- file download: `/projects/{project_key}/source-outputs/artifacts/{artifact_key}/download` は admin/config role が必要な tar.gz artifact download。
- raw spec public route: 未実装。
- public alias key: 未実装。

## 将来の解禁条件

- anonymous または semi-private share URL が実際に必要になった時
- key 発行単位、rotate/revoke、cache policy、auditability を先に決められる時
- raw `openapi.json` ではなく dedicated route contract として ownership を持てる時

## 検証

```bash
php -l mtool/app/router.php
php -l mtool/app/project_source_output_new_page.php
php -l mtool/app/project_source_output_detail_page.php
php -l tests/Integration/OpenApiSourceOutputContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `OpenApiSourceOutputContractTest`: `OK (16 tests, 1646 assertions)`
- full suite: `OK (120 tests, 4382 assertions)`
