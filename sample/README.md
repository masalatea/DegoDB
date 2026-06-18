# Sample Packs / sample pack 一覧

`sample/` は durable な sample pack の置き場です。

- `Project 1` は `MTOOL` 自体であり、core seed は `mtool/docker/mariadb/config-seed/` に置く
- `Project 1` の core compose override は `mtool/docker/compose/` に置く
- `Project 1` の検証 scenario は `tests/scenarios/` に置く
- `Project 2+` 相当は sample pack として `sample/<category>/<pack>/` に分ける
- active sample pack は `sample/tutorials/`、`sample/internal-patterns/`、`sample/legacy-projects/` の 3 系統に分ける
- `sample/tutorials/` は user-facing tutorial / demo の正本であり、`sample01-*` から simple-to-complex に並べる
- tutorial の段階設計と追加順は `docs/sample-tutorial-roadmap.md` を正本にする
- `sample/internal-patterns/` は rewrite / generator / migration guard 用の internal lane であり、`pattern01-*` から並べる
- `sample/legacy-projects/` は sanitized representative runtime pack を `sample51` / `sample53` / `sample56` に絞り、`50` 番台で category 境界を明示する
- current catalog 外の historical leftover は `sample/archive/` へ退避する
- sample pack 共通の運用 helper は `sample/_pack-support/` に置き、runner は `sample-pack-runner.sh` に揃える
- sample pack の disposable runtime state は `work/sample-packs/<pack>/` に出す
- runtime pack の `db-config` は `docker/mariadb/config-initdb/` の共通 schema と、その pack の `seed/` だけで fresh initdb する
- category ごとの入口は `sample/tutorials/README.md`、`sample/internal-patterns/README.md`、`sample/legacy-projects/README.md` を正本にし、archive 扱いは `sample/archive/README.md` で説明する

sample 配下には 2 種類あります。

- `runtime pack`
  - `README.md`
  - `compose.yaml`
  - `run.sh`
  - `seed/`
  - 必要なら `reference/`
- `file-based migration sample`
  - `README.md`
  - `reference/`
  - file fixture を入力にした migration gate 専用で、Docker runtime pack は持たない

category と structure type は別軸です。

- `sample/tutorials/`
  - current は `sample01-simple-table-runtime`、`sample02-dataclass-nullable-default-status`、`sample03-dataclass-lookup-and-helper`、`sample04-dataclass-parent-child-basic`、`sample05-dbaccess-select-basic`、`sample06-dbaccess-filter-sort-page`、`sample07-dbaccess-crud-basic`、`sample08-dbaccess-join-read-model`、`sample09-dbaccess-aggregate-report`、`sample10-dbaccess-mini-crud-flow`、`sample11-html-template-output`、`sample12-external-db-source-import`、`sample13-openapi-api-surface`、`sample14-custom-proxy-runtime`、`sample15-project-metadata-export-import`、`sample16-authenticated-proxy`、`sample17-multi-output-project`、`sample18-mini-task-board-demo`
  - 今後の user-facing tutorial はここへ追加する
- `sample/internal-patterns/`
  - `pattern01-default-property-split` から `pattern14-method-and-enum-heavy-multimethod` までの file-based migration sample
- `sample/legacy-projects/`
  - `sample51-runtime-sql-server`、`sample53-runtime-whiteboard`、`sample56-runtime-misc-proxy` は runtime pack
- `sample/archive/`
  - current catalog 外の archive / historical leftover

共通ルール:

- `reference/` には実ツールが出した actual output か、出所を確認できる legacy curated source だけを置く
- Codex などがそれっぽく書いた imitation output は `reference/` に置かない
- runtime pack の `seed/` には project row に加えて、代表的な legacy `project_source_outputs` metadata を少量だけ含める
- runtime pack へ generic な `RUNTIME-DBCLASSES` は seed しない
- buildable な source output を runtime pack に追加する場合だけ、その pack 自身の metadata と provenance が明確な実 source を前提に個別定義する

現在の active sample pack 一覧:

- `sample/tutorials/`
  - `sample01-simple-table-runtime`
  - `sample02-dataclass-nullable-default-status`
  - `sample03-dataclass-lookup-and-helper`
  - `sample04-dataclass-parent-child-basic`
  - `sample05-dbaccess-select-basic`
  - `sample06-dbaccess-filter-sort-page`
  - `sample07-dbaccess-crud-basic`
  - `sample08-dbaccess-join-read-model`
  - `sample09-dbaccess-aggregate-report`
  - `sample10-dbaccess-mini-crud-flow`
  - `sample11-html-template-output`
  - `sample12-external-db-source-import`
  - `sample13-openapi-api-surface`
  - `sample14-custom-proxy-runtime`
  - `sample15-project-metadata-export-import`
  - `sample16-authenticated-proxy`
  - `sample17-multi-output-project`
  - `sample18-mini-task-board-demo`
- `sample/internal-patterns/`
  - `pattern01-default-property-split`
  - `pattern02-wrapper-property-helper`
  - `pattern03-method-only-split`
  - `pattern04-method-and-enum-basic`
  - `pattern05-companion-declarations-basic`
  - `pattern06-companion-declarations-no-top-level`
  - `pattern07-companion-declarations-multiclass`
  - `pattern08-companion-declarations-multi-helper`
  - `pattern09-top-level-declaration-single`
  - `pattern10-top-level-declaration-multiclass`
  - `pattern11-top-level-declaration-html-template`
  - `pattern12-method-and-enum-no-top-level`
  - `pattern13-method-and-enum-multimethod`
  - `pattern14-method-and-enum-heavy-multimethod`
- `sample/legacy-projects/`
  - `sample51-runtime-sql-server`: legacy `Project.PID = 9`
  - `sample53-runtime-whiteboard`: legacy `Project.PID = 12`
  - `sample56-runtime-misc-proxy`: legacy `Project.PID = 16`

catalog guard:

- `tests/Integration/SamplePackCatalogTest.php`
  - category split、pack order、共通 runner、fixture catalog、`pattern01-14` の dedicated output test coverage を固定する
- `tests/Integration/LegacyProjectSampleCatalogTest.php`
  - remaining legacy project packs の canonical project key、project slug、seed、resource manifest contract を固定する
- `make sample-pack-compose-smoke`
  - active runtime pack の `compose.yaml` override merge が current catalog path で解決できることを host-side で軽く検証する
- `make sample-pack-runtime-smoke`
  - representative runtime pack (`sample51-runtime-sql-server`) を起動し、seed 適用と health / minimal runtime read を軽く検証する
- `make sample01-pack-runtime-test`
  - tutorial lane の canonical runtime test target
- `make pattern01-output-test`
  - internal pattern lane の canonical output test target

runtime pack の基本操作:

```bash
./sample/tutorials/sample01-simple-table-runtime/run.sh up
./sample/tutorials/sample01-simple-table-runtime/run.sh down
./sample/tutorials/sample01-simple-table-runtime/run.sh ps
./sample/tutorials/sample01-simple-table-runtime/run.sh apply-seed
```

file-based migration sample は `run.sh` や `compose.yaml` を持たず、fixture -> generated output -> `reference/` compare の gate 専用です。
`run.sh up` は root `compose.yaml` に pack の `compose.yaml` を重ねて起動します。
pack 固有の disposable runtime state は `work/sample-packs/<pack>/` 配下へ出ます。
publish 済みの current raw output は `work/source-outputs/{project_key}/{source_output_key}/` 配下へ出ます。
fresh initdb へ戻したい場合は `./sample/<category>/<pack>/run.sh reset` の後に `./sample/<category>/<pack>/run.sh up` を使います。
