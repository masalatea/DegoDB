COMPOSE ?= docker compose
PHP ?= php
LAB_DB_UI_PROFILE ?= lab-db-ui
COMPOSE_BASE := $(COMPOSE) -f compose.yaml
COMPOSE_LOCAL := $(COMPOSE_BASE) -f compose.local-db-config.yaml
COMPOSE_MTOOL := $(COMPOSE_LOCAL) -f mtool/docker/compose/01_mtool.compose.yaml
DURABLE_ENV_FILE ?= .env.durable
COMPOSE_DURABLE := $(COMPOSE) --env-file $(DURABLE_ENV_FILE) -f compose.yaml

.DEFAULT_GOAL := help

REFERENCE_ROOT := mtool/reference
REFERENCE_DBCLASSES_DIR := $(REFERENCE_ROOT)/dbclasses
WORK_ROOT := work
SAMPLE1_PACK_DIR := sample/tutorials/sample01-simple-table-runtime
SAMPLE1_COMPOSE_FILE := $(SAMPLE1_PACK_DIR)/compose.yaml
SAMPLE1_RUN := ./$(SAMPLE1_PACK_DIR)/run.sh
SAMPLE02_PACK_DIR := sample/tutorials/sample02-dataclass-nullable-default-status
SAMPLE02_COMPOSE_FILE := $(SAMPLE02_PACK_DIR)/compose.yaml
SAMPLE02_RUN := ./$(SAMPLE02_PACK_DIR)/run.sh
SAMPLE03_PACK_DIR := sample/tutorials/sample03-dataclass-lookup-and-helper
SAMPLE03_COMPOSE_FILE := $(SAMPLE03_PACK_DIR)/compose.yaml
SAMPLE03_RUN := ./$(SAMPLE03_PACK_DIR)/run.sh
SAMPLE04_PACK_DIR := sample/tutorials/sample04-dataclass-parent-child-basic
SAMPLE04_COMPOSE_FILE := $(SAMPLE04_PACK_DIR)/compose.yaml
SAMPLE04_RUN := ./$(SAMPLE04_PACK_DIR)/run.sh
SAMPLE05_PACK_DIR := sample/tutorials/sample05-dbaccess-select-basic
SAMPLE05_COMPOSE_FILE := $(SAMPLE05_PACK_DIR)/compose.yaml
SAMPLE05_RUN := ./$(SAMPLE05_PACK_DIR)/run.sh
SAMPLE06_PACK_DIR := sample/tutorials/sample06-dbaccess-filter-sort-page
SAMPLE06_COMPOSE_FILE := $(SAMPLE06_PACK_DIR)/compose.yaml
SAMPLE06_RUN := ./$(SAMPLE06_PACK_DIR)/run.sh
SAMPLE07_PACK_DIR := sample/tutorials/sample07-dbaccess-crud-basic
SAMPLE07_COMPOSE_FILE := $(SAMPLE07_PACK_DIR)/compose.yaml
SAMPLE07_RUN := ./$(SAMPLE07_PACK_DIR)/run.sh
SAMPLE08_PACK_DIR := sample/tutorials/sample08-dbaccess-join-read-model
SAMPLE08_COMPOSE_FILE := $(SAMPLE08_PACK_DIR)/compose.yaml
SAMPLE08_RUN := ./$(SAMPLE08_PACK_DIR)/run.sh
SAMPLE09_PACK_DIR := sample/tutorials/sample09-dbaccess-aggregate-report
SAMPLE09_COMPOSE_FILE := $(SAMPLE09_PACK_DIR)/compose.yaml
SAMPLE09_RUN := ./$(SAMPLE09_PACK_DIR)/run.sh
SAMPLE10_PACK_DIR := sample/tutorials/sample10-dbaccess-mini-crud-flow
SAMPLE10_COMPOSE_FILE := $(SAMPLE10_PACK_DIR)/compose.yaml
SAMPLE10_RUN := ./$(SAMPLE10_PACK_DIR)/run.sh

LEGACY_GENERATED_CLEAN_DIRS := \
	generated \
	work/legacy-generated
ROOT_TMP_DIR := tmp

.PHONY: help env env-force bootstrap-dbclasses bootstrap-dbclasses-runtime-reference promote-runtime-reference restore-runtime-reference-snapshot mtool-runtime-reference-status clean project-output db-access-sync mtool-canonical-sync mtool-self-loop-check mtool-proxy-output-check mtool-html-db-lang-res-wrapper-check mtool-lang-res-file-tree-export mtool-lang-res-file-tree-check mtool-external-source-lab-smoke mtool-external-source-lab-browser-smoke test sample-pack-compose-smoke sample-pack-runtime-smoke sample01-pack-runtime-test sample02-pack-runtime-test sample03-pack-runtime-test sample04-pack-runtime-test sample05-pack-runtime-test sample06-pack-runtime-test sample07-pack-runtime-test sample08-pack-runtime-test sample09-pack-runtime-test sample09-runtime-output-test sample10-pack-runtime-test sample10-runtime-output-test pattern01-output-test pattern02-output-test pattern03-output-test pattern04-output-test pattern05-output-test pattern06-output-test pattern07-output-test pattern08-output-test pattern09-output-test pattern10-output-test pattern11-output-test pattern12-output-test pattern13-output-test pattern14-output-test sample01-pack-output-test sample02-pack-output-test sample03-pack-output-test sample04-pack-output-test sample05-pack-output-test sample06-pack-output-test sample07-pack-output-test sample08-pack-output-test sample09-pack-output-test sample10-pack-output-test sample11-pack-output-test sample12-pack-output-test sample13-pack-output-test sample14-pack-output-test sample15-pack-output-test sample1-output-test sample1-output-check sample2-output-test sample2-output-check sample3-output-test sample3-output-check sample4-output-test sample4-output-check sample5-output-test sample5-output-check sample6-output-test sample6-output-check sample7-output-test sample7-output-check sample8-output-test sample8-output-check sample9-output-test sample9-output-check sample10-output-test sample10-output-check sample11-output-test sample11-output-check sample12-output-test sample12-output-check sample13-output-test sample13-output-check sample14-output-test sample14-output-check sample15-output-test sample15-output-check sample16-output-test sample16-output-check sample17-output-test sample17-output-check sample18-output-test sample18-output-check sample19-output-test sample19-output-check sample20-output-test sample20-output-check sample21-output-test sample21-output-check sample22-output-test sample22-output-check build up up-mtool start-mtool stop-mtool down-mtool reset-mtool ps-mtool logs-mtool health-mtool config-db-preflight-mtool up-external-config-db down-external-config-db ps-external-config-db logs-external-config-db health-external-config-db config-db-preflight-external-config-db db-config-migrate-external-config-db start stop down reset ps logs health admin-shell lab-shell db-config-shell db-lab-shell config-db-preflight db-config-migrate db-lab-migrate
.PHONY: backup-config-db restore-config-db backup-config-db-mtool restore-config-db-mtool up-durable-config-db ps-durable-config-db logs-durable-config-db health-durable-config-db config-db-preflight-durable-config-db db-config-migrate-durable-config-db down-durable-config-db

DOCKER_ENV_TARGETS := \
	build \
	up \
	up-mtool \
	start-mtool \
	health-mtool \
	config-db-preflight-mtool \
	backup-config-db-mtool \
	restore-config-db-mtool \
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
	$(COMPOSE_LOCAL) exec -T web-admin php /var/www/mtool/scripts/check_mtool_self_loop.php --requested-by=make

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
		--phpunit-target=/var/www/tests/Integration

sample-pack-compose-smoke: ## active runtime sample pack の compose override merge を軽く検証する
	bash mtool/scripts/check_sample_pack_compose_smoke.sh

sample-pack-runtime-smoke: ## representative runtime sample pack を up/apply-seed/health まで軽く検証する
	bash mtool/scripts/check_sample_pack_runtime_smoke.sh

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

sample2-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE02_COMPOSE_FILE) \
		--run-script=$(SAMPLE02_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php

sample2-output-check: sample2-output-test

sample3-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE03_COMPOSE_FILE) \
		--run-script=$(SAMPLE03_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample3DataclassLookupAndHelperOutputTest.php

sample3-output-check: sample3-output-test

sample4-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE04_COMPOSE_FILE) \
		--run-script=$(SAMPLE04_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample4DataclassParentChildBasicOutputTest.php

sample4-output-check: sample4-output-test

sample5-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE05_COMPOSE_FILE) \
		--run-script=$(SAMPLE05_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample5DbAccessSelectBasicOutputTest.php

sample5-output-check: sample5-output-test

sample6-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE06_COMPOSE_FILE) \
		--run-script=$(SAMPLE06_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample6DbAccessFilterSortPageOutputTest.php

sample6-output-check: sample6-output-test

sample7-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE07_COMPOSE_FILE) \
		--run-script=$(SAMPLE07_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample7DbAccessCrudBasicOutputTest.php

sample7-output-check: sample7-output-test

sample8-output-test:
	bash mtool/scripts/run_sample_pack_phpunit_test.sh \
		--compose-file=$(SAMPLE08_COMPOSE_FILE) \
		--run-script=$(SAMPLE08_RUN) \
		--apply-pack-seed \
		--phpunit-target=/var/www/tests/Integration/Sample8DbAccessJoinReadModelOutputTest.php

sample8-output-check: sample8-output-test

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

backup-config-db-mtool: ## MTOOL core seed stack の config DB を work/backups/config-db に SQL dump する
	@mkdir -p work/backups/config-db
	@backup_file="work/backups/config-db/config_db-mtool-$$(date +%Y%m%d-%H%M%S).sql"; \
	tmp_file="$$backup_file.tmp"; \
	echo "[db-config] dumping to $$backup_file"; \
	$(COMPOSE_MTOOL) exec -T db-config sh -lc 'mariadb-dump --single-transaction --routines --triggers --events -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' > "$$tmp_file"; \
	mv "$$tmp_file" "$$backup_file"; \
	echo "$$backup_file"

restore-config-db-mtool: ## MTOOL core seed stack の config DB を BACKUP_FILE=... から restore する。CONFIRM_RESTORE=yes 必須
	@test -n "$(BACKUP_FILE)" || (echo "BACKUP_FILE is required, e.g. make restore-config-db-mtool BACKUP_FILE=work/backups/config-db/config_db-mtool-YYYYMMDD-HHMMSS.sql CONFIRM_RESTORE=yes" >&2; exit 1)
	@test -f "$(BACKUP_FILE)" || (echo "BACKUP_FILE not found: $(BACKUP_FILE)" >&2; exit 1)
	@test "$(CONFIRM_RESTORE)" = "yes" || (echo "CONFIRM_RESTORE=yes is required because this overwrites config DB state." >&2; exit 1)
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

backup-config-db: ## local default stack の config DB を work/backups/config-db に SQL dump する
	@mkdir -p work/backups/config-db
	@backup_file="work/backups/config-db/config_db-$$(date +%Y%m%d-%H%M%S).sql"; \
	tmp_file="$$backup_file.tmp"; \
	echo "[db-config] dumping to $$backup_file"; \
	$(COMPOSE_LOCAL) exec -T db-config sh -lc 'mariadb-dump --single-transaction --routines --triggers --events -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' > "$$tmp_file"; \
	mv "$$tmp_file" "$$backup_file"; \
	echo "$$backup_file"

restore-config-db: ## local default stack の config DB を BACKUP_FILE=... から restore する。CONFIRM_RESTORE=yes 必須
	@test -n "$(BACKUP_FILE)" || (echo "BACKUP_FILE is required, e.g. make restore-config-db BACKUP_FILE=work/backups/config-db/config_db-YYYYMMDD-HHMMSS.sql CONFIRM_RESTORE=yes" >&2; exit 1)
	@test -f "$(BACKUP_FILE)" || (echo "BACKUP_FILE not found: $(BACKUP_FILE)" >&2; exit 1)
	@test "$(CONFIRM_RESTORE)" = "yes" || (echo "CONFIRM_RESTORE=yes is required because this overwrites config DB state." >&2; exit 1)
	$(COMPOSE_LOCAL) exec -T db-config sh -lc 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' < "$(BACKUP_FILE)"

db-lab-migrate: ## local default stack の db-lab に lab-initdb を再適用する
	@for f in docker/mariadb/lab-initdb/*.sql; do \
		echo "[db-lab] applying $$f"; \
		$(COMPOSE_LOCAL) exec -T db-lab sh -lc 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' < "$$f"; \
	done
