COMPOSE ?= docker compose
PHP ?= php
LAB_DB_UI_PROFILE ?= lab-db-ui
COMPOSE_BASE := $(COMPOSE) -f compose.yaml
COMPOSE_LOCAL := $(COMPOSE_BASE) -f compose.local-db-config.yaml
COMPOSE_MTOOL := $(COMPOSE_LOCAL) -f mtool/docker/compose/01_mtool.compose.yaml
COMPOSE_MTOOL_LITE := $(COMPOSE_BASE) -f mtool/docker/compose/01_mtool-lite.compose.yaml
COMPOSE_USER_DB_PGSQL := $(COMPOSE) -f compose.user-db-pgsql.yaml
DURABLE_ENV_FILE ?= .env.durable
COMPOSE_DURABLE := $(COMPOSE) --env-file $(DURABLE_ENV_FILE) -f compose.yaml
CONFIG_DB_BACKUP_DIR ?= work/backups/config-db
CONFIG_DB_BACKUP_KEEP_DAYS ?= 7
CONFIG_DB_BACKUP_KEEP_COUNT ?= 7
GENERATED_NAME_MIGRATION_RUN_ID ?= current
GENERATED_NAME_MIGRATION_KEYWORD_MAP ?=
USER_DB_PGSQL_HOST_PORT ?= 15432
USER_DB_PGSQL_CONTAINER_HOST ?= host.docker.internal
USER_DB_PGSQL_DB ?= lab_app
USER_DB_PGSQL_USER ?= lab_app
USER_DB_PGSQL_PASSWORD ?= lab_app_password

.DEFAULT_GOAL := help

REFERENCE_ROOT := mtool/reference
REFERENCE_DBCLASSES_DIR := $(REFERENCE_ROOT)/dbclasses
WORK_ROOT := work
SAMPLE1_PACK_DIR := sample/tutorials/sample01-simple-table-runtime
SAMPLE1_COMPOSE_FILE := $(SAMPLE1_PACK_DIR)/compose.yaml
SAMPLE1_SQLITE_COMPOSE_FILE := $(SAMPLE1_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE1_RUN := ./$(SAMPLE1_PACK_DIR)/run.sh
SAMPLE1_SQLITE_RUN := ./$(SAMPLE1_PACK_DIR)/run-sqlite-config.sh
SAMPLE02_PACK_DIR := sample/tutorials/sample02-dataclass-nullable-default-status
SAMPLE02_COMPOSE_FILE := $(SAMPLE02_PACK_DIR)/compose.yaml
SAMPLE02_RUN := ./$(SAMPLE02_PACK_DIR)/run.sh
SAMPLE02_SQLITE_COMPOSE_FILE := $(SAMPLE02_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE02_SQLITE_RUN := ./$(SAMPLE02_PACK_DIR)/run-sqlite-config.sh
SAMPLE03_PACK_DIR := sample/tutorials/sample03-dataclass-lookup-and-helper
SAMPLE03_COMPOSE_FILE := $(SAMPLE03_PACK_DIR)/compose.yaml
SAMPLE03_RUN := ./$(SAMPLE03_PACK_DIR)/run.sh
SAMPLE03_SQLITE_COMPOSE_FILE := $(SAMPLE03_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE03_SQLITE_RUN := ./$(SAMPLE03_PACK_DIR)/run-sqlite-config.sh
SAMPLE04_PACK_DIR := sample/tutorials/sample04-dataclass-parent-child-basic
SAMPLE04_COMPOSE_FILE := $(SAMPLE04_PACK_DIR)/compose.yaml
SAMPLE04_RUN := ./$(SAMPLE04_PACK_DIR)/run.sh
SAMPLE04_SQLITE_COMPOSE_FILE := $(SAMPLE04_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE04_SQLITE_RUN := ./$(SAMPLE04_PACK_DIR)/run-sqlite-config.sh
SAMPLE05_PACK_DIR := sample/tutorials/sample05-dbaccess-select-basic
SAMPLE05_COMPOSE_FILE := $(SAMPLE05_PACK_DIR)/compose.yaml
SAMPLE05_RUN := ./$(SAMPLE05_PACK_DIR)/run.sh
SAMPLE05_SQLITE_COMPOSE_FILE := $(SAMPLE05_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE05_SQLITE_RUN := ./$(SAMPLE05_PACK_DIR)/run-sqlite-config.sh
SAMPLE06_PACK_DIR := sample/tutorials/sample06-dbaccess-filter-sort-page
SAMPLE06_COMPOSE_FILE := $(SAMPLE06_PACK_DIR)/compose.yaml
SAMPLE06_RUN := ./$(SAMPLE06_PACK_DIR)/run.sh
SAMPLE06_SQLITE_COMPOSE_FILE := $(SAMPLE06_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE06_SQLITE_RUN := ./$(SAMPLE06_PACK_DIR)/run-sqlite-config.sh
SAMPLE07_PACK_DIR := sample/tutorials/sample07-dbaccess-crud-basic
SAMPLE07_COMPOSE_FILE := $(SAMPLE07_PACK_DIR)/compose.yaml
SAMPLE07_RUN := ./$(SAMPLE07_PACK_DIR)/run.sh
SAMPLE07_SQLITE_COMPOSE_FILE := $(SAMPLE07_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE07_SQLITE_RUN := ./$(SAMPLE07_PACK_DIR)/run-sqlite-config.sh
SAMPLE08_PACK_DIR := sample/tutorials/sample08-dbaccess-join-read-model
SAMPLE08_COMPOSE_FILE := $(SAMPLE08_PACK_DIR)/compose.yaml
SAMPLE08_RUN := ./$(SAMPLE08_PACK_DIR)/run.sh
SAMPLE08_SQLITE_COMPOSE_FILE := $(SAMPLE08_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE08_SQLITE_RUN := ./$(SAMPLE08_PACK_DIR)/run-sqlite-config.sh
SAMPLE09_PACK_DIR := sample/tutorials/sample09-dbaccess-aggregate-report
SAMPLE09_COMPOSE_FILE := $(SAMPLE09_PACK_DIR)/compose.yaml
SAMPLE09_RUN := ./$(SAMPLE09_PACK_DIR)/run.sh
SAMPLE09_SQLITE_COMPOSE_FILE := $(SAMPLE09_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE09_SQLITE_RUN := ./$(SAMPLE09_PACK_DIR)/run-sqlite-config.sh
SAMPLE10_PACK_DIR := sample/tutorials/sample10-dbaccess-mini-crud-flow
SAMPLE10_COMPOSE_FILE := $(SAMPLE10_PACK_DIR)/compose.yaml
SAMPLE10_RUN := ./$(SAMPLE10_PACK_DIR)/run.sh
SAMPLE10_SQLITE_COMPOSE_FILE := $(SAMPLE10_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE10_SQLITE_RUN := ./$(SAMPLE10_PACK_DIR)/run-sqlite-config.sh
SAMPLE11_PACK_DIR := sample/tutorials/sample11-html-template-output
SAMPLE11_COMPOSE_FILE := $(SAMPLE11_PACK_DIR)/compose.yaml
SAMPLE11_RUN := ./$(SAMPLE11_PACK_DIR)/run.sh
SAMPLE11_SQLITE_COMPOSE_FILE := $(SAMPLE11_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE11_SQLITE_RUN := ./$(SAMPLE11_PACK_DIR)/run-sqlite-config.sh
SAMPLE12_PACK_DIR := sample/tutorials/sample12-external-db-source-import
SAMPLE12_COMPOSE_FILE := $(SAMPLE12_PACK_DIR)/compose.yaml
SAMPLE12_RUN := ./$(SAMPLE12_PACK_DIR)/run.sh
SAMPLE12_SQLITE_COMPOSE_FILE := $(SAMPLE12_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE12_SQLITE_RUN := ./$(SAMPLE12_PACK_DIR)/run-sqlite-config.sh
SAMPLE13_PACK_DIR := sample/tutorials/sample13-openapi-api-surface
SAMPLE13_COMPOSE_FILE := $(SAMPLE13_PACK_DIR)/compose.yaml
SAMPLE13_RUN := ./$(SAMPLE13_PACK_DIR)/run.sh
SAMPLE13_SQLITE_COMPOSE_FILE := $(SAMPLE13_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE13_SQLITE_RUN := ./$(SAMPLE13_PACK_DIR)/run-sqlite-config.sh
SAMPLE14_PACK_DIR := sample/tutorials/sample14-custom-proxy-runtime
SAMPLE14_COMPOSE_FILE := $(SAMPLE14_PACK_DIR)/compose.yaml
SAMPLE14_RUN := ./$(SAMPLE14_PACK_DIR)/run.sh
SAMPLE14_SQLITE_COMPOSE_FILE := $(SAMPLE14_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE14_SQLITE_RUN := ./$(SAMPLE14_PACK_DIR)/run-sqlite-config.sh
SAMPLE15_PACK_DIR := sample/tutorials/sample15-project-metadata-export-import
SAMPLE15_COMPOSE_FILE := $(SAMPLE15_PACK_DIR)/compose.yaml
SAMPLE15_RUN := ./$(SAMPLE15_PACK_DIR)/run.sh
SAMPLE15_SQLITE_COMPOSE_FILE := $(SAMPLE15_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE15_SQLITE_RUN := ./$(SAMPLE15_PACK_DIR)/run-sqlite-config.sh
SAMPLE16_PACK_DIR := sample/tutorials/sample16-authenticated-proxy
SAMPLE16_COMPOSE_FILE := $(SAMPLE16_PACK_DIR)/compose.yaml
SAMPLE16_RUN := ./$(SAMPLE16_PACK_DIR)/run.sh
SAMPLE16_SQLITE_COMPOSE_FILE := $(SAMPLE16_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE16_SQLITE_RUN := ./$(SAMPLE16_PACK_DIR)/run-sqlite-config.sh
SAMPLE17_PACK_DIR := sample/tutorials/sample17-multi-output-project
SAMPLE17_COMPOSE_FILE := $(SAMPLE17_PACK_DIR)/compose.yaml
SAMPLE17_RUN := ./$(SAMPLE17_PACK_DIR)/run.sh
SAMPLE17_SQLITE_COMPOSE_FILE := $(SAMPLE17_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE17_SQLITE_RUN := ./$(SAMPLE17_PACK_DIR)/run-sqlite-config.sh
SAMPLE18_PACK_DIR := sample/tutorials/sample18-mini-task-board-demo
SAMPLE18_COMPOSE_FILE := $(SAMPLE18_PACK_DIR)/compose.yaml
SAMPLE18_RUN := ./$(SAMPLE18_PACK_DIR)/run.sh
SAMPLE18_SQLITE_COMPOSE_FILE := $(SAMPLE18_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE18_SQLITE_RUN := ./$(SAMPLE18_PACK_DIR)/run-sqlite-config.sh
SAMPLE19_PACK_DIR := sample/tutorials/sample19-json-first-content-model-demo
SAMPLE19_COMPOSE_FILE := $(SAMPLE19_PACK_DIR)/compose.yaml
SAMPLE19_RUN := ./$(SAMPLE19_PACK_DIR)/run.sh
SAMPLE19_SQLITE_COMPOSE_FILE := $(SAMPLE19_PACK_DIR)/compose.sqlite-config.yaml
SAMPLE19_SQLITE_RUN := ./$(SAMPLE19_PACK_DIR)/run-sqlite-config.sh
SAMPLE20_PACK_DIR := sample/tutorials/sample20-content-publishing-demo
SAMPLE20_COMPOSE_FILE := $(SAMPLE20_PACK_DIR)/compose.yaml
SAMPLE20_RUN := ./$(SAMPLE20_PACK_DIR)/run.sh
SAMPLE21_PACK_DIR := sample/tutorials/sample21-ebook-catalog-api-demo
SAMPLE21_COMPOSE_FILE := $(SAMPLE21_PACK_DIR)/compose.yaml
SAMPLE21_RUN := ./$(SAMPLE21_PACK_DIR)/run.sh
SAMPLE22_PACK_DIR := sample/tutorials/sample22-ebook-chapter-workflow-demo
SAMPLE22_COMPOSE_FILE := $(SAMPLE22_PACK_DIR)/compose.yaml
SAMPLE22_RUN := ./$(SAMPLE22_PACK_DIR)/run.sh
SAMPLE23_PACK_DIR := sample/tutorials/sample23-ebook-media-metadata-demo
SAMPLE23_COMPOSE_FILE := $(SAMPLE23_PACK_DIR)/compose.yaml
SAMPLE23_RUN := ./$(SAMPLE23_PACK_DIR)/run.sh
SAMPLE24_PACK_DIR := sample/tutorials/sample24-ebook-public-reader-site-demo
SAMPLE24_COMPOSE_FILE := $(SAMPLE24_PACK_DIR)/compose.yaml
SAMPLE24_RUN := ./$(SAMPLE24_PACK_DIR)/run.sh
SAMPLE25_PACK_DIR := sample/tutorials/sample25-ebook-editor-auth-cms-demo
SAMPLE25_COMPOSE_FILE := $(SAMPLE25_PACK_DIR)/compose.yaml
SAMPLE25_RUN := ./$(SAMPLE25_PACK_DIR)/run.sh
SAMPLE26_PACK_DIR := sample/tutorials/sample26-ebook-headless-cms-capstone
SAMPLE26_COMPOSE_FILE := $(SAMPLE26_PACK_DIR)/compose.yaml
SAMPLE26_RUN := ./$(SAMPLE26_PACK_DIR)/run.sh
SAMPLE27_PACK_DIR := sample/tutorials/sample27-app-local-persistence-demo
SAMPLE27_COMPOSE_FILE := $(SAMPLE27_PACK_DIR)/compose.yaml
SAMPLE27_RUN := ./$(SAMPLE27_PACK_DIR)/run.sh

LEGACY_GENERATED_CLEAN_DIRS := \
	generated \
	work/legacy-generated
ROOT_TMP_DIR := tmp

.PHONY: help env env-force bootstrap-dbclasses bootstrap-dbclasses-runtime-reference promote-runtime-reference restore-runtime-reference-snapshot mtool-runtime-reference-status clean project-output db-access-sync mtool-canonical-sync mtool-self-loop-check mtool-proxy-output-check mtool-html-db-lang-res-wrapper-check mtool-lang-res-file-tree-export mtool-lang-res-file-tree-check mtool-external-source-lab-smoke mtool-external-source-lab-browser-smoke mtool-lite-smoke test sample-pack-compose-smoke sample-pack-runtime-smoke sample01-pack-runtime-test sample02-pack-runtime-test sample03-pack-runtime-test sample04-pack-runtime-test sample05-pack-runtime-test sample06-pack-runtime-test sample07-pack-runtime-test sample08-pack-runtime-test sample09-pack-runtime-test sample09-runtime-output-test sample10-pack-runtime-test sample10-runtime-output-test sample11-pack-runtime-test sample11-runtime-output-test sample12-pack-runtime-test sample12-runtime-output-test sample13-pack-runtime-test sample13-runtime-output-test sample14-pack-runtime-test sample14-runtime-output-test sample15-pack-runtime-test sample15-runtime-output-test sample16-pack-runtime-test sample16-runtime-output-test sample17-pack-runtime-test sample17-runtime-output-test sample18-pack-runtime-test sample18-runtime-output-test sample19-pack-runtime-test sample19-runtime-output-test sample20-pack-runtime-test sample20-runtime-output-test sample21-pack-runtime-test sample21-runtime-output-test sample22-pack-runtime-test sample22-runtime-output-test sample23-pack-runtime-test sample23-runtime-output-test sample24-pack-runtime-test sample24-runtime-output-test sample25-pack-runtime-test sample25-runtime-output-test sample26-pack-runtime-test sample26-runtime-output-test sample27-pack-runtime-test sample27-runtime-output-test pattern01-output-test pattern02-output-test pattern03-output-test pattern04-output-test pattern05-output-test pattern06-output-test pattern07-output-test pattern08-output-test pattern09-output-test pattern10-output-test pattern11-output-test pattern12-output-test pattern13-output-test pattern14-output-test sample01-pack-output-test sample02-pack-output-test sample03-pack-output-test sample04-pack-output-test sample05-pack-output-test sample06-pack-output-test sample07-pack-output-test sample08-pack-output-test sample09-pack-output-test sample10-pack-output-test sample11-pack-output-test sample12-pack-output-test sample13-pack-output-test sample14-pack-output-test sample15-pack-output-test sample1-output-test sample1-output-check sample2-output-test sample2-output-check sample3-output-test sample3-output-check sample4-output-test sample4-output-check sample5-output-test sample5-output-check sample6-output-test sample6-output-check sample7-output-test sample7-output-check sample8-output-test sample8-output-check sample9-output-test sample9-output-check sample10-output-test sample10-output-check sample11-output-test sample11-output-check sample12-output-test sample12-output-check sample13-output-test sample13-output-check sample14-output-test sample14-output-check sample15-output-test sample15-output-check sample16-output-test sample16-output-check sample17-output-test sample17-output-check sample18-output-test sample18-output-check sample19-output-test sample19-output-check sample20-output-test sample20-output-check sample21-output-test sample21-output-check sample22-output-test sample22-output-check build up up-mtool start-mtool stop-mtool down-mtool reset-mtool ps-mtool logs-mtool health-mtool config-db-preflight-mtool db-config-migrate-mtool up-mtool-lite start-mtool-lite stop-mtool-lite down-mtool-lite reset-mtool-lite ps-mtool-lite logs-mtool-lite health-mtool-lite config-db-preflight-mtool-lite db-config-migrate-mtool-lite up-external-config-db down-external-config-db ps-external-config-db logs-external-config-db health-external-config-db config-db-preflight-external-config-db db-config-migrate-external-config-db start stop down reset ps logs health admin-shell lab-shell db-config-shell db-lab-shell config-db-preflight db-config-migrate db-lab-migrate
.PHONY: backup-config-db backup-config-db-rotate restore-config-db backup-config-db-mtool backup-config-db-mtool-rotate restore-config-db-mtool backup-config-db-sqlite backup-config-db-sqlite-rotate restore-config-db-sqlite backup-config-db-mtool-lite backup-config-db-mtool-lite-rotate restore-config-db-mtool-lite up-durable-config-db ps-durable-config-db logs-durable-config-db health-durable-config-db config-db-preflight-durable-config-db db-config-migrate-durable-config-db down-durable-config-db
.PHONY: sample01-pack-runtime-test-sqlite sample1-output-test-sqlite sample1-output-check-sqlite sample02-pack-runtime-test-sqlite sample2-output-test-sqlite sample2-output-check-sqlite sample03-pack-runtime-test-sqlite sample3-output-test-sqlite sample3-output-check-sqlite sample04-pack-runtime-test-sqlite sample4-output-test-sqlite sample4-output-check-sqlite sample05-pack-runtime-test-sqlite sample5-output-test-sqlite sample5-output-check-sqlite sample06-pack-runtime-test-sqlite sample6-output-test-sqlite sample6-output-check-sqlite sample07-pack-runtime-test-sqlite sample7-output-test-sqlite sample7-output-check-sqlite sample08-pack-runtime-test-sqlite sample8-output-test-sqlite sample8-output-check-sqlite sample09-pack-runtime-test-sqlite sample09-runtime-output-test-sqlite sample10-pack-runtime-test-sqlite sample10-runtime-output-test-sqlite sample11-pack-runtime-test-sqlite sample11-runtime-output-test-sqlite sample12-pack-runtime-test-sqlite sample12-runtime-output-test-sqlite sample13-pack-runtime-test-sqlite sample13-runtime-output-test-sqlite sample13-http-runtime-smoke sample13-http-runtime-smoke-sqlite sample13-browser-try-it-out-smoke sample13-browser-try-it-out-smoke-sqlite sample14-pack-runtime-test-sqlite sample14-runtime-output-test-sqlite sample15-pack-runtime-test-sqlite sample15-runtime-output-test-sqlite sample16-pack-runtime-test-sqlite sample16-runtime-output-test-sqlite sample16-http-runtime-smoke sample16-http-runtime-smoke-sqlite sample17-pack-runtime-test-sqlite sample17-runtime-output-test-sqlite sample18-pack-runtime-test-sqlite sample18-runtime-output-test-sqlite sample18-http-runtime-smoke sample19-pack-runtime-test-sqlite sample19-runtime-output-test-sqlite sample25-browser-try-it-out-smoke
.PHONY: artifact-parity-capture-mysql artifact-parity-capture-sqlite artifact-parity-compare artifact-parity-test
.PHONY: up-user-db-pgsql down-user-db-pgsql reset-user-db-pgsql ps-user-db-pgsql logs-user-db-pgsql health-user-db-pgsql user-db-contract-capture-mysql user-db-contract-capture-sqlite user-db-contract-capture-pgsql user-db-contract-compare user-db-contract-compare-pgsql user-db-contract-test user-db-contract-test-pgsql postgresql-user-db-test-local
.PHONY: generated-name-migration-capture-samples-before generated-name-migration-capture-samples-after generated-name-migration-validate-sample-keyword-map generated-name-migration-transform-samples-after generated-name-migration-compare-samples generated-name-migration-derive-keyword-map generated-name-migration-derive-sample-keyword-map generated-name-migration-scan-sample-keywords
.PHONY: mtool-oidc-login-smoke

DOCKER_ENV_TARGETS := \
	build \
	up \
	up-mtool \
	start-mtool \
	health-mtool \
	config-db-preflight-mtool \
	db-config-migrate-mtool \
	up-mtool-lite \
	start-mtool-lite \
	health-mtool-lite \
	config-db-preflight-mtool-lite \
	db-config-migrate-mtool-lite \
	backup-config-db-mtool \
	backup-config-db-mtool-rotate \
	restore-config-db-mtool \
	backup-config-db-mtool-lite \
	backup-config-db-mtool-lite-rotate \
	restore-config-db-mtool-lite \
	up-durable-config-db \
	health-durable-config-db \
	config-db-preflight-durable-config-db \
	db-config-migrate-durable-config-db \
	up-external-config-db \
	health-external-config-db \
	config-db-preflight-external-config-db \
	db-config-migrate-external-config-db \
	start \
	health \
	admin-shell \
	lab-shell \
	db-config-shell \
	db-lab-shell \
	config-db-preflight \
	db-config-migrate \
	backup-config-db \
	backup-config-db-rotate \
	restore-config-db \
	db-lab-migrate \
	db-access-sync \
	mtool-canonical-sync \
	mtool-self-loop-check \
	mtool-proxy-output-check \
	mtool-html-db-lang-res-wrapper-check \
	sample-pack-compose-smoke \
	sample-pack-runtime-smoke \
	test \
	sample1-output-test \
	sample2-output-test \
	sample3-output-test \
	sample4-output-test \
	sample5-output-test \
	sample6-output-test \
	sample7-output-test \
	sample8-output-test \
	sample09-runtime-output-test \
	sample10-runtime-output-test \
	sample11-runtime-output-test \
	sample12-runtime-output-test \
	sample13-runtime-output-test \
	sample14-runtime-output-test \
	sample15-runtime-output-test \
	sample16-runtime-output-test \
	sample17-runtime-output-test \
	sample18-runtime-output-test \
	sample19-runtime-output-test \
	sample20-runtime-output-test \
	sample21-runtime-output-test \
	sample22-runtime-output-test \
	sample23-runtime-output-test \
	sample24-runtime-output-test \
	sample25-runtime-output-test \
	sample26-runtime-output-test \
	sample27-runtime-output-test \
	sample9-output-test \
	sample10-output-test \
	sample11-output-test \
	sample12-output-test \
	sample13-output-test \
	sample14-output-test \
	sample15-output-test \
	sample16-output-test \
	sample17-output-test \
	sample18-output-test \
	sample19-output-test \
	sample20-output-test \
	sample21-output-test \
	sample22-output-test

$(DOCKER_ENV_TARGETS): env

help: ## 利用可能な target を表示する
	@awk 'BEGIN {FS = ":.*## "; print "Usage:"; print "  make <target>"; print ""; print "Targets:"} /^[a-zA-Z0-9_.-]+:.*## / {printf "  %-16s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

env: ## .env が無ければ local credential をランダム生成して作成する
	@test -f .env || $(PHP) mtool/scripts/generate_local_env.php --output=.env --template=.env.example
	@echo ".env is ready"

env-force: ## .env を再生成する。DB password rotate 時は fresh init 前提
	$(PHP) mtool/scripts/generate_local_env.php --force --output=.env --template=.env.example

bootstrap-dbclasses: ## archived helper: staged legacy copy は current supported workflow から外した
	@echo "bootstrap-dbclasses has been archived and is not part of the current supported workflow." >&2
	@echo "Current supported runtime reference recovery is make restore-runtime-reference-snapshot ARTIFACT_KEY=..." >&2
	@echo "If a host-side quarantined full-tree legacy copy is ever needed again, explicitly restore the archived helper from mtool/archive/." >&2
	@exit 1

bootstrap-dbclasses-runtime-reference: ## archived alias: runtime reference restore は snapshot-backed recovery を使う
	@echo "bootstrap-dbclasses-runtime-reference is archived." >&2
	@echo "Use make restore-runtime-reference-snapshot ARTIFACT_KEY=... to recover mtool/reference/dbclasses." >&2
	@echo "The archived helper must be restored explicitly from mtool/archive/ and is not part of the current supported workflow." >&2
	@exit 1

promote-runtime-reference: ## latest runtime artifact を mtool/reference/dbclasses へ promote する。ARTIFACT_KEY=... で固定可能
	$(PHP) mtool/scripts/promote_runtime_reference.php --requested-by=make $(if $(ARTIFACT_KEY),--artifact-key=$(ARTIFACT_KEY),)

restore-runtime-reference-snapshot: ## durable snapshot から runtime reference を restore する。ARTIFACT_KEY=... を必須指定する
	@test -n "$(ARTIFACT_KEY)" || (echo "ARTIFACT_KEY is required, e.g. make restore-runtime-reference-snapshot ARTIFACT_KEY=20260520-022959-3e593819" >&2; exit 1)
	$(PHP) mtool/scripts/restore_runtime_reference_snapshot.php --requested-by=make --artifact-key=$(ARTIFACT_KEY)

mtool-runtime-reference-status: ## promoted runtime reference と latest runtime artifact の同期状態を表示する
	$(PHP) mtool/scripts/show_runtime_reference_status.php $(if $(REQUIRE_CURRENT),--require-current,)

clean: ## disposable runtime work と旧 generated 残骸を完全削除する
	@echo "Removing disposable runtime work..."
	@rm -rf $(WORK_ROOT) $(LEGACY_GENERATED_CLEAN_DIRS) $(ROOT_TMP_DIR)
	@echo "Kept: $(REFERENCE_ROOT)/, mtool/extensions/, sample/"

project-output: ## runtime reference / staging から source output archive を作る。PROJECT_KEY=MTOOL を指定する
	@test -n "$(PROJECT_KEY)" || (echo "PROJECT_KEY is required, e.g. make project-output PROJECT_KEY=MTOOL" >&2; exit 1)
	$(PHP) mtool/scripts/create_project_output.php --project-key=$(PROJECT_KEY) --requested-by=make

db-access-sync: ## bootstrap dbaccess preview を canonical metadata へ sync する。PROJECT_KEY=MTOOL を指定する
	@test -n "$(PROJECT_KEY)" || (echo "PROJECT_KEY is required, e.g. make db-access-sync PROJECT_KEY=MTOOL" >&2; exit 1)
	$(COMPOSE_LOCAL) exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php --project-key=$(PROJECT_KEY)

mtool-canonical-sync: ## MTOOL core seed stack で import / data class sync / DB Access sync を一括実行する
	$(COMPOSE_MTOOL) exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=MTOOL
	$(COMPOSE_MTOOL) exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=MTOOL
	$(COMPOSE_MTOOL) exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php --project-key=MTOOL

mtool-self-loop-check: ## MTOOL の import/sync/generate self-loop を一括検証する
	$(COMPOSE_MTOOL) exec -T web-admin php /var/www/mtool/scripts/check_mtool_self_loop.php --requested-by=make

mtool-proxy-output-check: ## MTOOL の proxy source output build/generate expected output を検証する
	$(COMPOSE_LOCAL) exec -T web-admin php /var/www/mtool/scripts/check_mtool_proxy_outputs.php --requested-by=make

mtool-html-db-lang-res-wrapper-check: ## MTOOL HTML-DB generated language resource wrapper smoke を最新 publish 済み生成物で検証する
	$(COMPOSE_LOCAL) exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=make --publish
	$(COMPOSE_LOCAL) exec -T web-admin php /var/www/mtool/scripts/check_generated_html_db_language_resource_wrappers.php --docroot=/var/www/work/source-outputs/MTOOL/HTML-DB

mtool-lang-res-file-tree-export: ## LanguageResource file tree を既知 project 全件ぶん再生成する
	$(PHP) mtool/scripts/export_language_resource_file_tree.php --all --clean

mtool-lang-res-file-tree-check: ## LanguageResource file tree を一括 validate する
	$(PHP) mtool/scripts/validate_language_resource_file_tree.php --all

mtool-external-source-lab-smoke: env ## admin UI の external source 作成から import/sync/output/lab proxy+swagger までを localhost で一括 smoke する
	$(PHP) mtool/scripts/check_external_database_source_lab_swagger_flow.php

mtool-external-source-lab-browser-smoke: env ## external source prepare 後に Lab Swagger Try It Out を headless Chrome で実行し、lab_experiments は CRUD cycle まで確認する
	node mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js

mtool-oidc-login-smoke: ## mock OIDC provider で login/callback/session principal を HTTP smoke する
	$(PHP) mtool/scripts/check_oidc_login_smoke.php --pretty

test: ## 現在の自動テスト一式を実行する
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--apply-pack-seed \
		--extra-seed=$(SAMPLE02_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE03_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE04_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE05_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE06_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE07_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE08_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE09_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE10_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE11_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE12_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE13_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE14_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE15_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE16_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE17_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE18_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE19_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE20_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE21_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE22_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE23_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE24_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE25_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE26_PACK_DIR)/seed \
		--extra-seed=$(SAMPLE27_PACK_DIR)/seed \
		--phpunit-target=/var/www/tests/Integration

sample-pack-compose-smoke: ## active runtime sample pack の compose override merge を軽く検証する
	bash mtool/scripts/check_sample_pack_compose_smoke.sh

sample-pack-runtime-smoke: ## representative runtime sample pack を up/apply-seed/health まで軽く検証する
	bash mtool/scripts/check_sample_pack_runtime_smoke.sh

generated-name-migration-capture-samples-before: ## sample/tutorials/*/reference を命名移行 before snapshot として一括 capture/index する
	python3 -B mtool/scripts/generated_name_migration_audit.py capture-samples \
		--samples-root=sample/tutorials \
		--output-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		--phase=before \
		--manifest-output=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples-before.json \
		--index \
		--pretty

generated-name-migration-capture-samples-after: ## sample/tutorials/*/reference を命名移行 after snapshot として一括 capture/index する
	python3 -B mtool/scripts/generated_name_migration_audit.py capture-samples \
		--samples-root=sample/tutorials \
		--output-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		--phase=after \
		--manifest-output=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples-after.json \
		--index \
		--pretty

generated-name-migration-validate-sample-keyword-map: ## before snapshot に対して keyword map の衝突/連鎖を検査する
	@test -n "$(GENERATED_NAME_MIGRATION_KEYWORD_MAP)" || (echo "GENERATED_NAME_MIGRATION_KEYWORD_MAP is required" >&2; exit 1)
	python3 -B mtool/scripts/generated_name_migration_audit.py validate-keyword-map-samples \
		--samples-snapshot-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		--phase=before \
		--keyword-map=$(GENERATED_NAME_MIGRATION_KEYWORD_MAP) \
		--output=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/sample-keyword-map-validation.json \
		--pretty

generated-name-migration-transform-samples-after: ## before snapshot に keyword map を適用して after snapshot を生成/index する
	@test -n "$(GENERATED_NAME_MIGRATION_KEYWORD_MAP)" || (echo "GENERATED_NAME_MIGRATION_KEYWORD_MAP is required" >&2; exit 1)
	python3 -B mtool/scripts/generated_name_migration_audit.py transform-samples \
		--samples-snapshot-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		--output-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		--source-phase=before \
		--output-phase=after \
		--keyword-map=$(GENERATED_NAME_MIGRATION_KEYWORD_MAP) \
		--manifest-output=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples-after-transform.json \
		--index \
		--pretty

generated-name-migration-compare-samples: ## 命名移行の before/after sample index を一括 compare する
	python3 -B mtool/scripts/generated_name_migration_audit.py compare-samples \
		--before-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		--after-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		$(if $(GENERATED_NAME_MIGRATION_KEYWORD_MAP),--keyword-map=$(GENERATED_NAME_MIGRATION_KEYWORD_MAP),) \
		--output=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples-compare.json \
		--pretty

generated-name-migration-derive-keyword-map: ## before/after index から keyword map 候補を生成する（SAMPLE=sample-dir-name が必要）
	@test -n "$(SAMPLE)" || (echo "SAMPLE is required" >&2; exit 1)
	python3 -B mtool/scripts/generated_name_migration_audit.py derive-keyword-map \
		--before=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples/$(SAMPLE)/before-index.json \
		--after=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples/$(SAMPLE)/after-index.json \
		--output=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/$(SAMPLE)-keyword-map-candidates.json \
		--pretty

generated-name-migration-derive-sample-keyword-map: ## 全sampleの before/after index から keyword map 候補を集約する
	python3 -B mtool/scripts/generated_name_migration_audit.py derive-keyword-map-samples \
		--before-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		--after-root=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/samples \
		--output=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/sample-keyword-map-candidates.json \
		--pretty

generated-name-migration-scan-sample-keywords: ## keyword map の old 名が sample reference 内に残る場所を一覧化する
	@test -n "$(GENERATED_NAME_MIGRATION_KEYWORD_MAP)" || (echo "GENERATED_NAME_MIGRATION_KEYWORD_MAP is required" >&2; exit 1)
	python3 -B mtool/scripts/generated_name_migration_audit.py scan-keywords \
		--root=sample/tutorials \
		--keyword-map=$(GENERATED_NAME_MIGRATION_KEYWORD_MAP) \
		--output=work/generated-name-migration/$(GENERATED_NAME_MIGRATION_RUN_ID)/sample-keyword-scan.json \
		--pretty

sample01-pack-runtime-test: sample1-output-test ## sample01 tutorial runtime の import/sync/output integration test を実行する

sample02-pack-runtime-test: sample2-output-test ## sample02 tutorial runtime の import/sync/output integration test を実行する

sample03-pack-runtime-test: sample3-output-test ## sample03 tutorial runtime の import/sync/output integration test を実行する

sample04-pack-runtime-test: sample4-output-test ## sample04 tutorial runtime の import/sync/output integration test を実行する

sample05-pack-runtime-test: sample5-output-test ## sample05 tutorial runtime の import/sync/output integration test を実行する

sample06-pack-runtime-test: sample6-output-test ## sample06 tutorial runtime の import/sync/output integration test を実行する

sample07-pack-runtime-test: sample7-output-test ## sample07 tutorial runtime の import/sync/output integration test を実行する

sample08-pack-runtime-test: sample8-output-test ## sample08 tutorial runtime の import/sync/output integration test を実行する

sample09-pack-runtime-test: sample09-runtime-output-test ## sample09 tutorial runtime の import/sync/output integration test を実行する

sample10-pack-runtime-test: sample10-runtime-output-test ## sample10 tutorial runtime の import/sync/output integration test を実行する

sample11-pack-runtime-test: sample11-runtime-output-test ## sample11 tutorial runtime の HTML source output integration test を実行する

sample12-pack-runtime-test: sample12-runtime-output-test ## sample12 tutorial runtime の external DB source import integration test を実行する

sample13-pack-runtime-test: sample13-runtime-output-test ## sample13 tutorial runtime の OpenAPI API surface integration test を実行する

sample14-pack-runtime-test: sample14-runtime-output-test ## sample14 tutorial runtime の custom proxy runtime integration test を実行する

sample15-pack-runtime-test: sample15-runtime-output-test ## sample15 tutorial runtime の project metadata export/import integration test を実行する

sample16-pack-runtime-test: sample16-runtime-output-test ## sample16 tutorial runtime の authenticated proxy integration test を実行する

sample17-pack-runtime-test: sample17-runtime-output-test ## sample17 tutorial runtime の multi-output capstone integration test を実行する

sample18-pack-runtime-test: sample18-runtime-output-test ## sample18 tutorial runtime の instruction-driven demo integration test を実行する

sample19-pack-runtime-test: sample19-runtime-output-test ## sample19 tutorial runtime の JSON-first content model integration test を実行する

sample20-pack-runtime-test: sample20-runtime-output-test ## sample20 tutorial runtime の content publishing integration test を実行する

sample21-pack-runtime-test: sample21-runtime-output-test ## sample21 tutorial runtime の ebook catalog API integration test を実行する

sample22-pack-runtime-test: sample22-runtime-output-test ## sample22 tutorial runtime の ebook chapter workflow integration test を実行する

sample23-pack-runtime-test: sample23-runtime-output-test ## sample23 tutorial runtime の ebook media metadata integration test を実行する

sample24-pack-runtime-test: sample24-runtime-output-test ## sample24 tutorial runtime の ebook public reader site integration test を実行する

sample25-pack-runtime-test: sample25-runtime-output-test ## sample25 tutorial runtime の ebook editor auth CMS integration test を実行する

sample26-pack-runtime-test: sample26-runtime-output-test ## sample26 tutorial runtime の ebook headless CMS capstone integration test を実行する

sample27-pack-runtime-test: sample27-runtime-output-test ## sample27 tutorial runtime の App-local persistence integration test を実行する

pattern01-output-test: sample9-output-test ## pattern01 default-property-split の wrapper/base migration test を実行する

pattern02-output-test: sample12-output-test ## pattern02 wrapper-property-helper の wrapper/base migration test を実行する

pattern03-output-test: sample11-output-test ## pattern03 method-only-split の wrapper/base migration test を実行する

pattern04-output-test: sample13-output-test ## pattern04 method-and-enum-basic の wrapper/base migration test を実行する

pattern05-output-test: sample10-output-test ## pattern05 companion-declarations-basic の wrapper/base migration test を実行する

pattern06-output-test: sample15-output-test ## pattern06 companion-declarations-no-top-level の wrapper/base migration test を実行する

pattern07-output-test: sample16-output-test ## pattern07 companion-declarations-multiclass の wrapper/base migration test を実行する

pattern08-output-test: sample14-output-test ## pattern08 companion-declarations-multi-helper の wrapper/base migration test を実行する

pattern09-output-test: sample17-output-test ## pattern09 top-level-declaration-single の wrapper/base migration test を実行する

pattern10-output-test: sample18-output-test ## pattern10 top-level-declaration-multiclass の wrapper/base migration test を実行する

pattern11-output-test: sample19-output-test ## pattern11 top-level-declaration-html-template の wrapper/base migration test を実行する

pattern12-output-test: sample20-output-test ## pattern12 method-and-enum-no-top-level の wrapper/base migration test を実行する

pattern13-output-test: sample21-output-test ## pattern13 method-and-enum-multimethod の wrapper/base migration test を実行する

pattern14-output-test: sample22-output-test ## pattern14 method-and-enum-heavy-multimethod の wrapper/base migration test を実行する

sample01-pack-output-test: sample01-pack-runtime-test

sample02-pack-output-test: pattern01-output-test

sample03-pack-output-test: pattern02-output-test

sample04-pack-output-test: pattern03-output-test

sample05-pack-output-test: pattern04-output-test

sample06-pack-output-test: pattern05-output-test

sample07-pack-output-test: pattern06-output-test

sample08-pack-output-test: pattern07-output-test

sample09-pack-output-test: pattern08-output-test

sample10-pack-output-test: pattern09-output-test

sample11-pack-output-test: pattern10-output-test

sample12-pack-output-test: pattern11-output-test

sample13-pack-output-test: pattern12-output-test

sample14-pack-output-test: pattern13-output-test

sample15-pack-output-test: pattern14-output-test

sample1-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample1SimpleTableOutputTest.php

sample1-output-check: sample1-output-test

sample01-pack-runtime-test-sqlite: sample1-output-test-sqlite ## sample01 tutorial runtime を SQLite config store profile で実行する

sample1-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample01-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample1SimpleTableOutputTest.php

sample1-output-check-sqlite: sample1-output-test-sqlite

sample02-pack-runtime-test-sqlite: sample2-output-test-sqlite ## sample02 tutorial runtime を SQLite config store profile で実行する

sample2-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample02-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE02_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE02_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php

sample2-output-check-sqlite: sample2-output-test-sqlite

sample2-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE02_COMPOSE_FILE) \
		--run-script=$(SAMPLE02_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php

sample2-output-check: sample2-output-test

sample03-pack-runtime-test-sqlite: sample3-output-test-sqlite ## sample03 tutorial runtime を SQLite config store profile で実行する

sample3-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample03-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE03_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE03_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample3DataclassLookupAndHelperOutputTest.php

sample3-output-check-sqlite: sample3-output-test-sqlite

sample3-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE03_COMPOSE_FILE) \
		--run-script=$(SAMPLE03_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample3DataclassLookupAndHelperOutputTest.php

sample3-output-check: sample3-output-test

sample04-pack-runtime-test-sqlite: sample4-output-test-sqlite ## sample04 tutorial runtime を SQLite config store profile で実行する

sample4-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample04-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE04_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE04_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample4DataclassParentChildBasicOutputTest.php

sample4-output-check-sqlite: sample4-output-test-sqlite

sample4-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE04_COMPOSE_FILE) \
		--run-script=$(SAMPLE04_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample4DataclassParentChildBasicOutputTest.php

sample4-output-check: sample4-output-test

sample05-pack-runtime-test-sqlite: sample5-output-test-sqlite ## sample05 tutorial runtime を SQLite config store profile で実行する

sample5-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample05-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE05_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE05_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample5DbAccessSelectBasicOutputTest.php

sample5-output-check-sqlite: sample5-output-test-sqlite

sample5-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE05_COMPOSE_FILE) \
		--run-script=$(SAMPLE05_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample5DbAccessSelectBasicOutputTest.php

sample5-output-check: sample5-output-test

sample06-pack-runtime-test-sqlite: sample6-output-test-sqlite ## sample06 tutorial runtime を SQLite config store profile で実行する

sample6-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample06-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE06_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE06_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample6DbAccessFilterSortPageOutputTest.php

sample6-output-check-sqlite: sample6-output-test-sqlite

sample6-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE06_COMPOSE_FILE) \
		--run-script=$(SAMPLE06_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample6DbAccessFilterSortPageOutputTest.php

sample6-output-check: sample6-output-test

sample07-pack-runtime-test-sqlite: sample7-output-test-sqlite ## sample07 tutorial runtime を SQLite config store profile で実行する

sample7-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample07-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE07_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE07_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample7DbAccessCrudBasicOutputTest.php

sample7-output-check-sqlite: sample7-output-test-sqlite

sample7-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE07_COMPOSE_FILE) \
		--run-script=$(SAMPLE07_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample7DbAccessCrudBasicOutputTest.php

sample7-output-check: sample7-output-test

sample08-pack-runtime-test-sqlite: sample8-output-test-sqlite ## sample08 tutorial runtime を SQLite config store profile で実行する

sample8-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample08-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE08_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE08_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample8DbAccessJoinReadModelOutputTest.php

sample8-output-check-sqlite: sample8-output-test-sqlite

sample8-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE08_COMPOSE_FILE) \
		--run-script=$(SAMPLE08_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample8DbAccessJoinReadModelOutputTest.php

sample8-output-check: sample8-output-test

sample09-pack-runtime-test-sqlite: sample09-runtime-output-test-sqlite ## sample09 tutorial runtime を SQLite config store profile で実行する

sample09-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample09-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE09_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE09_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample09DbAccessAggregateReportOutputTest.php

sample09-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE09_COMPOSE_FILE) \
		--run-script=$(SAMPLE09_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample09DbAccessAggregateReportOutputTest.php

sample10-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE10_COMPOSE_FILE) \
		--run-script=$(SAMPLE10_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample10DbAccessMiniCrudFlowOutputTest.php

sample10-pack-runtime-test-sqlite: sample10-runtime-output-test-sqlite ## sample10 tutorial runtime を SQLite config store profile で実行する

sample10-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample10-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE10_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE10_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample10DbAccessMiniCrudFlowOutputTest.php

sample11-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE11_COMPOSE_FILE) \
		--run-script=$(SAMPLE11_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample11HtmlTemplateOutputTest.php

sample11-pack-runtime-test-sqlite: sample11-runtime-output-test-sqlite ## sample11 tutorial runtime を SQLite config store profile で実行する

sample11-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample11-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE11_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE11_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample11HtmlTemplateOutputTest.php

sample12-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE12_COMPOSE_FILE) \
		--run-script=$(SAMPLE12_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample12ExternalDbSourceImportOutputTest.php

sample12-pack-runtime-test-sqlite: sample12-runtime-output-test-sqlite ## sample12 tutorial runtime を SQLite config store profile で実行する

sample12-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample12-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE12_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE12_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample12ExternalDbSourceImportOutputTest.php

sample13-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE13_COMPOSE_FILE) \
		--run-script=$(SAMPLE13_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php

sample13-pack-runtime-test-sqlite: sample13-runtime-output-test-sqlite ## sample13 tutorial runtime を SQLite config store profile で実行する

sample13-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample13-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE13_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE13_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php

sample13-http-runtime-smoke: ## sample13 OpenAPI Swagger viewer を HTTP route 経由で検証する
	set -e; \
	trap '$(SAMPLE13_RUN) reset >/dev/null 2>&1 || true' EXIT; \
	KEEP_SAMPLE_STACK_RUNNING=1 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE13_COMPOSE_FILE) \
		--run-script=$(SAMPLE13_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php; \
	$(COMPOSE_LOCAL) -f $(SAMPLE13_COMPOSE_FILE) exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
		--project-key=SAMPLE13 \
		--source-output-key=API-PROXY-SERVER \
		--requested-by=sample13-http-smoke \
		--publish; \
	LAB_HTTP_PORT=18222 $(PHP) mtool/scripts/check_sample13_openapi_swagger_http_smoke.php \
		--lab-base-url=http://127.0.0.1:18222 \
		--lab-user="$${LAB_AUTH_STUB_USER:-lab-local}" \
		--lab-password="$${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}" \
		--db-source-key=config_db \
		--pretty

sample13-http-runtime-smoke-sqlite: ## sample13 OpenAPI Swagger viewer を SQLite config store profile の HTTP route 経由で検証する
	set -e; \
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample13-http-sqlite-$$(date +%Y%m%d%H%M%S)}"; \
	export APP_CONFIG_STORE_DIR; \
	trap 'APP_CONFIG_STORE_DIR="'"$$APP_CONFIG_STORE_DIR"'" $(SAMPLE13_SQLITE_RUN) reset >/dev/null 2>&1 || true' EXIT; \
	KEEP_SAMPLE_STACK_RUNNING=1 SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE13_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE13_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php; \
	APP_CONFIG_STORE_DIR="$$APP_CONFIG_STORE_DIR" $(COMPOSE_BASE) -f $(SAMPLE13_SQLITE_COMPOSE_FILE) exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
		--project-key=SAMPLE13 \
		--source-output-key=API-PROXY-SERVER \
		--requested-by=sample13-http-smoke-sqlite \
		--publish; \
	LAB_HTTP_PORT=18232 $(PHP) mtool/scripts/check_sample13_openapi_swagger_http_smoke.php \
		--lab-base-url=http://127.0.0.1:18232 \
		--lab-user="$${LAB_AUTH_STUB_USER:-lab-local}" \
		--lab-password="$${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}" \
		--db-source-key=config_db \
		--pretty

sample13-browser-try-it-out-smoke: ## sample13 Swagger viewer の Try It Out を headless Chrome で検証する
	set -e; \
	trap '$(SAMPLE13_RUN) reset >/dev/null 2>&1 || true' EXIT; \
	KEEP_SAMPLE_STACK_RUNNING=1 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE13_COMPOSE_FILE) \
		--run-script=$(SAMPLE13_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php; \
	$(COMPOSE_LOCAL) -f $(SAMPLE13_COMPOSE_FILE) exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
		--project-key=SAMPLE13 \
		--source-output-key=API-PROXY-SERVER \
		--requested-by=sample13-browser-smoke \
		--publish; \
	LAB_HTTP_PORT=18222 node mtool/scripts/check_sample13_openapi_swagger_try_it_out.js \
		--lab-port=18222 \
		--lab-user="$${LAB_AUTH_STUB_USER:-lab-local}" \
		--lab-password="$${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}" \
		--db-source-key=config_db

sample13-browser-try-it-out-smoke-sqlite: ## sample13 Swagger viewer の Try It Out を SQLite config store profile で検証する
	set -e; \
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample13-browser-sqlite-$$(date +%Y%m%d%H%M%S)}"; \
	export APP_CONFIG_STORE_DIR; \
	trap 'APP_CONFIG_STORE_DIR="'"$$APP_CONFIG_STORE_DIR"'" $(SAMPLE13_SQLITE_RUN) reset >/dev/null 2>&1 || true' EXIT; \
	KEEP_SAMPLE_STACK_RUNNING=1 SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE13_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE13_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php; \
	APP_CONFIG_STORE_DIR="$$APP_CONFIG_STORE_DIR" $(COMPOSE_BASE) -f $(SAMPLE13_SQLITE_COMPOSE_FILE) exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
		--project-key=SAMPLE13 \
		--source-output-key=API-PROXY-SERVER \
		--requested-by=sample13-browser-smoke-sqlite \
		--publish; \
	LAB_HTTP_PORT=18232 node mtool/scripts/check_sample13_openapi_swagger_try_it_out.js \
		--lab-port=18232 \
		--lab-user="$${LAB_AUTH_STUB_USER:-lab-local}" \
		--lab-password="$${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}" \
		--db-source-key=config_db

sample14-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE14_COMPOSE_FILE) \
		--run-script=$(SAMPLE14_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample14CustomProxyRuntimeOutputTest.php

sample14-pack-runtime-test-sqlite: sample14-runtime-output-test-sqlite ## sample14 tutorial runtime を SQLite config store profile で実行する

sample14-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample14-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE14_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE14_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample14CustomProxyRuntimeOutputTest.php

sample15-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE15_COMPOSE_FILE) \
		--run-script=$(SAMPLE15_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample15ProjectMetadataExportImportTest.php

sample15-pack-runtime-test-sqlite: sample15-runtime-output-test-sqlite ## sample15 tutorial runtime を SQLite config store profile で実行する

sample15-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample15-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE15_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE15_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample15ProjectMetadataExportImportTest.php

sample16-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE16_COMPOSE_FILE) \
		--run-script=$(SAMPLE16_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample16AuthenticatedProxyTest.php

sample16-pack-runtime-test-sqlite: sample16-runtime-output-test-sqlite ## sample16 tutorial runtime を SQLite config store profile で実行する

sample16-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample16-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE16_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE16_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample16AuthenticatedProxyTest.php

sample16-http-runtime-smoke: ## sample16 authenticated proxy を HTTP route 経由で検証する
	set -e; \
	trap '$(SAMPLE16_RUN) reset >/dev/null 2>&1 || true' EXIT; \
	KEEP_SAMPLE_STACK_RUNNING=1 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE16_COMPOSE_FILE) \
		--run-script=$(SAMPLE16_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample16AuthenticatedProxyTest.php; \
	LAB_HTTP_PORT=18252 $(PHP) mtool/scripts/check_sample16_authenticated_proxy_http_smoke.php \
		--lab-base-url=http://127.0.0.1:18252 \
		--lab-user="$${LAB_AUTH_STUB_USER:-lab-local}" \
		--lab-password="$${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}" \
		--proxy-token="$${MTOOL_PROXY_PROJECT_TOKEN:-sample16-token}" \
		--db-source-key=config_db \
		--pretty

sample16-http-runtime-smoke-sqlite: ## sample16 authenticated proxy を SQLite config store profile の HTTP route 経由で検証する
	set -e; \
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample16-http-sqlite-$$(date +%Y%m%d%H%M%S)}"; \
	export APP_CONFIG_STORE_DIR; \
	trap 'APP_CONFIG_STORE_DIR="'"$$APP_CONFIG_STORE_DIR"'" $(SAMPLE16_SQLITE_RUN) reset >/dev/null 2>&1 || true' EXIT; \
	KEEP_SAMPLE_STACK_RUNNING=1 SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE16_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE16_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample16AuthenticatedProxyTest.php; \
	LAB_HTTP_PORT=18262 $(PHP) mtool/scripts/check_sample16_authenticated_proxy_http_smoke.php \
		--lab-base-url=http://127.0.0.1:18262 \
		--lab-user="$${LAB_AUTH_STUB_USER:-lab-local}" \
		--lab-password="$${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}" \
		--proxy-token="$${MTOOL_PROXY_PROJECT_TOKEN:-sample16-token}" \
		--db-source-key=config_db \
		--pretty

sample17-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE17_COMPOSE_FILE) \
		--run-script=$(SAMPLE17_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample17MultiOutputProjectTest.php

sample17-pack-runtime-test-sqlite: sample17-runtime-output-test-sqlite ## sample17 tutorial runtime を SQLite config store profile で実行する

sample17-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample17-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE17_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE17_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample17MultiOutputProjectTest.php

sample18-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE18_COMPOSE_FILE) \
		--run-script=$(SAMPLE18_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample18MiniTaskBoardDemoTest.php

sample19-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE19_COMPOSE_FILE) \
		--run-script=$(SAMPLE19_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample19JsonFirstContentModelOutputTest.php

sample20-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE20_COMPOSE_FILE) \
		--run-script=$(SAMPLE20_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample20ContentPublishingDemoTest.php

sample21-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE21_COMPOSE_FILE) \
		--run-script=$(SAMPLE21_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample21EbookCatalogApiDemoTest.php

sample22-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE22_COMPOSE_FILE) \
		--run-script=$(SAMPLE22_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample22EbookChapterWorkflowDemoTest.php

sample23-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE23_COMPOSE_FILE) \
		--run-script=$(SAMPLE23_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample23EbookMediaMetadataDemoTest.php

sample24-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE24_COMPOSE_FILE) \
		--run-script=$(SAMPLE24_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample24EbookPublicReaderSiteDemoTest.php

sample25-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE25_COMPOSE_FILE) \
		--run-script=$(SAMPLE25_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample25EbookEditorAuthCmsDemoTest.php

sample25-browser-try-it-out-smoke: ## sample25 auth-required Swagger Try It Out を headless Chrome で検証する
	set -e; \
	trap '$(SAMPLE25_RUN) reset >/dev/null 2>&1 || true' EXIT; \
	KEEP_SAMPLE_STACK_RUNNING=1 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE25_COMPOSE_FILE) \
		--run-script=$(SAMPLE25_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample25EbookEditorAuthCmsDemoTest.php; \
	LAB_HTTP_PORT=18282 MTOOL_PROXY_PROJECT_TOKEN=sample25-token node mtool/scripts/check_sample25_auth_swagger_try_it_out.js \
		--lab-port=18282 \
		--lab-user="$${LAB_AUTH_STUB_USER:-lab-local}" \
		--lab-password="$${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}" \
		--db-source-key=config_db \
		--project-token=sample25-token

sample26-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE26_COMPOSE_FILE) \
		--run-script=$(SAMPLE26_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample26EbookHeadlessCmsCapstoneTest.php

sample27-runtime-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE27_COMPOSE_FILE) \
		--run-script=$(SAMPLE27_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample27AppLocalPersistenceDemoTest.php

sample18-pack-runtime-test-sqlite: sample18-runtime-output-test-sqlite ## sample18 tutorial runtime を SQLite config store profile で実行する

sample18-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample18-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE18_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE18_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample18MiniTaskBoardDemoTest.php

sample19-pack-runtime-test-sqlite: sample19-runtime-output-test-sqlite ## sample19 tutorial runtime を SQLite config store profile で実行する

sample19-runtime-output-test-sqlite:
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/tmp/config-store-sample19-sqlite-$$(date +%Y%m%d%H%M%S)}" SAMPLE_PACK_COMPOSE_LANE=base SAMPLE_PACK_INCLUDE_LIFECYCLE=0 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE19_SQLITE_COMPOSE_FILE) \
		--run-script=$(SAMPLE19_SQLITE_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample19JsonFirstContentModelOutputTest.php

sample18-http-runtime-smoke: ## sample18 task board demo page を HTTP route 経由で検証する
	set -e; \
	trap '$(SAMPLE18_RUN) reset >/dev/null 2>&1 || true' EXIT; \
	KEEP_SAMPLE_STACK_RUNNING=1 bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE18_COMPOSE_FILE) \
		--run-script=$(SAMPLE18_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample18MiniTaskBoardDemoTest.php; \
	LAB_HTTP_PORT=18272 $(PHP) mtool/scripts/check_sample18_task_board_http_smoke.php \
		--lab-base-url=http://127.0.0.1:18272 \
		--lab-user="$${LAB_AUTH_STUB_USER:-lab-local}" \
		--lab-password="$${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}" \
		--pretty

ARTIFACT_PARITY_RUN_ID ?= latest
ARTIFACT_PARITY_ROOT := work/artifact-parity/$(ARTIFACT_PARITY_RUN_ID)
USER_DB_CONTRACT_RUN_ID ?= latest
USER_DB_CONTRACT_ROOT := work/user-db-contract/$(USER_DB_CONTRACT_RUN_ID)
USER_DB_CONTRACT_SAMPLE ?= sample10-dbaccess-mini-crud-flow

up-user-db-pgsql: ## PostgreSQL user DB contract 用の local PostgreSQL を起動する
	USER_DB_PGSQL_HOST_PORT=$(USER_DB_PGSQL_HOST_PORT) \
	USER_DB_PGSQL_DB=$(USER_DB_PGSQL_DB) \
	USER_DB_PGSQL_USER=$(USER_DB_PGSQL_USER) \
	USER_DB_PGSQL_PASSWORD=$(USER_DB_PGSQL_PASSWORD) \
	$(COMPOSE_USER_DB_PGSQL) up -d --wait

down-user-db-pgsql: ## PostgreSQL user DB contract 用の local PostgreSQL を停止する
	$(COMPOSE_USER_DB_PGSQL) down

reset-user-db-pgsql: ## PostgreSQL user DB contract 用の local PostgreSQL volume を削除して停止する
	$(COMPOSE_USER_DB_PGSQL) down -v

ps-user-db-pgsql: ## PostgreSQL user DB contract 用 compose stack の状態を見る
	$(COMPOSE_USER_DB_PGSQL) ps

logs-user-db-pgsql: ## PostgreSQL user DB contract 用 compose stack のログを見る
	$(COMPOSE_USER_DB_PGSQL) logs --tail=120 user-db-pgsql

health-user-db-pgsql: ## PostgreSQL user DB contract 用 database の readiness を確認する
	$(COMPOSE_USER_DB_PGSQL) exec -T user-db-pgsql pg_isready -U $(USER_DB_PGSQL_USER) -d $(USER_DB_PGSQL_DB)

artifact-parity-capture-mysql: ## MySQL/MariaDB config store lane の parity 対象 artifact を capture する
	bash mtool/scripts/run_artifact_parity_capture.sh \
		--lane=mysql \
		--run-id=$(ARTIFACT_PARITY_RUN_ID)

artifact-parity-capture-sqlite: ## SQLite config store lane の parity 対象 artifact を capture する
	bash mtool/scripts/run_artifact_parity_capture.sh \
		--lane=sqlite \
		--run-id=$(ARTIFACT_PARITY_RUN_ID)

artifact-parity-compare: ## capture 済み MySQL/MariaDB lane と SQLite lane の artifact parity を比較する
	$(PHP) mtool/scripts/artifact_parity.php compare \
		--mysql=$(ARTIFACT_PARITY_ROOT)/mysql/manifest.json \
		--sqlite=$(ARTIFACT_PARITY_ROOT)/sqlite/manifest.json \
		--output=$(ARTIFACT_PARITY_ROOT)/compare.json \
		--pretty

artifact-parity-test: artifact-parity-capture-mysql artifact-parity-capture-sqlite artifact-parity-compare ## sample01-17 の artifact parity を capture + compare する

user-db-contract-capture-mysql: ## MySQL/MariaDB user DB contract 対象 output を capture する
	bash mtool/scripts/run_user_db_contract_capture.sh \
		--lane=mysql \
		--run-id=$(USER_DB_CONTRACT_RUN_ID) \
		--sample=$(USER_DB_CONTRACT_SAMPLE)

user-db-contract-capture-sqlite: ## SQLite user DB contract 対象 output を capture する
	bash mtool/scripts/run_user_db_contract_capture.sh \
		--lane=sqlite \
		--run-id=$(USER_DB_CONTRACT_RUN_ID) \
		--sample=$(USER_DB_CONTRACT_SAMPLE)

user-db-contract-capture-pgsql: ## PostgreSQL user DB contract 対象 output を capture する（MTOOL_RUNTIME_PGSQL_* が必要）
	bash mtool/scripts/run_user_db_contract_capture.sh \
		--lane=pgsql \
		--run-id=$(USER_DB_CONTRACT_RUN_ID) \
		--sample=$(USER_DB_CONTRACT_SAMPLE)

user-db-contract-compare: ## capture 済み MySQL/MariaDB lane と SQLite lane の user DB contract を比較する
	$(PHP) mtool/scripts/user_db_contract.php compare \
		--left=$(USER_DB_CONTRACT_ROOT)/mysql/manifest.json \
		--right=$(USER_DB_CONTRACT_ROOT)/sqlite/manifest.json \
		--output=$(USER_DB_CONTRACT_ROOT)/compare.json \
		--pretty

user-db-contract-compare-pgsql: ## capture 済み MySQL/MariaDB lane と PostgreSQL lane の user DB contract を比較する
	$(PHP) mtool/scripts/user_db_contract.php compare \
		--left=$(USER_DB_CONTRACT_ROOT)/mysql/manifest.json \
		--right=$(USER_DB_CONTRACT_ROOT)/pgsql/manifest.json \
		--output=$(USER_DB_CONTRACT_ROOT)/compare-pgsql.json \
		--pretty

user-db-contract-test: user-db-contract-capture-mysql user-db-contract-capture-sqlite user-db-contract-compare ## sample10 の user DB contract を capture + compare する

user-db-contract-test-pgsql: user-db-contract-capture-mysql user-db-contract-capture-pgsql user-db-contract-compare-pgsql ## sample10 の PostgreSQL user DB contract を capture + compare する（MTOOL_RUNTIME_PGSQL_* が必要）

postgresql-user-db-test-local: up-user-db-pgsql ## local PostgreSQL compose stack で user DB contract と sample12 live import を検証する
	MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=127.0.0.1;port=$(USER_DB_PGSQL_HOST_PORT);dbname=$(USER_DB_PGSQL_DB)' \
	MTOOL_RUNTIME_PGSQL_USER='$(USER_DB_PGSQL_USER)' \
	MTOOL_RUNTIME_PGSQL_PASSWORD='$(USER_DB_PGSQL_PASSWORD)' \
	$(MAKE) user-db-contract-test-pgsql USER_DB_CONTRACT_RUN_ID=$(USER_DB_CONTRACT_RUN_ID) USER_DB_CONTRACT_SAMPLE=$(USER_DB_CONTRACT_SAMPLE)
	MTOOL_RUNTIME_PGSQL_DSN='pgsql:host=$(USER_DB_PGSQL_CONTAINER_HOST);port=$(USER_DB_PGSQL_HOST_PORT);dbname=$(USER_DB_PGSQL_DB)' \
	MTOOL_RUNTIME_PGSQL_HOST='$(USER_DB_PGSQL_CONTAINER_HOST)' \
	MTOOL_RUNTIME_PGSQL_PORT='$(USER_DB_PGSQL_HOST_PORT)' \
	MTOOL_RUNTIME_PGSQL_DB='$(USER_DB_PGSQL_DB)' \
	MTOOL_RUNTIME_PGSQL_USER='$(USER_DB_PGSQL_USER)' \
	MTOOL_RUNTIME_PGSQL_PASSWORD='$(USER_DB_PGSQL_PASSWORD)' \
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE12_COMPOSE_FILE) \
		--run-script=$(SAMPLE12_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample12PostgresqlLiveSchemaImportTest.php

sample9-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample9TestPatternDefaultPropertyOutputTest.php

sample9-output-check: sample9-output-test

sample10-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample10CompareOutputCompanionDeclarationsOutputTest.php

sample10-output-check: sample10-output-test

sample11-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample11DaDataclassMethodOnlyOutputTest.php

sample11-output-check: sample11-output-test

sample12-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample12DbtablecolumnsWrapperPropertyOutputTest.php

sample12-output-check: sample12-output-test

sample13-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample13ReqMethodAndEnumOutputTest.php

sample13-output-check: sample13-output-test

sample14-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample14BuildSourceFuncCacheCompanionDeclarationsOutputTest.php

sample14-output-check: sample14-output-test

sample15-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample15BuildLogCompanionDeclarationsOutputTest.php

sample15-output-check: sample15-output-test

sample16-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample16LiveCheckResultCompanionDeclarationsOutputTest.php

sample16-output-check: sample16-output-test

sample17-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample17SpecContentTopLevelDeclarationOutputTest.php

sample17-output-check: sample17-output-test

sample18-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample18ProjectUserTopLevelDeclarationOutputTest.php

sample18-output-check: sample18-output-test

sample19-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample19HtmlTemplateTopLevelDeclarationOutputTest.php

sample19-output-check: sample19-output-test

sample20-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample20DaCustomProxyMethodAndEnumOutputTest.php

sample20-output-check: sample20-output-test

sample21-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample21ProjectMethodAndEnumOutputTest.php

sample21-output-check: sample21-output-test

sample22-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE1_COMPOSE_FILE) \
		--run-script=$(SAMPLE1_RUN) \
		--phpunit-target=/var/www/tests/Integration/Sample22ProjectSourceOutputMethodAndEnumOutputTest.php

sample22-output-check: sample22-output-test

build: ## local default stack を build する
	$(COMPOSE_LOCAL) build

up: ## local default stack を build して起動する。Lab DB UI も含む
	$(COMPOSE_LOCAL) up -d --build
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_LOCAL) up -d lab-db-ui
	bash mtool/scripts/show_compose_access_urls.sh

up-mtool: ## MTOOL core seed 付き local stack を build して起動する。Quickstart 用
	$(COMPOSE_MTOOL) up -d --build
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL) up -d lab-db-ui
	bash mtool/scripts/show_compose_access_urls.sh --compose-file=compose.local-db-config.yaml --compose-file=mtool/docker/compose/01_mtool.compose.yaml

start-mtool: ## 停止済み MTOOL core seed stack を起動する。Lab DB UI も含む
	$(COMPOSE_MTOOL) start
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL) up -d lab-db-ui
	bash mtool/scripts/show_compose_access_urls.sh --compose-file=compose.local-db-config.yaml --compose-file=mtool/docker/compose/01_mtool.compose.yaml

stop-mtool: ## MTOOL core seed stack を停止する
	$(COMPOSE_MTOOL) stop
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL) stop lab-db-ui || true

down-mtool: ## MTOOL core seed stack を停止して削除する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL) down

reset-mtool: ## MTOOL core seed stack を DB volume ごと削除する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL) down -v

ps-mtool: ## MTOOL core seed stack のサービス状態を表示する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL) ps

logs-mtool: ## MTOOL core seed stack の全サービスログを表示する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL) logs -f --tail=100

health-mtool: ## MTOOL core seed stack の admin / lab health を確認する
	@echo "[admin]"
	@$(COMPOSE_MTOOL) exec web-admin curl -fsS http://127.0.0.1/health
	@echo
	@echo "[lab]"
	@$(COMPOSE_MTOOL) exec web-lab curl -fsS http://127.0.0.1/health
	@echo

config-db-preflight-mtool: ## MTOOL core seed stack の config DB schema を確認する
	$(COMPOSE_MTOOL) exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=make

db-config-migrate-mtool: ## MTOOL core seed stack の config DB に config-initdb を再適用する
	$(COMPOSE_MTOOL) exec -T web-admin php /var/www/mtool/scripts/migrate_config_db.php --requested-by=make

up-mtool-lite: ## folder-backed SQLite config store で MTOOL lightweight stack を起動する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" $(COMPOSE_MTOOL_LITE) up -d --build web-admin web-lab db-lab
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL_LITE) up -d lab-db-ui
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" bash mtool/scripts/show_compose_access_urls.sh --compose-file=mtool/docker/compose/01_mtool-lite.compose.yaml

start-mtool-lite: ## 停止済み MTOOL lightweight stack を起動する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" $(COMPOSE_MTOOL_LITE) start web-admin web-lab db-lab
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL_LITE) up -d lab-db-ui
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" bash mtool/scripts/show_compose_access_urls.sh --compose-file=mtool/docker/compose/01_mtool-lite.compose.yaml

stop-mtool-lite: ## MTOOL lightweight stack を停止する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" $(COMPOSE_MTOOL_LITE) stop
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL_LITE) stop lab-db-ui || true

down-mtool-lite: ## MTOOL lightweight stack を停止して削除する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL_LITE) down

reset-mtool-lite: ## MTOOL lightweight stack を db-lab volume ごと削除する。SQLite config store folder は削除しない
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL_LITE) down -v

ps-mtool-lite: ## MTOOL lightweight stack のサービス状態を表示する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL_LITE) ps

logs-mtool-lite: ## MTOOL lightweight stack の全サービスログを表示する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_MTOOL_LITE) logs -f --tail=100

health-mtool-lite: ## MTOOL lightweight stack の admin / lab health を確認する
	@echo "[admin]"
	@APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" $(COMPOSE_MTOOL_LITE) exec web-admin curl -fsS http://127.0.0.1/health
	@echo
	@echo "[lab]"
	@APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" $(COMPOSE_MTOOL_LITE) exec web-lab curl -fsS http://127.0.0.1/health
	@echo

config-db-preflight-mtool-lite: ## MTOOL lightweight stack の SQLite config store schema を確認する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" $(COMPOSE_MTOOL_LITE) exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=make

db-config-migrate-mtool-lite: ## MTOOL lightweight stack の SQLite config store に config-initdb を再適用する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" $(COMPOSE_MTOOL_LITE) exec -T web-admin php /var/www/mtool/scripts/migrate_config_db.php --requested-by=make

mtool-lite-smoke: ## MTOOL lightweight SQLite lane の admin/script/backup/restore smoke を一括実行する
	bash mtool/scripts/run_mtool_lite_smoke.sh

backup-config-db-mtool-lite: backup-config-db-sqlite ## MTOOL lightweight stack の SQLite config store を CONFIG_DB_BACKUP_DIR に backup する

backup-config-db-mtool-lite-rotate: backup-config-db-sqlite-rotate ## MTOOL lightweight stack の SQLite config store backup を作成し rotation する

restore-config-db-mtool-lite: restore-config-db-sqlite ## MTOOL lightweight stack の SQLite config store を BACKUP_FILE=... から restore する

backup-config-db-mtool: ## MTOOL core seed stack の config DB を CONFIG_DB_BACKUP_DIR に SQL dump する
	@mkdir -p "$(CONFIG_DB_BACKUP_DIR)"
	@backup_file="$(CONFIG_DB_BACKUP_DIR)/config_db-mtool-$$(date +%Y%m%d-%H%M%S).sql"; \
	tmp_file="$$backup_file.tmp"; \
	manifest_file="$$backup_file.manifest.json"; \
	git_commit="$$(git rev-parse --short HEAD 2>/dev/null || echo unknown)"; \
	echo "[db-config] dumping to $$backup_file"; \
	$(COMPOSE_MTOOL) exec -T db-config sh -lc 'mariadb-dump --single-transaction --routines --triggers --events -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' > "$$tmp_file"; \
	mv "$$tmp_file" "$$backup_file"; \
	printf '{\n  "created_at": "%s",\n  "profile": "mtool-core-seed",\n  "backup_file": "%s",\n  "git_commit": "%s"\n}\n' "$$(date -u +%Y-%m-%dT%H:%M:%SZ)" "$$backup_file" "$$git_commit" > "$$manifest_file"; \
	echo "$$backup_file"

backup-config-db-mtool-rotate: backup-config-db-mtool ## MTOOL core seed stack の config DB backup を作成し、CONFIG_DB_BACKUP_KEEP_DAYS / KEEP_COUNT で整理する
	@echo "[db-config] rotating mtool backups in $(CONFIG_DB_BACKUP_DIR)"
	@if [ "$(CONFIG_DB_BACKUP_KEEP_DAYS)" -gt 0 ]; then \
		find "$(CONFIG_DB_BACKUP_DIR)" -maxdepth 1 -type f \( -name 'config_db-mtool-*.sql' -o -name 'config_db-mtool-*.sql.manifest.json' \) -mtime +"$(CONFIG_DB_BACKUP_KEEP_DAYS)" -print -delete; \
	fi
	@if [ "$(CONFIG_DB_BACKUP_KEEP_COUNT)" -gt 0 ]; then \
		ls -1t "$(CONFIG_DB_BACKUP_DIR)"/config_db-mtool-*.sql 2>/dev/null | tail -n +$$(($(CONFIG_DB_BACKUP_KEEP_COUNT) + 1)) | while IFS= read -r old_backup; do \
			echo "$$old_backup"; \
			rm -f "$$old_backup" "$$old_backup.manifest.json"; \
		done; \
	fi

restore-config-db-mtool: ## MTOOL core seed stack の config DB を BACKUP_FILE=... から restore する。CONFIRM_RESTORE=yes 必須
	@test -n "$(BACKUP_FILE)" || (echo "BACKUP_FILE is required, e.g. make restore-config-db-mtool BACKUP_FILE=$(CONFIG_DB_BACKUP_DIR)/config_db-mtool-YYYYMMDD-HHMMSS.sql CONFIRM_RESTORE=yes" >&2; exit 1)
	@test -f "$(BACKUP_FILE)" || (echo "BACKUP_FILE not found: $(BACKUP_FILE)" >&2; exit 1)
	@test "$(CONFIRM_RESTORE)" = "yes" || (echo "CONFIRM_RESTORE=yes is required because this overwrites config DB state." >&2; exit 1)
	@$(MAKE) backup-config-db-mtool
	$(COMPOSE_MTOOL) exec -T db-config sh -lc 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' < "$(BACKUP_FILE)"

up-durable-config-db: ## DURABLE_ENV_FILE の external config DB 設定で local db-config なしに起動する
	@test -f "$(DURABLE_ENV_FILE)" || (echo "DURABLE_ENV_FILE not found: $(DURABLE_ENV_FILE). Copy deploy/durable-config-db.env.example first." >&2; exit 1)
	$(COMPOSE_DURABLE) up -d --build web-admin web-lab db-lab
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_DURABLE) up -d lab-db-ui
	bash mtool/scripts/show_compose_access_urls.sh

down-durable-config-db: ## DURABLE_ENV_FILE の durable lane コンテナ / network を停止して削除する
	@test -f "$(DURABLE_ENV_FILE)" || (echo "DURABLE_ENV_FILE not found: $(DURABLE_ENV_FILE)" >&2; exit 1)
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_DURABLE) down

ps-durable-config-db: ## DURABLE_ENV_FILE の durable lane サービス状態を表示する
	@test -f "$(DURABLE_ENV_FILE)" || (echo "DURABLE_ENV_FILE not found: $(DURABLE_ENV_FILE)" >&2; exit 1)
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_DURABLE) ps

logs-durable-config-db: ## DURABLE_ENV_FILE の durable lane 全サービスログを表示する
	@test -f "$(DURABLE_ENV_FILE)" || (echo "DURABLE_ENV_FILE not found: $(DURABLE_ENV_FILE)" >&2; exit 1)
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_DURABLE) logs -f --tail=100

health-durable-config-db: ## DURABLE_ENV_FILE の durable lane admin / lab health を確認する
	@test -f "$(DURABLE_ENV_FILE)" || (echo "DURABLE_ENV_FILE not found: $(DURABLE_ENV_FILE)" >&2; exit 1)
	@echo "[admin]"
	@$(COMPOSE_DURABLE) exec web-admin curl -fsS http://127.0.0.1/health
	@echo
	@echo "[lab]"
	@$(COMPOSE_DURABLE) exec web-lab curl -fsS http://127.0.0.1/health
	@echo

config-db-preflight-durable-config-db: ## DURABLE_ENV_FILE の external config DB schema を確認する
	@test -f "$(DURABLE_ENV_FILE)" || (echo "DURABLE_ENV_FILE not found: $(DURABLE_ENV_FILE)" >&2; exit 1)
	$(COMPOSE_DURABLE) exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=make

db-config-migrate-durable-config-db: ## DURABLE_ENV_FILE の external config DB に config-initdb を再適用する
	@test -f "$(DURABLE_ENV_FILE)" || (echo "DURABLE_ENV_FILE not found: $(DURABLE_ENV_FILE)" >&2; exit 1)
	$(COMPOSE_DURABLE) exec -T web-admin php /var/www/mtool/scripts/migrate_config_db.php --requested-by=make

up-external-config-db: ## external APP_CONFIG_DB_* を使い、local db-config を起動せずに build/start する
	$(COMPOSE_BASE) up -d --build web-admin web-lab db-lab
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_BASE) up -d lab-db-ui
	bash mtool/scripts/show_compose_access_urls.sh

down-external-config-db: ## external config DB lane のコンテナ / network を停止して削除する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_BASE) down

ps-external-config-db: ## external config DB lane のサービス状態を表示する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_BASE) ps

logs-external-config-db: ## external config DB lane の全サービスログを表示する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_BASE) logs -f --tail=100

health-external-config-db: ## external config DB lane の admin / lab health を確認する
	@echo "[admin]"
	@$(COMPOSE_BASE) exec web-admin curl -fsS http://127.0.0.1/health
	@echo
	@echo "[lab]"
	@$(COMPOSE_BASE) exec web-lab curl -fsS http://127.0.0.1/health
	@echo

config-db-preflight-external-config-db: ## external config DB lane 経由で現在の APP_CONFIG_DB_* target が current schema か確認する
	$(COMPOSE_BASE) exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=make

db-config-migrate-external-config-db: ## external config DB lane 経由で config-initdb を現在の APP_CONFIG_DB target に再適用する
	$(COMPOSE_BASE) exec -T web-admin php /var/www/mtool/scripts/migrate_config_db.php --requested-by=make

start: ## 停止済み local default stack を起動する。Lab DB UI も含む
	$(COMPOSE_LOCAL) start
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_LOCAL) up -d lab-db-ui
	bash mtool/scripts/show_compose_access_urls.sh

stop: ## local default stack を停止する
	$(COMPOSE_LOCAL) stop
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_LOCAL) stop lab-db-ui || true

down: ## local default stack を停止して削除する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_LOCAL) down

reset: ## local default stack を DB volume ごと削除する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_LOCAL) down -v

ps: ## local default stack のサービス状態を表示する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_LOCAL) ps

logs: ## local default stack の全サービスログを表示する
	COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_LOCAL) logs -f --tail=100

health: ## local default stack の admin / lab health を確認する
	@echo "[admin]"
	@$(COMPOSE_LOCAL) exec web-admin curl -fsS http://127.0.0.1/health
	@echo
	@echo "[lab]"
	@$(COMPOSE_LOCAL) exec web-lab curl -fsS http://127.0.0.1/health
	@echo

admin-shell: ## local default stack の web-admin コンテナに入る
	$(COMPOSE_LOCAL) exec web-admin bash

lab-shell: ## local default stack の web-lab コンテナに入る
	$(COMPOSE_LOCAL) exec web-lab bash

db-config-shell: ## local default stack の db-config コンテナで MariaDB shell を開く
	$(COMPOSE_LOCAL) exec db-config sh -lc 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"'

db-lab-shell: ## local default stack の db-lab コンテナで MariaDB shell を開く
	$(COMPOSE_LOCAL) exec db-lab sh -lc 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"'

config-db-preflight: ## local default stack 経由で現在の APP_CONFIG_DB_* target が current schema か確認する
	$(COMPOSE_LOCAL) exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=make

db-config-migrate: ## local default stack 経由で config-initdb を現在の APP_CONFIG_DB target に再適用する
	$(COMPOSE_LOCAL) exec -T web-admin php /var/www/mtool/scripts/migrate_config_db.php --requested-by=make

backup-config-db: ## local default stack の config DB を CONFIG_DB_BACKUP_DIR に SQL dump する
	@mkdir -p "$(CONFIG_DB_BACKUP_DIR)"
	@backup_file="$(CONFIG_DB_BACKUP_DIR)/config_db-$$(date +%Y%m%d-%H%M%S).sql"; \
	tmp_file="$$backup_file.tmp"; \
	manifest_file="$$backup_file.manifest.json"; \
	git_commit="$$(git rev-parse --short HEAD 2>/dev/null || echo unknown)"; \
	echo "[db-config] dumping to $$backup_file"; \
	$(COMPOSE_LOCAL) exec -T db-config sh -lc 'mariadb-dump --single-transaction --routines --triggers --events -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' > "$$tmp_file"; \
	mv "$$tmp_file" "$$backup_file"; \
	printf '{\n  "created_at": "%s",\n  "profile": "local-default",\n  "backup_file": "%s",\n  "git_commit": "%s"\n}\n' "$$(date -u +%Y-%m-%dT%H:%M:%SZ)" "$$backup_file" "$$git_commit" > "$$manifest_file"; \
	echo "$$backup_file"

backup-config-db-rotate: backup-config-db ## local default stack の config DB backup を作成し、CONFIG_DB_BACKUP_KEEP_DAYS / KEEP_COUNT で整理する
	@echo "[db-config] rotating local backups in $(CONFIG_DB_BACKUP_DIR)"
	@if [ "$(CONFIG_DB_BACKUP_KEEP_DAYS)" -gt 0 ]; then \
		find "$(CONFIG_DB_BACKUP_DIR)" -maxdepth 1 -type f \( -name 'config_db-[0-9]*.sql' -o -name 'config_db-[0-9]*.sql.manifest.json' \) -mtime +"$(CONFIG_DB_BACKUP_KEEP_DAYS)" -print -delete; \
	fi
	@if [ "$(CONFIG_DB_BACKUP_KEEP_COUNT)" -gt 0 ]; then \
		ls -1t "$(CONFIG_DB_BACKUP_DIR)"/config_db-[0-9]*.sql 2>/dev/null | tail -n +$$(($(CONFIG_DB_BACKUP_KEEP_COUNT) + 1)) | while IFS= read -r old_backup; do \
			echo "$$old_backup"; \
			rm -f "$$old_backup" "$$old_backup.manifest.json"; \
		done; \
	fi

restore-config-db: ## local default stack の config DB を BACKUP_FILE=... から restore する。CONFIRM_RESTORE=yes 必須
	@test -n "$(BACKUP_FILE)" || (echo "BACKUP_FILE is required, e.g. make restore-config-db BACKUP_FILE=$(CONFIG_DB_BACKUP_DIR)/config_db-YYYYMMDD-HHMMSS.sql CONFIRM_RESTORE=yes" >&2; exit 1)
	@test -f "$(BACKUP_FILE)" || (echo "BACKUP_FILE not found: $(BACKUP_FILE)" >&2; exit 1)
	@test "$(CONFIRM_RESTORE)" = "yes" || (echo "CONFIRM_RESTORE=yes is required because this overwrites config DB state." >&2; exit 1)
	@$(MAKE) backup-config-db
	$(COMPOSE_LOCAL) exec -T db-config sh -lc 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' < "$(BACKUP_FILE)"

backup-config-db-sqlite: ## APP_CONFIG_STORE_DIR の SQLite config store を CONFIG_DB_BACKUP_DIR に backup する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" CONFIG_DB_BACKUP_DIR="$(CONFIG_DB_BACKUP_DIR)" $(PHP) mtool/scripts/config_store_sqlite_backup.php \
		--action=backup \
		--backup-dir="$(CONFIG_DB_BACKUP_DIR)" \
		--profile="$${CONFIG_DB_BACKUP_PROFILE:-sqlite-config-store}"

backup-config-db-sqlite-rotate: backup-config-db-sqlite ## APP_CONFIG_STORE_DIR の SQLite config store backup を作成し、CONFIG_DB_BACKUP_KEEP_DAYS / KEEP_COUNT で整理する
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" CONFIG_DB_BACKUP_DIR="$(CONFIG_DB_BACKUP_DIR)" CONFIG_DB_BACKUP_KEEP_DAYS="$(CONFIG_DB_BACKUP_KEEP_DAYS)" CONFIG_DB_BACKUP_KEEP_COUNT="$(CONFIG_DB_BACKUP_KEEP_COUNT)" $(PHP) mtool/scripts/config_store_sqlite_backup.php \
		--action=rotate \
		--backup-dir="$(CONFIG_DB_BACKUP_DIR)" \
		--keep-days="$(CONFIG_DB_BACKUP_KEEP_DAYS)" \
		--keep-count="$(CONFIG_DB_BACKUP_KEEP_COUNT)"

restore-config-db-sqlite: ## APP_CONFIG_STORE_DIR の SQLite config store を BACKUP_FILE=... から restore する。CONFIRM_RESTORE=yes 必須
	@test -n "$(BACKUP_FILE)" || (echo "BACKUP_FILE is required, e.g. make restore-config-db-sqlite BACKUP_FILE=$(CONFIG_DB_BACKUP_DIR)/config_store-sqlite-config-store-YYYYMMDD-HHMMSS.sqlite CONFIRM_RESTORE=yes" >&2; exit 1)
	@test -f "$(BACKUP_FILE)" || (echo "BACKUP_FILE not found: $(BACKUP_FILE)" >&2; exit 1)
	@test "$(CONFIRM_RESTORE)" = "yes" || (echo "CONFIRM_RESTORE=yes is required because this overwrites SQLite config store state." >&2; exit 1)
	APP_CONFIG_STORE_DIR="$${APP_CONFIG_STORE_DIR:-work/config-store}" CONFIG_DB_BACKUP_DIR="$(CONFIG_DB_BACKUP_DIR)" CONFIRM_RESTORE="$(CONFIRM_RESTORE)" $(PHP) mtool/scripts/config_store_sqlite_backup.php \
		--action=restore \
		--backup-dir="$(CONFIG_DB_BACKUP_DIR)" \
		--backup-file="$(BACKUP_FILE)" \
		--confirm-restore="$(CONFIRM_RESTORE)" \
		--profile="$${CONFIG_DB_BACKUP_PROFILE:-sqlite-config-store}"

db-lab-migrate: ## local default stack の db-lab に lab-initdb を再適用する
	@for f in docker/mariadb/lab-initdb/*.sql; do \
		echo "[db-lab] applying $$f"; \
		$(COMPOSE_LOCAL) exec -T db-lab sh -lc 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' < "$$f"; \
	done
