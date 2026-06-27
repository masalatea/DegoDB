# sample24-ebook-public-reader-site-demo

English companion:
This tutorial pack turns the ebook lane into a small public reader site shape. It publishes read-only book, chapter, EPUB delivery metadata, a curated HTML reader page, and OpenAPI JSON. EPUB files are not generated or parsed here; the bundled fixture is only shown as a download asset.

- project key: `SAMPLE24`
- runtime root: `work/sample-packs/sample24-ebook-public-reader-site-demo/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON`

## Original Prompt

```text
公開中の電子書籍を読者が探して読めるサイトを作りたいです。
トップ、本一覧、本詳細、章本文ページを HTML と API で出し、
EPUB がある本はダウンロード導線も出してください。
```

## Interpreted Scope

`sample24` は production reader ではなく、Mtool sample として public reader の read surface を見せる。

- tables:
  - `ebook_reader_book` -> `EbookReaderBookData`
  - `ebook_reader_chapter` -> `EbookReaderChapterData`
  - `ebook_reader_media_delivery` -> `EbookReaderMediaDeliveryData`
- DBAccess:
  - `EbookReaderBook.GetPublicEbookReaderBookList`
  - `EbookReaderBook.GetPublicEbookReaderBook`
  - `EbookReaderBook.GetPublicEbookReaderChapterList`
  - `EbookReaderBook.GetPublicEbookReaderChapter`
  - `EbookReaderBook.GetPublicEbookReaderMediaDeliveryList`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `HTML-PAGE`
  - `OPENAPI-JSON`

The physical tables use `ebook_reader_*` snake_case names while generated PHP, OpenAPI, and proxy names stay on the `EbookReader*` logical surface. The HTML page is a curated generated artifact that shows top/detail/chapter/download affordances in one page. The API surface is the app-friendly side of the same public reader model.

## 起動

```bash
./sample/tutorials/sample24-ebook-public-reader-site-demo/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample24-ebook-public-reader-site-demo/run.sh apply-seed
```

## 検証

```bash
make sample24-pack-runtime-test
```

`sample20+` is MySQL / MariaDB canonical. There is no SQLite config store profile for this ebook/content demo lane.

## Out of Scope

- production routing
- full text search
- user bookshelf / purchase flow
- EPUB generation
- EPUB parsing
- in-browser EPUB renderer
- payment, DRM, or production operations
