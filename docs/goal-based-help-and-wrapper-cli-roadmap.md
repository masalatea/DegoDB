# Goal-Based Help And Wrapper CLI Roadmap / 目的別 help と wrapper CLI roadmap

English companion:
This document defines the planned user-facing command model before adding a wrapper CLI. The current repo already has `make` targets, sample `run.sh` scripts, and focused PHP scripts; this roadmap keeps those as the implementation layer and designs a goal-based entry layer above them.

この文書は、wrapper CLI を実装する前に、ユーザが「目的」から current command へ辿るための help model を定義する。  
現時点の実行正本は `make` target、sample ごとの `run.sh`、`mtool/scripts/*.php` のままにし、その上に将来の薄い入口 layer を置く。

## Purpose / 目的

DegoDB / Mtool の current workflow は、知っている人には十分に操作できる。一方で、初見の利用者や導入支援の場では、「どの command を叩くか」より先に「何をしたいか」が決まっている。

この roadmap は以下を分ける。

| Layer / 層 | Role / 役割 | Current state / 現状 |
| --- | --- | --- |
| Goal help | 目的から読む help group | This document and [Choose Your Path](choose-your-path.md) |
| Wrapper CLI | `dego ...` or `mtool ...` style user entrypoint | Future implementation |
| Existing commands | `make`, sample `run.sh`, PHP scripts | Current source of truth |
| Internal scripts | focused scripts under `mtool/scripts/` | Kept stable; wrapped, not replaced |

## Current Rule / 現在のルール

- This roadmap does not replace current commands. / この roadmap は current command を置き換えない。
- Wrapper CLI should call existing `make` targets or scripts first. / wrapper CLI はまず既存 `make` target または script を呼ぶ。
- A wrapper command must print the underlying command before executing it. / wrapper command は実行前に underlying command を表示する。
- Destructive operations require explicit confirmation flags. / destructive operation は明示 confirmation flag を必須にする。
- Project metadata, generated artifacts, and reference snapshots remain owned by existing services. / project metadata、generated artifact、reference snapshot の正本は既存 service が持つ。
- Do not add hidden magic around config DB or source output generation. / config DB や source output generation に隠れた magic を足さない。

## Goal Help Groups / 目的別 help group

| Goal group / 目的 | User question / ユーザの問い | Current command entry / 現在の入口 | Future help command / 将来 |
| --- | --- | --- | --- |
| Start local | local でまず動かしたい | `make env`, `make up-mtool`, `make health-mtool` | `dego help start` |
| Learn samples | sample を順に触りたい | `make sample01-pack-runtime-test`, `make sample17-pack-runtime-test`, then follow `docs/sample-tutorial-roadmap.md` through sample47 | `dego help sample` |
| Import schema | DB schema を canonical metadata に取り込みたい | `php /var/www/mtool/scripts/import_project_tables.php ...` | `dego help import` |
| Sync design | DataClass / DBAccess metadata を同期したい | `make mtool-canonical-sync`, `sync_project_data_classes.php`, `sync_project_db_access.php` | `dego help sync` |
| Publish output | Source Output を publish したい | `create_project_output.php --publish` | `dego help output` |
| Inspect handoff | AI context / modernization audit を出したい | `AI-CONTEXT-MD`, `MODERNIZATION-AUDIT-MD` source outputs | `dego help handoff` |
| Bundle project | project metadata を export / import preview したい | `export_project_metadata.php`, `import_project_metadata.php --mode=preview` | `dego help bundle` |
| Use config store | local / lite / durable config store を選びたい | `make up-mtool`, `make up-mtool-lite`, `make up-durable-config-db` | `dego help config-store` |
| Verify state | green state を確認したい | `make test`, sample pack tests, status scripts | `dego help verify` |
| Diagnose issues | warning / error の意味を切り分けたい | [Troubleshooting](troubleshooting.md), status scripts | `dego help troubleshoot` |

## Wrapper CLI Command Shape / wrapper CLI の形

The first wrapper should be thin and predictable. / 最初の wrapper は薄く、予測可能にする。

```bash
dego help
dego help start
dego help sample
dego sample test sample17
dego stack up --profile mtool
dego stack health --profile mtool
dego project import SAMPLE17 --source live-schema --table CapstoneTask
dego project sync SAMPLE17 --data-class --db-access
dego output publish SAMPLE17 AI-CONTEXT-MD
dego output publish SAMPLE17 MODERNIZATION-AUDIT-MD
dego project bundle export MTOOL --database-source lab-live-schema
dego project bundle preview /path/to/bundle --secrets /path/to/secrets.json
```

Names are provisional until implementation. / command name は実装時に確定する。

## Command Mapping / command 対応表

| Future command / 将来 command | Current command / 現在 command |
| --- | --- |
| `dego stack up --profile mtool` | `make up-mtool` |
| `dego stack up --profile lite` | `make up-mtool-lite` |
| `dego stack health --profile mtool` | `make health-mtool` |
| `dego sample test sample17` | `make sample17-pack-runtime-test` |
| `dego sample test sample17 --sqlite-config` | `make sample17-pack-runtime-test-sqlite` |
| `dego project import SAMPLE17 --source live-schema --table CapstoneTask` | `docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE17 --source=live-schema --table=CapstoneTask` |
| `dego project sync SAMPLE17 --data-class` | `docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE17` |
| `dego project sync SAMPLE17 --db-access` | `docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php --project-key=SAMPLE17` |
| `dego output publish SAMPLE17 AI-CONTEXT-MD` | `docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE17 --source-output-key=AI-CONTEXT-MD --publish` |
| `dego output plan SAMPLE17 MODERNIZATION-AUDIT-MD` | `docker compose exec -T web-admin php /var/www/mtool/scripts/show_source_output_build_plan.php --project-key=SAMPLE17 --source-output-key=MODERNIZATION-AUDIT-MD` |
| `dego verify runtime-reference` | `php mtool/scripts/show_runtime_reference_status.php --require-current` |
| `dego verify rollout` | `php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only` |

## Help Output Design / help 表示設計

`dego help` should not dump every script. / `dego help` は全 script 一覧をそのまま出さない。

It should show goal groups first. / まず目的 group を出す。

```text
Usage:
  dego help [goal]

Goals:
  start          Start local DegoDB / Mtool stacks
  sample         Run tutorial samples and profile variants
  import         Import DB schema into canonical metadata
  sync           Sync DataClass / DBAccess metadata
  output         Publish Source Outputs
  handoff        Generate AI context and modernization audit outputs
  bundle         Export or preview project metadata bundles
  config-store   Choose local, lite, durable, or external config store lanes
  verify         Run green-state checks
  troubleshoot   Find status and warning diagnostics
```

Each goal help should include:

- What the goal does / 何をするか
- Required state / 事前状態
- Current command / 現在の command
- Wrapper command preview / 将来 command
- Safety notes / 注意点
- Link to canonical doc / 正本文書への link

## Roadmap / 実装順

| Phase | Scope / 範囲 | Done when / 完了条件 |
| --- | --- | --- |
| 1 | Document goal groups and wrapper command names | This document exists and is linked from the entry docs |
| 2 | Add a read-only help script | `php mtool/scripts/dego_help.php [goal]` prints goal help without executing commands |
| 3 | Add a thin wrapper prototype | wrapper prints and runs selected safe commands |
| 4 | Add destructive-command guardrails | reset / restore / apply operations require explicit confirmation |
| 5 | Promote stable wrapper entrypoint | `dego` or `mtool` executable is documented as a supported user entry |

Phase 2 should come before any command execution wrapper. / command 実行 wrapper より前に Phase 2 の read-only help を入れる。

## Naming Decision / 名前の判断

Two names are plausible.

| Name | Pros | Cons |
| --- | --- | --- |
| `dego` | Short user-facing product entry; good for docs and examples | New executable name must be introduced |
| `mtool` | Matches internal tool name and current scripts | Can blur app/tool/repo boundaries |

Default recommendation: use `dego` for the external wrapper and keep `mtool/scripts/*` as implementation scripts.  
既定案は、外向き wrapper を `dego`、既存実装 script を `mtool/scripts/*` として分けること。

## Non-Goals / 今回やらないこと

- Do not implement a full CLI framework in this planning slice. / この planning slice では full CLI framework を実装しない。
- Do not rename existing `make` targets. / 既存 `make` target は rename しない。
- Do not remove PHP scripts that tests or docs already use. / 既存 test/doc が使う PHP script を消さない。
- Do not hide Docker compose lane differences. / Docker compose lane の違いを隠さない。
- Do not make `MODERNIZATION-AUDIT-MD` an implicit default output in this roadmap. / この roadmap で `MODERNIZATION-AUDIT-MD` を implicit default 化しない。

## Links / 関連文書

- [Choose Your Path / 目的別の読み方](choose-your-path.md)
- [Common Tasks / よく使う作業](common-tasks.md)
- [Existing DB To Output / 既存 DB から出力まで](existing-db-to-output.md)
- [Project Metadata Bundle / プロジェクトメタデータ bundle](project-metadata-bundle.md)
- [Config DB Externalization / config DB 外部化](config-db-externalization.md)
- [Current Plans / 現在の計画](current-plans.md)
