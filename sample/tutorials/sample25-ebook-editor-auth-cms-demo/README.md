# sample25-ebook-editor-auth-cms-demo

English companion:
This tutorial pack adds the editor side of the ebook CMS lane. It keeps the sample intentionally small: editor chapter preview, draft update, and publish are generated as ProjectToken-protected proxy endpoints. Public reader APIs remain in `sample24`.

- project key: `SAMPLE25`
- runtime root: `work/sample-packs/sample25-ebook-editor-auth-cms-demo/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON`, `AUTH-PROXY-SERVER`

## Original Prompt

```text
編集者だけが本や章を作成・更新・公開できる CMS API が欲しいです。
読者向け API は公開、編集 API は token がないと失敗するようにしてください。
```

## Interpreted Scope

`sample25` は production editor CMS ではなく、Mtool sample として editor write API の認証境界を見せる。

- tables:
  - `ebook_editor_book` -> `EbookEditorBookData`
  - `ebook_editor_chapter` -> `EbookEditorChapterData`
- DBAccess:
  - `EbookEditorChapter.GetEditorEbookChapter`
  - `EbookEditorChapter.UpdateEditorEbookChapterDraft`
  - `EbookEditorChapter.PublishEditorEbookChapter`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `OPENAPI-JSON`
  - `AUTH-PROXY-SERVER`

The physical tables use `ebook_editor_*` snake_case names while generated PHP, OpenAPI, and proxy names stay on the `EbookEditor*` logical surface. All three editor functions use `ProjectToken`. The generated auth proxy is expected to fail closed when `TOKEN` is missing, empty, wrong, or when `MTOOL_PROXY_PROJECT_TOKEN` is not configured.

## 起動

```bash
./sample/tutorials/sample25-ebook-editor-auth-cms-demo/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample25-ebook-editor-auth-cms-demo/run.sh apply-seed
```

## 検証

```bash
make sample25-pack-runtime-test
```

`sample20+` is MySQL / MariaDB canonical. There is no SQLite config store profile for this ebook/content demo lane.

## Out of Scope

- full editor UI
- user / role management
- audit log
- revision history / rollback
- multi-step approval workflow
- EPUB generation
- payment, DRM, or production operations
