# 2026-06-19 Ebook Headless CMS Sample Plan

## Status

- status: `SAMPLE19_26_DONE_AS_TEACHING_SAMPLE`
- target lane: `sample/tutorials/sample19+`
- current base: `sample18-mini-task-board-demo`
- goal: 電子書籍サイト & app の headless CMS 的な使い方へ、段階的に到達する sample 作成計画
- completion note: `sample19-26` は runtime pack、reference output、checker、study guide、比較表、tutorial 導線まで含めて完了扱いにする。

## Completion Boundary

`sample19-26` は、製品 CMS を作る計画ではなく、サンプル教材として完了した lane として扱う。

今後この lane について次の作業を提案する場合は、production feature 追加ではなく、次の基準に寄せる。

- 読みやすさ
- sample 間の粒度揃え
- study guide / README / roadmap の導線
- generated artifact と reference compare の理解しやすさ
- scope cut / out of scope の明確化

逆に、次のような提案はこの lane の通常の次作業として扱わない。

- editor UI の本格化
- user / role 管理の実装
- revision history / approval workflow
- upload / EPUB build pipeline
- search / payment / DRM
- production monitoring / backup / migration strategy

それらが必要になった場合は、sample19-26 の続きではなく、別の product / foundation plan として明示的に扱う。

## Progress

- `sample19-json-first-content-model-demo`: implemented as the JSON-first entrance. MySQL / MariaDB and SQLite config store profiles are both covered.
- `sample20-content-publishing-demo`: implemented as the first MySQL / MariaDB canonical ebook/content lane. It keeps scope to generated DataClass / DBAccess / HTML / OpenAPI artifacts and does not add a dedicated web-lab app page.
- `sample21-ebook-catalog-api-demo`: implemented as the first ebook catalog API sample. EPUB is represented only by delivery metadata fields.
- `sample22-ebook-chapter-workflow-demo`: implemented as chapter workflow sample. EPUB is represented only by `SpineOrder` / `NavLabel` / `EpubResourcePath` metadata.
- `sample23-ebook-media-metadata-demo`: implemented as EPUB / media delivery metadata sample. EPUB is represented by URL / MIME type / file size / sha256 / version metadata for the bundled fixture.
- `sample24-ebook-public-reader-site-demo`: implemented as public reader site sample. It publishes a curated HTML reader artifact and public read OpenAPI surface.
- `sample25-ebook-editor-auth-cms-demo`: implemented as editor-side authenticated CMS API sample. It publishes ProjectToken protected preview / draft update / publish endpoints and verifies fail-closed auth behavior.
- `sample26-ebook-headless-cms-capstone`: implemented as the capstone. It publishes public reader HTML, app OpenAPI, ProjectToken editor proxy endpoints, and a project metadata bundle from one compact ebook CMS project.

## Implementation Retrospective

当初の会話では、`sample19-26` を全部作るには保守的に 2-3 日程度かかる可能性を見ていた。実際には、Mtool sample として scope を切ったことで、見立てよりかなり早く実装できた。

ざっくりした数字で見ると、当初見立てを 16-24 時間程度の作業と置くなら、実際の `sample19-26` 追加・見直し・検証は数時間規模に収まった。厳密な工数計測ではないが、体感評価ではおよそ 70-85% 程度早かった、つまり当初見立ての 15-30% 程度の時間で到達できたと見るのが近い。

AI 側の素直な評価としては、最初の見積もりは「普通に実運用寄りの demo を作る」前提に寄りすぎていた。今回の実装は production CMS ではなく、Mtool の価値を見せる tutorial lane であるため、DataClass / DBAccess / OpenAPI / auth proxy / metadata bundle の生成は Mtool に任せ、手作業は seed、checker、reference 固定、README に集中できた。このため、実装速度は当初見立てより大きく速くなった。

早くできた主な理由:

- 本運用に必要な editor UI、user / role 管理、audit log、revision history、approval workflow、file upload、EPUB build、search、payment / DRM を scope out した。
- `sample20-26` は完全な新規 app ではなく、既存 runtime pack / Source Output / checker pattern の組み合わせで作れた。
- `sample24` の public reader pattern と `sample25` の ProjectToken auth proxy pattern を `sample26` に再利用できた。
- generated artifact は actual Mtool output として固定し、手書き imitation output を作らなかった。

この結果は、Mtool の sample としては望ましい。つまり「JSON-first に業務イメージを置く -> AI が DB / API / output metadata へ解釈する -> Mtool が artifact を生成する」という価値が、手作業量をかなり削れる形で確認できた。一方で、この速度を production-ready CMS の実装速度と混同しないよう、各 sample の `Out of Scope` / `Production Notes` は維持する。

これは元々の Mtool policy とも一致する。複雑なものをすべて core flow で直接扱うのではなく、正規化・単純化できる大部分のケースを最適化し、そこから外れるものだけを例外・拡張・custom runtime work として扱う、という方針である。今回の ebook CMS lane は、本運用の複雑さを sample scope から切り、正規化できる DB / API / output artifact の流れに集中したため、目的に沿った結果になった。

補足すると、これは「Mtool では難しいものに対応できない」という意味ではない。DataClass は共通の data representation として流用でき、必要なら generated base data class を継承した derived class 側に domain method を足せる。DBAccess も同様に、generated foundation を流用したうえで、Mtool の generator がまだ直接扱わない query、transaction、workflow、integration だけを AI または手書きで追加できる。つまり Mtool の意図は、すべてを generator に押し込むことではなく、生成で済む部分と custom code で書く部分の boundary を明確にし、framework と generated class を再利用しながら unsupported / application-specific な部分だけを実装する、という整理である。

## Mtool Compared With ORM-like Tools

Latest living version:
`docs/mtool-positioning.md`

ここでいう ORM / OR mapper 類似ツールは、Prisma、TypeORM、Sequelize、SQLAlchemy、Doctrine、Rails ActiveRecord、Django ORM のような、application code から database table / row を扱うための開発者向け tool を指す。

一般的な ORM / OR mapper 類似ツールの主な役割:

- application code から DB row / relation を扱いやすくする。
- model、query、migration を code-first または schema-first に管理する。
- 開発者が schema、relation、query、transaction を理解して使う前提が強い。
- API spec、HTML artifact、authenticated proxy、project metadata bundle は通常は別の framework / tool / custom code で作る。

Mtool の主な役割:

- project metadata から DataClass、DBAccess、OpenAPI、HTML artifact、auth proxy、metadata bundle を publish する。
- DB を知らない user の JSON-first 入力を、AI が normalized DB / API / output metadata へ解釈する sample story と相性がよい。
- generated artifact を `reference/` として固定し、sample / tutorial / regression check に使いやすい。
- application runtime の business logic よりも、DB / API / output artifact の構成と生成を説明することに寄っている。

Mtool のメリット:

- 同じ project metadata から複数の output を生成できる。
- sample として「入力 story -> DB / API metadata -> generated artifact -> reference compare」までを一貫して見せやすい。
- AI が設計した DB / API の形を、seed と checker に落とし込みやすい。
- OpenAPI、HTML、auth proxy、metadata bundle まで含めた tutorial lane を作りやすい。
- 手書き application code ではなく、metadata と generated output の差分で検証しやすい。

Mtool のデメリット / ORM 類似ツールに比べて弱い点:

- application code 内での柔軟な query composition は ORM の方が得意。
- complex transaction、domain service、business logic の自動生成は Mtool の主目的ではない。ただし generated DataClass / DBAccess を継承・拡張して、未対応部分だけを AI または手書きで実装することはできる。
- migration workflow、IDE 補完、ecosystem、framework integration は一般的な ORM の方が成熟していることが多い。
- production app の日常的な model 操作、test fixture、developer ergonomics は ORM 側が有利な場面が多い。
- Mtool の強みは metadata / artifact generation にあるため、runtime application の細かい挙動は extension boundary で別途設計・実装する。

この比較は、Mtool を ORM の置き換えとして評価するより、metadata-driven output generator / tutorial sample builder として評価した方が分かりやすい、という整理である。

## 結論

`sample18` の次に、推奨では 8 本の sample を追加する。

- `sample19-json-first-content-model-demo`
- `sample20-content-publishing-demo`
- `sample21-ebook-catalog-api-demo`
- `sample22-ebook-chapter-workflow-demo`
- `sample23-ebook-media-metadata-demo`
- `sample24-ebook-public-reader-site-demo`
- `sample25-ebook-editor-auth-cms-demo`
- `sample26-ebook-headless-cms-capstone`

最短で見せるだけなら `sample19`、`sample21`、`sample24`、`sample26` の 4 本でも可能。ただし、電子書籍 CMS として自然に見せるには、content publishing、章管理、表紙・メディア metadata、編集者向け認証 write API を途中で分けた方が、各 sample の主題が混ざらない。

## Existing Sample Context

現在の tutorial lane は次の流れで整っている。

- `sample01-04`: Data Class の基本、lookup、親子 table
- `sample05-10`: DBAccess の select、filter、CRUD、join、aggregate
- `sample11-17`: HTML、external DB import、OpenAPI、custom proxy、metadata bundle、auth、multi-output capstone
- `sample18`: 仮想 prompt から small app demo を作り、HTML / OpenAPI / web-lab page まで出す first instruction-driven demo

電子書籍 CMS は `sample18` の延長に置くのが自然。つまり、機能別 tutorial へ戻すのではなく、「実ユーザーがほしいもの」を sample 向け prompt に整理し、schema / DBAccess / outputs / demo page を固定する demo lane として伸ばす。

## Design Principles

- 1 sample = 1 主テーマにする。
- ユーザーは DB 設計を知らない前提にする。ユーザーが分かる入力は JSON とし、AI がその JSON を読み、裏側で DB structure / DBAccess / Source Output 設計へ変換する見立てにそろえる。
- headless CMS の最終形をいきなり作らず、JSON-first content model、content publishing、public API、workflow、media metadata、public reader、authenticated editor API、capstone の順で積む。
- 目的は production-ready CMS ではなく、Mtool の sample として「JSON で業務イメージを書く -> AI が DB / API 設計へ変換する -> Mtool が runtime artifact を生成する」流れを見せることに置く。
- 本運用に必要な周辺機能は大胆に削る。削ったものは `Out of Scope` / `Feedback Notes` / `Production Notes` として README や計画書に明記し、sample の未完成ではなく意図した境界として扱う。
- `reference/` は actual generated output のみ置く。
- `original-codes/` は runtime input に使わない。
- `sample19` までは MySQL / MariaDB config store と SQLite config store の両対応 tutorial として維持する。`sample20+` は ebook CMS の実運用寄り demo として MySQL / MariaDB runtime を正本にし、SQLite config store profile を必須 acceptance にしない。
- EPUB は早い段階から扱う。ただし sample scope では EPUB を生成せず、同梱済み `.epub` fixture の asset metadata / download URL / reader 表示導線に絞る。
- 実ファイル upload / EPUB build engine / payment / DRM / full text search engine は最終 capstone でも out of scope にする。
- file body の扱いは最初は URL / path / checksum / metadata に限定し、blob runtime を sample の主題にしない。

## Sample Scope Policy

ebook CMS lane は、Mtool の生成・接続・説明価値を見せるための sample であり、本運用 CMS の完全再現を目指さない。

優先するもの:

- JSON-first の入力 story
- AI が解釈した normalized DB / API 設計
- Mtool metadata / seed / Source Output の流れ
- DataClass / DBAccess / OpenAPI / HTML / auth proxy / metadata bundle の生成
- reader site と editor API の最小動作
- sample pack としての再現性

削ってよいもの:

- 本格 user / role / permission 管理
- audit log / revision history / rollback
- approval workflow の多段化
- file upload / image processing / EPUB build engine
- payment / purchase / subscription / DRM
- search engine / indexing / recommendation
- notification / email / webhook
- production monitoring / backup / migration strategy
- admin UI の完全 CRUD
- mobile app 本体

削ったものは各 sample README の `Out of Scope` に書く。最終 capstone では `Production Notes` として「本運用なら追加するもの」を短くまとめる。

## Display And EPUB Policy

ebook CMS lane の表示は、最初から「HTML reader だけ」には閉じない。早い段階から EPUB を意識する。

表示の層は 3 つに分ける。

- admin/editor preview:
  - 下書きや章本文を確認するための最小 HTML 表示。
  - 本格 editor UI は作らない。
- public web reader:
  - 公開済み book / chapter を HTML で読む導線。
  - `sample24` で top / list / detail / chapter view を見せる。
- EPUB-facing delivery:
  - `.epub` ファイル本体は生成しない。
  - repo に同梱したサンプル EPUB を、電子書籍サイトで表示・ダウンロードできる asset として扱う。
  - EPUB の URL / MIME type / checksum / file size / version / updated time を metadata として扱う。
  - 章順、nav label、spine order などは、HTML reader と同梱 EPUB の表示情報を揃えるための metadata として `sample22` から持たせる。

EPUB 対応の段階:

- `sample21`
  - catalog に `EpubStatus` / `PrimaryEpubUrl` のような最小 field を入れるか、後続で拡張できる余白を README に明記する。
- `sample22`
  - chapter workflow に `SpineOrder` / `NavLabel` / `EpubResourcePath` を追加し、HTML reader と EPUB nav の両方に使える章 metadata にする。
- `sample23`
  - `MediaAsset` / `BookMedia` で `application/epub+zip` を扱い、同梱 EPUB fixture の download metadata を管理する。
  - repo 同梱の自作 fixture `sample/_assets/epub/json-first-mini-book/json-first-mini-book.epub` を使い、URL / MIME type / file size / sha256 を seed できるようにする。
- `sample24`
  - public reader site に HTML reading と EPUB download link の両方を出す。
- `sample26`
  - capstone で web reader、app API、EPUB delivery metadata が同じ Book / Chapter model から説明できる状態にする。

EPUB fixture:

- path: `sample/_assets/epub/json-first-mini-book/json-first-mini-book.epub`
- source/provenance: `sample/_assets/epub/json-first-mini-book/source/`
  - 中身確認と著作権境界の説明用に置く。
  - sample runtime / README / manual flow では EPUB 作成手順として扱わない。
- media type: `application/epub+zip`
- size: `3125` bytes
- sha256: `6b52e37129d9f01097da7e9b598b0e06d60a5b8e3b4126870c799cdc6c1dd5ea`
- copyright boundary: この fixture の本文は sample 用に新規作成した短文のみで、第三者の書籍本文や public-domain 作品の転載は含めない。

## Proposed Samples

### sample19-json-first-content-model-demo

仮想 prompt:

```text
DB のことはよく分かりません。
でも、記事や本のデータを JSON で考えることならできます。
この JSON をもとに、管理しやすい DB 構造と API の形を考えてください。
```

入力 JSON の例:

```json
{
  "article": {
    "title": "はじめての電子書籍CMS",
    "slug": "first-ebook-cms",
    "status": "published",
    "publishedAt": "2026-06-19T09:00:00+09:00",
    "author": {
      "name": "Sample Editor"
    },
    "category": {
      "name": "Guide"
    },
    "body": "JSONから始めるCMSの例です。"
  }
}
```

狙い:

- DB を知らないユーザー向けに、JSON-first の入口を作る。
- AI が JSON を見て、`Article` / `Author` / `Category` のような normalized table 案へ変換する過程を sample の見立てとして固定する。
- 実装上は、AI が先に DB を考えて seed してもよい。ただし README / sample story では JSON が先、DB 設計が後という順に見せる。
- 後続 sample は、この JSON-first contract を発展させる。

主な outputs:

- `DATACLASS-PHP`
- `DBACCESS-PHP`
- 必要なら `OPENAPI-JSON`

acceptance:

- README に `User JSON`、`AI Interpreted Data Model`、`Generated DB / API Scope` を分けて書く。
- JSON の nested object が、DB table / relation に変換される考え方を説明できる。
- actual runtime は MySQL / MariaDB 上の normalized schema として再現できる。

### sample20-content-publishing-demo

仮想 prompt:

```text
小さなメディアサイトの記事管理を作りたいです。
記事、カテゴリ、公開状態、公開日時を管理して、
公開済み記事の一覧と詳細を HTML と API で見られるようにしてください。
```

狙い:

- generic CMS の最小形。
- `Article` / `Category` / `Author` 程度に絞る。
- publish state、published_at、slug の基本を扱う。
- `sample18` の CRUD app から、content publishing へ主語を移す。

主な outputs:

- `DATACLASS-PHP`
- `DBACCESS-PHP`
- `HTML-PAGE`
- `OPENAPI-JSON`

acceptance:

- published の list / detail API がある。
- draft は public list に出ない。
- generated HTML artifact と OpenAPI artifact で public surface が確認できる。
- dedicated web-lab app page は sample20 では削り、reader site / EPUB delivery は後続 sample に送る。

### sample21-ebook-catalog-api-demo

仮想 prompt:

```text
電子書籍ストアの本棚データを管理したいです。
本、著者、シリーズ、ジャンルを登録し、公開中の本をジャンルや著者で絞り込める API が欲しいです。
```

狙い:

- 電子書籍 domain への移行。
- `Book` / `Author` / `Series` / `Genre` / `BookAuthor` など、CMS より少し現実的な catalog schema を扱う。
- join read model と filter / sort / page を電子書籍文脈で見せる。
- EPUB は生成対象ではなく、catalog 側で表示・ダウンロード用の状態や URL を後続で足せる設計にする。

主な outputs:

- `DATACLASS-PHP`
- `DBACCESS-PHP`
- `OPENAPI-JSON`

acceptance:

- public catalog list API が genre / author / series / keyword-like title filter を持つ。
- book detail API が author / genre / series 表示用 fields を返す。
- Swagger viewer で catalog API surface を確認できる。
- README に、EPUB 生成は扱わず catalog model は EPUB 表示・ダウンロード metadata に拡張できることを書く。

### sample22-ebook-chapter-workflow-demo

仮想 prompt:

```text
本ごとに章を管理したいです。
章には本文、並び順、下書き、レビュー中、公開済みの状態があり、
公開済みの章だけを読者向けに出したいです。
```

狙い:

- ebook CMS らしい chapter / manuscript 管理。
- parent-child table、sort order、status transition を扱う。
- public read と editor write の前段として、workflow data を固定する。
- 章 metadata は HTML reader と EPUB nav/spine の両方に使える形にする。

候補 schema:

- `Book`
- `BookChapter`
  - `BodyMarkdown`
  - `Status`
  - `SpineOrder`
  - `NavLabel`
  - `EpubResourcePath`
- `ChapterRevision` は入れるなら最小にする。初回は `BookChapter` に留めてもよい。

主な outputs:

- `DATACLASS-PHP`
- `DBACCESS-PHP`
- `OPENAPI-JSON`

acceptance:

- book detail から published chapter list を取れる。
- editor 用に chapter create / update / reorder / publish がある。
- draft chapter が public API に混ざらない。
- published chapter list が EPUB spine order としても説明できる。

### sample23-ebook-media-metadata-demo

仮想 prompt:

```text
電子書籍には EPUB ファイルや表紙画像などの配信用ファイルがあります。
ファイル本体の生成やアップロードは不要です。
同梱済み EPUB をダウンロード表示できるように、URL、MIME type、サイズ、checksum を管理してください。
```

狙い:

- file blob を直接扱わず、headless CMS でよくある media metadata を扱う。
- 同梱 EPUB fixture の URL、MIME type、file size、sha256、version metadata を電子書籍 catalog に接続する。
- 画像 upload や変換を sample scope に入れず、DegoDB が得意な metadata / API / HTML に寄せる。
- `application/epub+zip` の asset を早めに扱い、EPUB delivery の存在を sample として見せる。

候補 schema:

- `EbookMediaBook`
- `EbookMediaAsset`
- `EbookMediaBookAsset`
- `EbookMediaDelivery`

主な outputs:

- `DATACLASS-PHP`
- `DBACCESS-PHP`
- `OPENAPI-JSON`

acceptance:

- public delivery API に EPUB download metadata が出る。
- EPUB fixture の MIME type、file size、sha256 を seed に含める。
- draft cover placeholder は public delivery read model に出さない。
- EPUB generation / parsing / upload / blob storage は out of scope と明記する。

### sample24-ebook-public-reader-site-demo

仮想 prompt:

```text
公開中の電子書籍を読者が探して読めるサイトを作りたいです。
トップ、本一覧、本詳細、章本文ページを HTML と API で出し、EPUB がある本はダウンロード導線も出してください。
```

狙い:

- site と app のうち、まず public reader site 側を作る。
- `sample21-23` の概念を読み取り中心にまとめる。
- HTML page と OpenAPI の multi-output demo を、電子書籍の見た目で確認する。
- HTML reader と EPUB download metadata の両方を表示する。

主な outputs:

- `DATACLASS-PHP`
- `DBACCESS-PHP`
- `HTML-PAGE`
- `OPENAPI-JSON`

acceptance:

- HTML-PAGE artifact で top / book detail / chapter view / EPUB download link の見立てが確認できる。
- book reader API に公開本、章一覧、章詳細、EPUB download metadata が出る。
- API は app client が使う public read endpoints として説明できる。
- production routing、検索、購入、EPUB renderer は out of scope と明記する。

### sample25-ebook-editor-auth-cms-demo

仮想 prompt:

```text
編集者だけが本や章を作成・更新・公開できる CMS API が欲しいです。
読者向け API は公開、編集 API は token がないと失敗するようにしてください。
```

狙い:

- headless CMS の管理側。
- `sample16` の authenticated proxy と `sample18` の write flow を ebook domain へ接続する。
- public read API と editor write API の境界を明確にする。

主な outputs:

- `DATACLASS-PHP`
- `DBACCESS-PHP`
- `OPENAPI-JSON`
- `AUTH-PROXY-SERVER`

acceptance:

- token missing / empty / wrong token / env missing が fail-closed。
- editor API で chapter preview / draft update / publish transition ができる。
- public reader API は `sample24` に残し、`sample25` は editor auth boundary に集中する。
- full editor UI、user / role 管理、audit log、revision history、approval workflow、EPUB generation は out of scope と明記する。

### sample26-ebook-headless-cms-capstone

仮想 prompt:

```text
電子書籍サイトとスマホ app の両方から使える headless CMS の小さいデモが欲しいです。
編集者は本、著者、章、表紙を管理でき、読者は公開中の本と章をサイトや app API から読めるようにしてください。
```

狙い:

- 最終到達点。
- これまでの sample を統合し、site / app / editor API の分離を見せる。
- DegoDB から複数 outputs を publish して、headless CMS 的な使い方を説明する。

候補 schema:

- `Book`
- `Author`
- `Genre`
- `BookAuthor`
- `BookChapter`
- `MediaAsset`
- `BookMedia`
- `EditorUser` は本格 user 管理ではなく metadata / fixture に留める

主な outputs:

- `DATACLASS-PHP`
- `DBACCESS-PHP`
- `HTML-PAGE`
- `OPENAPI-JSON`
- `AUTH-PROXY-SERVER`
- `PROJECT-METADATA-BUNDLE`

acceptance:

- public reader site demo がある。
- public reader site に HTML reader と EPUB download metadata がある。
- public app API の OpenAPI がある。
- editor write API は token protected。
- project metadata bundle を export できる。
- HTTP smoke で reader page 表示、book detail 表示、editor API auth failure / success の代表 case を確認する。

## Implementation Order

1. `sample19-json-first-content-model-demo`
   - DB を知らないユーザーが JSON で見立てた content model を、AI が DB / API 設計へ変換する入口を固定する。
2. `sample20-content-publishing-demo`
   - generic CMS で publish state / slug / category を先に固定する。
3. `sample21-ebook-catalog-api-demo`
   - ebook domain の catalog API を作る。
4. `sample22-ebook-chapter-workflow-demo`
   - chapter と公開 workflow を加える。
5. `sample23-ebook-media-metadata-demo`
   - cover / thumbnail / sample file metadata を加える。
6. `sample24-ebook-public-reader-site-demo`
   - 読者向け site / app read API を形にする。
7. `sample25-ebook-editor-auth-cms-demo`
   - 編集者向け token protected write API を分ける。
8. `sample26-ebook-headless-cms-capstone`
   - site / app / editor API / metadata bundle を統合する。

## Runtime Compatibility Policy

`sample01-19` は、DegoDB の tutorial / compatibility lane として MySQL / MariaDB config store と SQLite config store profile の両方を確認する。`sample19` は ebook 本体ではなく JSON-first の入口なので、ここまでは両対応の範囲に含める。

`sample20+` の ebook CMS lane は、実運用想定の demo として MySQL / MariaDB runtime を正本にする。

- canonical test は `make sampleNN-pack-runtime-test` の MySQL / MariaDB profile に置く。
- `run-sqlite-config.sh` と `make sampleNN-pack-runtime-test-sqlite` は必須にしない。
- SQLite config store でたまたま動くことは妨げないが、acceptance / release gate には含めない。
- ebook CMS lane で SQLite 差分が出た場合は、sample 側で吸収するより、必要に応じて別の compatibility issue / report として扱う。

この境界により、`sample20+` は ebook CMS domain、API、HTML demo、auth、metadata bundle の完成度を優先する。

## Lineage And Change Propagation

`sample19-25` は、設計上は前段 sample をベースに積み上げる。

- `sample20` は `sample19` の JSON-first content model を generic CMS へ具体化する。
- `sample21` は `sample20` の content publishing model を ebook catalog へ置き換える。
- `sample22` は `sample21` の `Book` を親にして chapter workflow を足す。
- `sample23` は `sample22` の `Book` に media metadata を足す。
- `sample24` は `sample21-23` の public read 側を reader site / app API としてまとめる。
- `sample25` は `sample24` の public read 側に対して、editor write API と auth boundary を足す。
- `sample26` は `sample19-25` の到達点を統合する。

ただし runtime pack としては、それぞれ独立して起動・検証できる状態を維持する。後続 sample は前段 sample の実行結果を runtime input として参照しない。

前段 sample に修正が入った場合は、変更の種類で伝播範囲を決める。

- domain model の修正:
  - 例: `Book` の公開状態、slug、author relation、chapter order の持ち方。
  - 後続 sample の schema / DBAccess / README / reference output へ反映する。
- public contract の修正:
  - 例: public list/detail API の field 名、draft を返さない条件、OpenAPI path の考え方。
  - 後続 sample と capstone へ反映する。
- runtime / generator bug fix:
  - actual generated output が変わる場合は、該当する後続 sample の `reference/` も更新する。
- sample 固有の見た目や fixture の修正:
  - 例: demo page の文言、seed data のタイトル、表示用の説明文。
  - 後続 sample に意味がなければ伝播しない。

この lane は「過去 sample を歴史的 snapshot として固定する」より、「同じ product idea が段階的に育つ教材」として扱う。そのため、前段の設計判断を変えた場合は、後続 sample も原則として同じ判断に合わせる。

## Cut Lines

### Recommended Cut

8 本すべて作る。各 sample が 1-2 個の新概念だけを足すので、途中で generator / runtime の不足が見つかっても原因を切り分けやすい。

### Minimum Cut

4 本に圧縮する。

- `sample19-json-first-content-model-demo`
- `sample21-ebook-catalog-api-demo`
- `sample24-ebook-public-reader-site-demo`
- `sample26-ebook-headless-cms-capstone`

この場合、generic CMS、chapter workflow、media metadata、auth editor API が capstone に入り、最終 sample が重くなる。

## Risks And Watch Points

- JSON-first の見せ方が「JSON をそのまま DB に保存する」話に見えないよう、AI が normalized DB / API contract へ解釈する流れを明確にする。
- OpenAPI の read / write schema が電子書籍 domain で読みやすく出るか。
- HTML module を curated にする範囲が増えすぎないか。
- media を file upload と誤解されないよう、metadata sample として明確に切る必要がある。
- auth を user / role management sample に広げすぎない。
- capstone の schema が大きくなりすぎる場合は `BookReview`、`Purchase`、`Subscription`、`DRM`、`EPUB build` を明確に out of scope にする。

## Roadmap Promotion

この計画は独立 report として置く。採用後に次を更新する。

- `docs/sample-tutorial-roadmap.md`
- `sample/tutorials/README.md`
- `sample/README.md`
- 各 sample の `README.md`
- `Makefile` の `sampleNN-pack-runtime-test`
- 必要な `tests/Integration/SampleNN...Test.php`

## Done Definition

- `sample26-ebook-headless-cms-capstone` で、JSON-first の入力から、電子書籍 site と app が使う public read API、編集者向け authenticated write API、reader site HTML、metadata bundle export が 1 project から説明できる。
- 各 sample が fresh runtime で再現でき、canonical `make sampleNN-pack-runtime-test` が通る。
- 最終 capstone は HTTP smoke で reader page と editor auth boundary を確認できる。
