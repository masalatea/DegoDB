# sample18-mini-task-board-demo

English companion:
This tutorial pack is the first instruction-driven demo sample. It starts from a cleaned virtual user prompt, turns it into one small TaskCard project, and publishes multiple Source Outputs from that project.

- project key: `SAMPLE18`
- runtime root: `work/sample-packs/sample18-mini-task-board-demo/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON`
- no-code extraction output: `NO-CODE-RUNTIME`
- web-lab demo page: `/samples/sample18-task-board`

## Original Prompt

```text
小さなチーム用のタスクボードを作りたいです。

タスクにはタイトル、説明、状態、担当者、期限、優先度があり、
一覧では未完了タスクを期限順に見たいです。

できれば、タスクの詳細、作成、更新、完了もできるようにしてください。
HTML の簡単な画面と、外部から使える API 仕様も欲しいです。
まずは Docker でそのまま動く小さいデモにしてください。
```

## Interpreted Scope

`sample18` は、実際の会話ログをそのまま残すのではなく、AI が sample 向けに整理した prompt から作る demo です。

- table:
  - physical table: `task_card`
  - generated class/API surface: `TaskCard`
- DBAccess:
  - `TaskCard.GetTaskCardList`
  - `TaskCard.GetTaskCard`
  - `TaskCard.InsertTaskCard`
  - `TaskCard.UpdateTaskCard`
  - `TaskCard.CompleteTaskCard`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `HTML-PAGE`
  - `OPENAPI-JSON`
  - `NO-CODE-RUNTIME`

## Demo Pages

`run.sh up` で起動した後、次のページを確認できます。

- Task board demo page:
  - `http://127.0.0.1:18272/samples/sample18-task-board`
- Swagger / OpenAPI viewer:
  - `http://127.0.0.1:18272/runs/swagger/SAMPLE18?source_output_key=OPENAPI-JSON`

`web-lab` は login が必要です。local sample の既定値は次の通りです。

- user: `lab-local`
- password: `change-this-lab-password`

## Out of Scope

- production-level full CRUD UI
- ProjectToken auth
- multi-table lookup relation
- external database source import
- production-ready task workflow

## Generated / Curated Boundary

- `DATACLASS-PHP` and `DBACCESS-PHP` are generated from imported table metadata and DBAccess metadata.
- `OPENAPI-JSON` is generated from single-function proxy target metadata.
- `HTML-PAGE` publishes the curated module under `mtool/reference/html-modules/sample18/HTML-PAGE/current/`.
- `NO-CODE-RUNTIME` is generated from readonly `task_card` shared contract metadata for the first sample UI no-code extraction spike.
- Generated no-code action metadata declares create, update, complete, reopen, and delete as disabled dry-run route boundaries for the existing curated sample page. It does not replace the route or enable generated mutation buttons.
- `reference/` stores actual generated output only.

## 起動

```bash
./sample/tutorials/sample18-mini-task-board-demo/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample18-mini-task-board-demo/run.sh apply-seed
```

## 検証

```bash
make sample18-pack-runtime-test
```

web-lab の sample page まで HTTP 経由で確認する場合:

```bash
make sample18-http-runtime-smoke
```

この smoke は login、task 作成、task 編集まで確認する。

SQLite config store profile で同じ gate を見る場合:

```bash
make sample18-pack-runtime-test-sqlite
```

## Seed 内容

- project:
  - `project_key=SAMPLE18`
- live table:
  - physical table: `task_card`
  - generated class/API surface: `TaskCard`
- DBAccess:
  - `TaskCard.GetTaskCardList`
  - `TaskCard.GetTaskCard`
  - `TaskCard.InsertTaskCard`
  - `TaskCard.UpdateTaskCard`
  - `TaskCard.CompleteTaskCard`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `HTML-PAGE`
  - `OPENAPI-JSON`
  - `NO-CODE-RUNTIME`
- HTML module source:
  - `mtool/reference/html-modules/sample18/HTML-PAGE/current/`

## Manual Flow

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample18-mini-task-board-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE18 --source=live-schema --table=task_card

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample18-mini-task-board-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE18

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample18-mini-task-board-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE18 --source-output-key=DATACLASS-PHP --requested-by=sample18-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample18-mini-task-board-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE18 --source-output-key=DBACCESS-PHP --requested-by=sample18-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample18-mini-task-board-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE18 --source-output-key=HTML-PAGE --requested-by=sample18-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample18-mini-task-board-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE18 --source-output-key=OPENAPI-JSON --requested-by=sample18-manual --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample18-mini-task-board-demo/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE18 --source-output-key=NO-CODE-RUNTIME --requested-by=sample18-manual --publish
```

## Feedback Notes

- This first demo intentionally stays on one table so the instruction-driven sample remains readable.
- The web-lab page covers create, edit, complete, reopen, and delete as a small running UI.
- If OpenAPI write-function examples are too rough, improve request schema generation rather than hiding the write functions from the demo.
- A later demo can add lookup tables for status and priority once the single-table version is stable.
