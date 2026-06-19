# sample26-ebook-headless-cms-capstone

English companion:
This capstone pack shows the ebook lane as a small headless CMS shape. A JSON-first ebook idea is interpreted as a compact Book / Chapter model, then Mtool publishes public reader artifacts, app API metadata, ProjectToken-protected editor proxy endpoints, and a project metadata bundle.

- project key: `SAMPLE26`
- runtime root: `work/sample-packs/sample26-ebook-headless-cms-capstone/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON`, `AUTH-PROXY-SERVER`, `PROJECT-METADATA-BUNDLE`

## Original Prompt

```text
電子書籍サイトとスマホ app の両方から使える headless CMS の小さいデモが欲しいです。
編集者は本、著者、章、表紙を管理でき、読者は公開中の本と章をサイトや app API から読めるようにしてください。
```

## Interpreted Scope

`sample26` は production CMS ではなく、Mtool sample として JSON-first ebook model から複数 artifact を publish する到達点を見せる。

- tables:
  - `EbookCmsBook`
  - `EbookCmsChapter`
- public DBAccess:
  - `EbookCmsBook.GetPublicEbookCmsBookList`
  - `EbookCmsBook.GetPublicEbookCmsBook`
  - `EbookCmsBook.GetPublicEbookCmsChapterList`
  - `EbookCmsBook.GetPublicEbookCmsChapter`
  - `EbookCmsBook.GetPublicEbookCmsEpubDeliveryList`
- editor DBAccess:
  - `EbookCmsBook.GetEditorEbookCmsChapter`
  - `EbookCmsBook.UpdateEditorEbookCmsChapterDraft`
  - `EbookCmsBook.PublishEditorEbookCmsChapter`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `HTML-PAGE`
  - `OPENAPI-JSON`
  - `AUTH-PROXY-SERVER`
  - `PROJECT-METADATA-BUNDLE`

Public reader APIs use `NoSecurity`. Editor APIs use `ProjectToken` and are also published through `AUTH-PROXY-SERVER`. The metadata bundle is exported by the checker to demonstrate how the whole project definition can be carried as headless CMS configuration.

## 起動

```bash
./sample/tutorials/sample26-ebook-headless-cms-capstone/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample26-ebook-headless-cms-capstone/run.sh apply-seed
```

## 検証

```bash
make sample26-pack-runtime-test
```

`sample20+` is MySQL / MariaDB canonical. There is no SQLite config store profile for this ebook/content demo lane.

## Production Notes

本運用なら user / role 管理、audit log、revision history、approval workflow、file upload、EPUB build pipeline、search index、purchase / DRM、backup / monitoring が必要になる。ここでは Mtool の sample として、DB / API / HTML / auth proxy / metadata bundle の生成境界を見せるために削っている。
