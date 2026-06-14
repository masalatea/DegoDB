# 2026-05-27 Resume Prompt

2026-05-27 にそのままコピペして再開するための prompt。  
停止点は 2026-05-26 の終了時点です。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。2026-05-26 の停止点、2026-05-27 の開始点として扱ってください。

現状:
- broad rewrite current wave の active execution plan は `docs/reports/2026/2026-0527-broad-rewrite-temporary-closure-plan.md` の 1 本だけに固定済み
- broad rewrite の current active milestone は `M1. runtime contract truth normalization`
- `M2. DBACCESS wrapper/base migration`、`M3. canonical generated data-* wrapper/base migration`、`Close. verification / docs / status freeze` は `PENDING`
- `Close` では一般/permanent docs (`README.md`, `docs/*.md`) を日英併記へ寄せる。`docs/reports/` 配下の progress / handoff は日本語のみでよい
- `M1` から `M3` まで完了した時点を中間完了、`Close` 完了を current wave の status freeze として扱う
- `original-codes/` は host-side reference only。runtime input に戻さない
- tutorial lane は `sample01` から `sample10` まで current
- `Lab DB schema -> Admin import -> sync -> proxy/openapi output -> Lab Swagger Try It Out` は browser smoke まで到達済み
- generated proxy auth は hardening として `ProjectToken` を fail-closed 化済み。`ProjectTokenOrGetFunc` は get-function fallback を維持し、full auth redesign は current slice では見送る
- `project_source_outputs.spec_visibility` を追加済み。allowed value は `internal-only` / `disabled`
- OpenAPI source output は default `internal-only` で authenticated viewer から見える。`disabled` にすると Lab Swagger viewer から隠れる
- fixed `openapi.json` filename は維持。current local stack では internal artifact であり public static file ではない
- OpenAPI public alias key / raw route は検討済みで、current slice では実装しない。supported share lane は authenticated viewer と admin artifact download のみ
- canonical metadata の project-scoped export bundle CLI は実装済み
- canonical metadata の import CLI は `preview/apply` 二段で実装済み
- bundle secret は本体から分離済み。optional `database-source-secrets` file と generated `database-source-secrets.template.json` を使う
- separate secrets file は literal password に加えて `{ "password_env": "ENV_NAME" }` の env reference を使える
- existing source は secret 未指定で password preserve、new source + `has_password=true` は fail-closed
- `APP_CONFIG_DB_*` override の config DB preflight / migrate は実装済み
- admin canonical metadata repository は `config_db` を直接読む。built-in `db` は `live-schema` import source / site default DB の意味のまま残る
- admin `APP_DB_*` と `APP_CONFIG_DB_*` の mismatch は preflight warning 扱いで、`config_db` schema が current なら fail しない
- root compose の `web-admin` / `web-lab` は `db-config` 起動順依存を持たない
- base `compose.yaml` から `db-config` service 定義を外し、default local 用に `compose.local-db-config.yaml` を追加済み
- `make up` / `make start` / `make config-db-preflight` などの local lane は `compose.yaml + compose.local-db-config.yaml` を使う
- `make up-external-config-db` は base `compose.yaml` だけを使い、external `APP_CONFIG_DB_*` を向けたまま local `db-config` を起動せずに `web-admin` / `web-lab` / `db-lab` と `lab-db-ui` を上げられる
- external lane の minimal follow-up target として `make ps-external-config-db` / `make logs-external-config-db` / `make health-external-config-db` / `make config-db-preflight-external-config-db` / `make db-config-migrate-external-config-db` / `make down-external-config-db` を追加済み
- external lane の `start/stop/reset/shell` parity は current では増やさない判断にした。advanced case は raw `docker compose -f compose.yaml ...` を都度使う
- `README.md` / `docs/start-here.md` / `docs/common-tasks.md` / `docs/current-supported-workflow.md` に external lane の raw compose fallback note (`docker compose -f compose.yaml ...`) を追加済み
- sample/helper 系の compose stack 解決は `mtool/scripts/list_compose_stack_files.sh` に共通化済み。sample pack runner / seed apply / compose smoke / runtime smoke / phpunit runner はここから local overlay を読む
- `show_compose_access_urls.sh` は current では base-only default のまま維持する。`compose.local-db-config.yaml` を足しても出力差分は無く、必要時だけ `--compose-file=...` を使う判断にした
- current docs (`README.md`, `docs/start-here.md`, `docs/current-supported-workflow.md`, `docs/common-tasks.md`) と tutorial sample README の manual compose 例も local overlay wording に更新済み
- `docs/choose-your-path.md` を追加済み。goal-based な contributor 入口として `README.md` / `docs/start-here.md` / `docs/README.md` から辿れる
- `docs/project-metadata-bundle.md` と `docs/config-db-externalization.md` を追加済み。bundle / externalization の stable rule は report ではなく恒久文書から読める
- `docs/troubleshooting.md` を追加済み。current supported lane の warning / error はここから切り分けられる
- `make help` の local-only target wording を current 化済み
- running local `db-config` は `make db-config-migrate` 済み
- `make config-db-preflight` は `ok=true`, `schema_current=true`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ConfigDbExternalizationContractTest.php` は `5 tests / 59 assertions` pass
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/LabDbIngressContractTest.php` は `4 tests / 52 assertions` pass
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php` は `6 tests / 118 assertions` pass
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SamplePackCatalogTest.php` は current manual compose wording guard を含めて pass
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php` は `16 tests / 1646 assertions` pass
- `make sample-pack-compose-smoke` は `17 pack(s)` pass
- `make ps-external-config-db` は current local stack 上でも pass
- `make health-external-config-db` は admin / lab とも `ok=true`
- `make config-db-preflight-external-config-db` は `ok=true`, `schema_current=true`
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` は `124 tests / 4482 assertions` pass
- `php mtool/scripts/show_runtime_reference_status.php --require-current` は `status=reference-snapshot-only`。`work` artifact history は無いが durable snapshot recovery は ready
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only` は `non_plain_items=36`, `unclassified_non_plain_items=0`

重要な前提:
- 設定と canonical metadata の責務は `admin`
- `lab` は runtime 実験 / compare / review / Swagger viewer
- `db-lab` は editable import source であり canonical store ではない
- external DB import は one-off DSN ではなく named DB source model に一般化して扱う
- OpenAPI public exposure は default OFF の考え方を維持し、internal viewer capability と public raw route を分ける

再開条件:
1. 新しい auth requirement が出た時だけ `proxy auth v2` を再検討する
2. OpenAPI public alias key / raw delivery は新しい共有要件が出た時だけ再検討する

最初に読む文書:
- `docs/reports/2026/2026-0527-broad-rewrite-temporary-closure-plan.md`
- `docs/project-metadata-bundle.md`
- `docs/config-db-externalization.md`
- `docs/troubleshooting.md`
- `docs/choose-your-path.md`
- `docs/current-supported-workflow.md`
- `docs/common-tasks.md`
- `docs/reports/2026/2026-0525-openapi-auth-persistence-plan.md`
- `docs/reports/2026/2026-0525-openapi-spec-visibility-control.md`
- `docs/reports/2026/2026-0526-openapi-public-route-policy.md`
- `docs/reports/2026/2026-0526-project-metadata-bundle-database-sources-secrets.md`
- `docs/reports/2026/2026-0526-config-db-externalization-preflight.md`
- `docs/reports/2026/2026-0526-config-db-externalization-metadata-routing.md`
- `docs/reports/2026/2026-0526-config-db-externalization-compose-dependency.md`
- `docs/reports/2026/2026-0526-config-db-externalization-local-compose-overlay.md`
- `docs/reports/2026/2026-0526-config-db-externalization-manual-lane-wording.md`
- `docs/reports/2026/2026-0526-compose-stack-helper-commonization.md`
- `docs/reports/2026/2026-0526-external-config-db-convenience-targets.md`
- `docs/reports/2026/2026-0526-external-config-db-parity-scope-decision.md`
- `docs/reports/2026/2026-0526-show-compose-access-urls-base-lane-decision.md`
- `docs/reports/2026/2026-0526-goal-based-doc-entry-guide.md`
- `docs/reports/2026/2026-0526-permanent-doc-promotion-bundle-and-config-db.md`
- `docs/reports/2026/2026-0526-troubleshooting-doc-guide.md`
- `docs/reports/2026/2026-0526-progress-snapshot.md`

最初に確認するコマンド:
- `php mtool/scripts/show_runtime_reference_status.php --require-current`
- `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only`
- `make help | sed -n '1,80p'`
- `make config-db-preflight`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ConfigDbExternalizationContractTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/LabDbIngressContractTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SamplePackCatalogTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
- `make sample-pack-compose-smoke`
- `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`

停止点の意味:
- broad rewrite の current execution は `2026-0527-broad-rewrite-temporary-closure-plan.md` に集約し、他の broad rewrite 親計画は supporting/reference 扱いへ正規化した
- current active milestone は `M1. runtime contract truth normalization` で、`M2` / `M3` / `Close` は後続である
- `Close` では一般/permanent docs の日英併記化を行い、progress/handoff docs は日本語のみ運用を維持する
- export/import/secrets/config DB externalization の current first/second slice は一通り実装と検証が終わった
- OpenAPI public alias/raw policy は current では defer と判断した
- config DB externalization は app/preflight/migrate/metadata routing、root compose web service の `db-config` 起動順依存 removal、external startup lane 追加に加え、base compose から `db-config` service を切り出した local overlay まで終わった
- sample/helper 系の compose stack merge も helper script に寄せて current 化した
- external config DB lane の minimal follow-up target も追加済み
- external config DB lane の `start/stop/reset/shell` parity は current では非採用と判断した
- `show_compose_access_urls.sh` は current では base-only default 維持と判断した
- current docs/help/tutorial sample README の wording も local overlay / external lane に揃えた
- goal-based doc entry guide (`docs/choose-your-path.md`) の追加と root/doc index の導線更新も終わった
- `project metadata bundle` / `config DB externalization` の stable rule も恒久文書へ昇格済み
- `troubleshooting` も恒久文書へ昇格済みで、warning / error は report より先にこちらを見る
- compose lane の current cleanup 判断は一通り済んだ
```
