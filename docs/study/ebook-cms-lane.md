# Ebook CMS Lane

English companion:
This study note explains how to read `sample19` through `sample26` as one ebook CMS tutorial lane. The goal is not to build a production CMS. The goal is to understand how a JSON-first idea can be interpreted into Mtool metadata, then published as DataClass, DBAccess, HTML, OpenAPI, auth proxy, and metadata bundle artifacts.

`sample19` から `sample26` は、JSON-first の発想から ebook / headless CMS 風の artifact へ進む tutorial lane です。

ここで見るものは production CMS ではありません。user / role 管理、revision、upload、EPUB build、search、payment などを完成させる話ではなく、Mtool が metadata からどの artifact を出せるかを段階的に読むための教材です。

## 最短確認

まず test が green になることだけ確認します。

```bash
make sample19-pack-runtime-test sample20-pack-runtime-test sample21-pack-runtime-test sample22-pack-runtime-test sample23-pack-runtime-test sample24-pack-runtime-test sample25-pack-runtime-test sample26-pack-runtime-test
```

`sample19` は JSON-first entrance なので SQLite config store profile も持ちます。

```bash
make sample19-pack-runtime-test-sqlite
```

`sample20` 以降は ebook / content lane として MySQL / MariaDB canonical profile に絞ります。これは sample の読みやすさと保守コストを優先した判断です。

## 読む順番

| sample | 追加される概念 | 主な output | この sample で見ないもの | 次に進む理由 |
| --- | --- | --- | --- | --- |
| `sample19` | JSON-first input を normalized table / read model として解釈する入口 | `DATACLASS-PHP`, `DBACCESS-PHP` | OpenAPI、HTML、editor workflow | JSON から DB / DBAccess metadata へ変換する最初の形を固定する |
| `sample20` | public content list/detail と HTML page | `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON` | ebook 固有 catalog、editor workflow、EPUB metadata | content publishing を最小の public surface として見る |
| `sample21` | ebook catalog domain | `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON` | reader site、chapter workflow、EPUB delivery | Book / Author / Genre など ebook らしい catalog metadata へ進む |
| `sample22` | chapter workflow と public/editor API の分離 | `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON` | auth proxy、reader HTML、revision history | draft と published chapter の境界を見る |
| `sample23` | EPUB / media delivery metadata | `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON` | EPUB build、upload、blob storage | file 本体ではなく delivery metadata を artifact 化する |
| `sample24` | public reader site | `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON` | editor auth API、purchase、search | public reader HTML と app API を同じ project から出す |
| `sample25` | ProjectToken protected editor CMS API | `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON`, `AUTH-PROXY-SERVER` | full editor UI、user / role 管理、audit log | editor API を fail-closed auth proxy として見る |
| `sample26` | headless CMS capstone | `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON`, `AUTH-PROXY-SERVER`, `PROJECT-METADATA-BUNDLE` | production CMS 全体 | public / app / editor / metadata bundle を 1 project でまとめて見る |

## まず見るファイル

各 sample では、最初から全 generated PHP を読む必要はありません。次の順で十分です。

1. `README.md`
2. `seed/`
3. `reference/`
4. 必要な場合だけ `mtool/scripts/lib/sampleNN_*_check.php`

読む時は、次の 4 点だけを追います。

- 何の table を seed しているか
- どの DBAccess function を sample の中心にしているか
- どの Source Output を publish しているか
- 何を out of scope にしているか

## sample と production の境界

この lane は「ebook CMS を製品として作る手順」ではありません。Mtool sample として、複雑な題材を次のように単純化しています。

- runtime は sample pack の isolated Docker stack で確認する
- schema は sample が読める程度に小さく保つ
- EPUB は fixture / URL / metadata として扱い、build pipeline は扱わない
- editor API は ProjectToken の最小 auth に絞る
- UI は public reader の HTML artifact までに留める
- production で必要な運用機能は README の out of scope に逃がす

この切り方により、読者は「Mtool が何を生成し、reference compare が何を保証しているか」に集中できます。

## 各 sample

- [sample19 JSON-first content model](../../sample/tutorials/sample19-json-first-content-model-demo/README.md)
- [sample20 content publishing](../../sample/tutorials/sample20-content-publishing-demo/README.md)
- [sample21 ebook catalog API](../../sample/tutorials/sample21-ebook-catalog-api-demo/README.md)
- [sample22 ebook chapter workflow](../../sample/tutorials/sample22-ebook-chapter-workflow-demo/README.md)
- [sample23 ebook media metadata](../../sample/tutorials/sample23-ebook-media-metadata-demo/README.md)
- [sample24 ebook public reader site](../../sample/tutorials/sample24-ebook-public-reader-site-demo/README.md)
- [sample25 ebook editor auth CMS](../../sample/tutorials/sample25-ebook-editor-auth-cms-demo/README.md)
- [sample26 ebook headless CMS capstone](../../sample/tutorials/sample26-ebook-headless-cms-capstone/README.md)
