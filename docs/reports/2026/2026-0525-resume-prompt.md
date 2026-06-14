<repo-root> の MTOOL rewrite 作業を再開してください。2026-05-26 の開始点として扱ってください。

現状:
- `original-codes/` は host-side reference only。runtime input に戻さない
- tutorial lane は `sample01` から `sample10` まで current
- `lab-db-ui` (Adminer) を追加済み。`make up` / `make start` で `admin` / `lab` / `lab-db-ui` の URL と login / DB credential が表示される
- admin 側の table import source に `lab-live-schema` を追加済み
- `db-lab` を編集してから admin 側 canonical metadata へ import できる
- `APP_LAB_DB_*` 対応と named source preflight probe は実装済み
- `Lab DB schema -> Admin import -> sync -> proxy/openapi output -> Lab Swagger Try It Out` は browser smoke まで到達済み
- generated proxy auth hardening として `ProjectToken` は fail-closed 化済み。`ProjectTokenOrGetFunc` は get-function fallback を維持
- `project_source_outputs.spec_visibility` を追加済み。allowed value は `internal-only` / `disabled`
- OpenAPI source output は default `internal-only` で authenticated viewer から見える。`disabled` にすると Lab Swagger viewer から隠れる
- fixed `openapi.json` filename は維持。current local stack では `work/source-outputs/...` の internal artifact であり public static file ではない
- running local `db-config` は `make db-config-migrate` 済みで `spec_visibility` column を持つ
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` は `112 tests / 4162 assertions` pass
- focused OpenAPI contract test は `15 tests / 1627 assertions` pass

重要な前提:
- 設定と canonical metadata の責務は `admin`
- `lab` は runtime 実験 / compare / review / 将来の Swagger UI
- `db-lab` は editable import source であり canonical store ではない
- external DB import は one-off DSN ではなく named DB source model に一般化して足す
- OpenAPI public exposure は default OFF の考え方を維持し、internal viewer capability と public raw route を分ける

次の優先タスク:
1. canonical metadata の project-scoped export bundle CLI を実装する
2. canonical metadata の import CLI を preview/apply 二段で実装する
3. export/import bundle から secret を分離するルールを決める
4. config DB externalization の bootstrap/preflight を設計し、`APP_CONFIG_DB_*` で外部 MariaDB を使えるようにする
5. その後に必要なら OpenAPI public alias key / raw delivery policy を検討する

最初に読む文書:
- `docs/reports/2026/2026-0525-openapi-auth-persistence-plan.md`
- `docs/reports/2026/2026-0525-openapi-spec-visibility-control.md`
- `docs/current-supported-workflow.md`
- `docs/common-tasks.md`
- `README.md`
- `tests/README.md`

最初に確認するコマンド:
- `php mtool/scripts/show_runtime_reference_status.php --require-current`
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
- `make help | sed -n '1,80p'`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
- `docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=MTOOL --source=lab-live-schema`

停止点の意味:
- OpenAPI / Swagger lane は一度 end-to-end で通り、その上で auth と exposure control の first hardening slice まで完了した
- 次は新しい viewer 機能追加ではなく、canonical metadata durability の export/import と config DB externalization が主線
