# 2026-05-25 OpenAPI Spec Visibility Control

## 要約

- `project_source_outputs` に `spec_visibility` metadata を追加し、OpenAPI spec の viewer 露出を explicit control できるようにした。
- allowed value は `internal-only` と `disabled` の 2 つで、default は `internal-only` とした。
- `openapi.json` の internal filename は固定のまま維持し、防御の主軸は random suffix ではなく storage boundary と access control に置く方針を code / doc / UI に反映した。

## 変更点

- `docker/mariadb/config-initdb/011_project_source_output_metadata.sql`
  - `project_source_outputs.spec_visibility` column を追加した
- `docker/mariadb/config-initdb/029_project_source_output_spec_visibility.sql`
  - 既存 `db-config` 向けの ALTER を追加した
- `mtool/app/domain_validation.php`
  - `spec_visibility` の allowed value / caption / default resolver を追加した
  - source output form default と validation に `spec_visibility` を組み込んだ
- `mtool/app/source_output_repository_pdo.php`
  - fetch/create/update に `spec_visibility` を通した
- `mtool/app/project_source_output_new_page.php`
  - create form に `spec_visibility` selector を追加した
  - OpenAPI internal artifact の扱いを note として明記した
- `mtool/app/project_source_output_edit_page.php`
  - edit form と summary に `spec_visibility` を追加した
- `mtool/app/project_source_output_detail_page.php`
  - detail summary / field rows で `spec_visibility` を確認できるようにした
- `mtool/app/lab_swagger_service.php`
  - `artifact_strategy=openapi-json` かつ `spec_visibility!=disabled` の definition だけを viewer 対象にした
- `mtool/app/lab_swagger_page.php`
  - selected/all definitions が disabled の時に explicit notice を出すようにした
- `tests/Integration/OpenApiSourceOutputContractTest.php`
  - `spec_visibility` validation と viewer filtering の contract を追加した

## 検証

```bash
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
make db-config-migrate
```

- focused test: `OK (15 tests, 1627 assertions)`
- full suite: `OK (112 tests, 4162 assertions)`
- running local `db-config` にも `spec_visibility` column を反映済み

## 今の意味

- fixed `openapi.json` filename は current local stack では public static route ではなく、単体では主因の security hole ではない
- OpenAPI の viewer exposure は `ProjectSourceOutput` canonical metadata で制御できるようになった
- 次はこの方針を前提に、canonical metadata export/import と config DB externalization を実装する段階に入れる
