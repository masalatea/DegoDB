# Sample Tutorial Packs / tutorial sample 一覧

- 役割: user-facing の tutorial sample / demo sample を simple-to-complex に並べる category
- pack 名は `sample01-*` から始める
- README / Make help / 今後の導線はここを優先する
- tutorial backlog と段階設計の正本は `docs/sample-tutorial-roadmap.md` を参照する
- sample を教材として読む hands-on guide は `docs/study/README.md` を参照する

current runtime tutorial packs:

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

上記は runtime pack であり、`compose.yaml` / `run.sh` / `seed/` を持つ。

基本操作:

```bash
./sample/tutorials/sample01-simple-table-runtime/run.sh up
./sample/tutorials/sample01-simple-table-runtime/run.sh apply-seed
make sample01-pack-runtime-test
make sample11-pack-runtime-test
make sample12-pack-runtime-test
make sample13-pack-runtime-test
make sample14-pack-runtime-test
make sample15-pack-runtime-test
make sample16-pack-runtime-test
make sample17-pack-runtime-test
make sample18-pack-runtime-test
make sample18-http-runtime-smoke
make test
```

`sample18-http-runtime-smoke` は `web-lab` の `/samples/sample18-task-board` に login し、画面から task を作成・編集できることまで確認する。

今後の tutorial / demo sample は `sample19+` として上の順を延長して追加する。
