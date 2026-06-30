# Sample Tutorial Packs / tutorial sample 一覧

- 役割: user-facing の tutorial sample / demo sample を simple-to-complex に並べる category
- pack 名は `sample01-*` から始める
- README / Make help / 今後の導線はここを優先する
- tutorial backlog と段階設計の正本は `docs/sample-tutorial-roadmap.md` を参照する
- sample を教材として読む hands-on guide は `docs/study/README.md` を参照する
- `sample19-26` の ebook CMS lane をまとめて読む場合は `docs/study/ebook-cms-lane.md` を参照する

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
- `sample19-json-first-content-model-demo`
- `sample20-content-publishing-demo`
- `sample21-ebook-catalog-api-demo`
- `sample22-ebook-chapter-workflow-demo`
- `sample23-ebook-media-metadata-demo`
- `sample24-ebook-public-reader-site-demo`
- `sample25-ebook-editor-auth-cms-demo`
- `sample26-ebook-headless-cms-capstone`
- `sample27-app-local-persistence-demo`
- `sample28-no-code-data-app-mvp`

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
make sample19-pack-runtime-test
make sample20-pack-runtime-test
make sample21-pack-runtime-test
make sample22-pack-runtime-test
make sample23-pack-runtime-test
make sample24-pack-runtime-test
make sample25-pack-runtime-test
make sample26-pack-runtime-test
make sample27-pack-runtime-test
make sample28-pack-runtime-test
make sample28-no-code-runtime-ui-smoke
make sample18-http-runtime-smoke
make test
```

`sample18-http-runtime-smoke` は `web-lab` の `/samples/sample18-task-board` に login し、画面から task を作成・編集できることまで確認する。

`sample19` は JSON-first content model entrance として、MySQL / MariaDB と SQLite config store profile の両方を検証する。

`sample20` からは ebook / content publishing lane に入り、runtime profile は MySQL / MariaDB canonical のみに絞る。`sample21` は ebook catalog API、`sample22` は chapter workflow、`sample23` は EPUB / media delivery metadata、`sample24` は public reader site、`sample25` は legacy-compatible ProjectToken protected editor CMS API、`sample26` は headless CMS capstone に進める。`sample27` は shared contract から App-local SQLite schema / DBAccess helper へ接続する App-local persistence demo。`sample28` は data-first no-code app MVP として、shared contract / managed operation metadata から `NO-CODE-RUNTIME` artifact を生成し、generated list/detail/form と browser dispatch intent を `sample28-no-code-runtime-ui-smoke` で確認する。current generated runtime security baseline は `sample16` の static bearer authenticated proxy で確認する。
