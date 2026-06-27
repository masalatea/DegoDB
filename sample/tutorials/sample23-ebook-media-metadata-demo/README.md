# sample23-ebook-media-metadata-demo

English companion:
This tutorial pack adds ebook media metadata to the ebook lane. It keeps EPUB handling deliberately small: the sample does not create, parse, or upload EPUB files. It registers metadata for the self-authored EPUB fixture that already exists in this repository, then generates DataClass, DBAccess, and OpenAPI artifacts around that delivery surface.

- project key: `SAMPLE23`
- runtime root: `work/sample-packs/sample23-ebook-media-metadata-demo/`
- reference outputs: `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON`

## Original Prompt

```text
電子書籍には EPUB ファイルや表紙画像などの配信用ファイルがあります。
ファイル本体の生成やアップロードは不要です。
同梱済み EPUB をダウンロード表示できるように、URL、MIME type、サイズ、checksum を管理してください。
```

## Interpreted Scope

`sample23` は production asset manager ではなく、Mtool sample として EPUB-facing metadata を DB / API に落とす例に絞る。

- tables:
  - `ebook_media_book` -> `EbookMediaBookData`
  - `ebook_media_asset` -> `EbookMediaAssetData`
  - `ebook_media_book_asset` -> `EbookMediaBookAssetData`
  - `ebook_media_delivery` -> `EbookMediaDeliveryData`
- DBAccess:
  - `EbookMediaAsset.GetPublicEbookMediaDeliveryList`
  - `EbookMediaAsset.GetPublicEbookMediaAsset`
  - `EbookMediaAsset.InsertEbookMediaAsset`
  - `EbookMediaAsset.UpdateEbookMediaAssetMetadata`
- source outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `OPENAPI-JSON`

The EPUB row points at the bundled fixture:

- path: `sample/_assets/epub/json-first-mini-book/json-first-mini-book.epub`
- public URL in sample metadata: `/assets/epub/json-first-mini-book/json-first-mini-book.epub`
- MIME type: `application/epub+zip`
- size: `3125`
- sha256: `6b52e37129d9f01097da7e9b598b0e06d60a5b8e3b4126870c799cdc6c1dd5ea`

`ebook_media_asset` is the editable physical metadata table. `ebook_media_book_asset` links a book to one or more assets. `ebook_media_delivery` is a materialized public read model so the public API can return only published delivery metadata while generated PHP and OpenAPI names stay in the `EbookMedia*` logical form. The sample includes a draft cover placeholder to show that unpublished assets stay out of public delivery rows.

OpenAPI schema generation follows the DBAccess class source and exposes `EbookMediaAssetData`. The generated PHP DBAccess still uses `EbookMediaDeliveryData` for public read rows.

## 起動

```bash
./sample/tutorials/sample23-ebook-media-metadata-demo/run.sh up
```

seed を再適用する場合:

```bash
./sample/tutorials/sample23-ebook-media-metadata-demo/run.sh apply-seed
```

## 検証

```bash
make sample23-pack-runtime-test
```

`sample20+` is MySQL / MariaDB canonical. There is no SQLite config store profile for this ebook/content demo lane.

## Out of Scope

- EPUB generation
- EPUB parsing / metadata extraction
- file upload
- blob storage
- image processing
- CDN invalidation
- payment, DRM, or production operations
