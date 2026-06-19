# sample22-ebook-chapter-workflow-demo

English companion:
This tutorial pack adds chapter workflow to the ebook lane. It keeps the sample focused: a book table, a chapter table, a materialized public chapter read model, public read functions, and minimal editor write functions for draft creation, content update, reorder, and publish.

- project key: `SAMPLE22`
- runtime root: `work/sample-packs/sample22-ebook-chapter-workflow-demo/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON`

## Original Prompt

```text
本ごとに章を管理したいです。
章には本文、並び順、下書き、レビュー中、公開済みの状態があり、
公開済みの章だけを読者向けに出したいです。
```

## Interpreted Scope

`sample22` は production editor ではなく、Mtool sample として chapter workflow metadata の最小公開面と編集入口を見せる。

- tables:
  - `EbookWorkflowBook`
  - `EbookWorkflowChapter`
  - `EbookWorkflowPublishedChapter`
- DBAccess:
  - `EbookWorkflowChapter.GetPublishedEbookWorkflowChapterList`
  - `EbookWorkflowChapter.GetPublishedEbookWorkflowChapter`
  - `EbookWorkflowChapter.InsertEbookWorkflowChapter`
  - `EbookWorkflowChapter.UpdateEbookWorkflowChapterDraft`
  - `EbookWorkflowChapter.UpdateEbookWorkflowChapterOrder`
  - `EbookWorkflowChapter.PublishEbookWorkflowChapter`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `OPENAPI-JSON`

`draft` chapters are kept in the fixture data but excluded from public DBAccess functions by the `Status = published` condition. `SpineOrder`, `NavLabel`, and `EpubResourcePath` are EPUB-facing metadata only; this sample does not generate or parse EPUB files.

OpenAPI schema generation follows the DBAccess class source and exposes `EbookWorkflowChapterData` for this sample. The generated PHP DBAccess still uses `EbookWorkflowPublishedChapterData` for public read rows.

## 起動

```bash
./sample/tutorials/sample22-ebook-chapter-workflow-demo/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample22-ebook-chapter-workflow-demo/run.sh apply-seed
```

## 検証

```bash
make sample22-pack-runtime-test
```

`sample20+` is MySQL / MariaDB canonical. There is no SQLite config store profile for this ebook/content demo lane.

## Out of Scope

- full editor UI
- revision history / diff / rollback
- approval workflow beyond status fields
- EPUB generation or EPUB parsing
- rendering Markdown to HTML
- search, payment, DRM, or production operations
