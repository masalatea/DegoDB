# Sample Tutorial Roadmap / sample 学習導線

English companion:
This roadmap defines the user-facing tutorial lane under `sample/tutorials/`. It explains the learning order from `sample01` through `sample26`, the design principles behind the packs, and the acceptance criteria for each stage.

## 目的

- user-facing tutorial sample を `sample/tutorials/` に固定し、`sample01-*` から simple-to-complex に並べる。
- instruction-driven demo sample も `sample/tutorials/` の後続番号に置き、AI が整理した prompt から作った動作 demo として扱う。
- `DB 構造 -> import -> Data Class -> DB Access -> Source Output` という main flow に沿って、何をどの sample で学ぶかを明確にする。
- rewrite / migration guard 用の `sample/internal-patterns/` を tutorial lane と混ぜず、役割を分離する。

## 前提

- tutorial lane の正本は `sample/tutorials/` とする。
- internal の complex/new form guard は `sample/internal-patterns/` に置き、tutorial 番号へ戻さない。
- tutorial sample は file-based sample ではなく runtime pack を基本とする。
- 各 pack は `compose.yaml`、`run.sh`、`seed/`、`README.md`、必要なら `reference/` を持つ。
- canonical test target は `make sampleNN-pack-runtime-test` とする。
- `original-codes/` は host-side reference only とし、tutorial pack の runtime input には使わない。

## tutorial 設計原則

- 1 pack = 1 主テーマを守る。
- simple な schema / metadata から始め、1 つ前の sample に 1-2 段だけ概念を足す。
- `sample01-04` は Data Class 理解を優先し、`sample05-10` で DB Access を段階的に足す。
- 各 sample は `README` だけで「何を seed し、何を import / sync / output し、何を検証するか」が読める状態にする。
- reference は actual output だけを置き、説明用の疑似生成物は置かない。

## catalog

| pack | status | 主テーマ | schema / metadata の範囲 | 主な output | canonical test |
| --- | --- | --- | --- | --- | --- |
| `sample01-simple-table-runtime` | current | 最初の end-to-end | physical `article` / generated `Article` 1 table | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample01-pack-runtime-test` |
| `sample02-dataclass-nullable-default-status` | current | nullable / default / status 付き Data Class | 1 table | `DATACLASS-PHP` | `make sample02-pack-runtime-test` |
| `sample03-dataclass-lookup-and-helper` | current | lookup / caption 向き Data Class | 2 tables | `DATACLASS-PHP` | `make sample03-pack-runtime-test` |
| `sample04-dataclass-parent-child-basic` | current | 親子 2 table の Data Class | 2 tables + FK | `DATACLASS-PHP` | `make sample04-pack-runtime-test` |
| `sample05-dbaccess-select-basic` | current | single-table select | 1 table + 1 db access class + 1 function | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample05-pack-runtime-test` |
| `sample06-dbaccess-filter-sort-page` | current | filter / sort / pagination | 1 table + select metadata | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample06-pack-runtime-test` |
| `sample07-dbaccess-crud-basic` | current | insert / update / delete | 1 table + write metadata | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample07-pack-runtime-test` |
| `sample08-dbaccess-join-read-model` | current | join read model | 2 live tables + 1 read model table + join metadata | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample08-pack-runtime-test` |
| `sample09-dbaccess-aggregate-report` | current | aggregate / report | 2 live tables + 1 report model table + count/sum/group/having metadata | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample09-pack-runtime-test` |
| `sample10-dbaccess-mini-crud-flow` | current | DBAccess tutorial capstone | 1 table で list/detail/create/update/delete をまとめる最小 flow | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample10-pack-runtime-test` |
| `sample11-html-template-output` | current | HTML Source Output | curated HTML module と HTML template metadata の最小 publish flow | `HTML-PAGE` | `make sample11-pack-runtime-test` |
| `sample12-external-db-source-import` | current | external DB source import | named external source から table import / sync / publish へ進む最小 flow | `DATACLASS-PHP` | `make sample12-pack-runtime-test` |
| `sample13-openapi-api-surface` | current | OpenAPI / API surface | single-function proxy target metadata から `openapi.json` を publish する最小 flow | `OPENAPI-JSON` | `make sample13-pack-runtime-test` |
| `sample14-custom-proxy-runtime` | current | custom proxy runtime | custom proxy metadata から PHP proxy server artifact を publish する最小 flow | `CUSTOM-PROXY-SERVER` | `make sample14-pack-runtime-test` |
| `sample15-project-metadata-export-import` | current | project metadata export / import | project-core metadata bundle を export し、preview / apply で復元する最小 flow | `PROJECT-METADATA-BUNDLE` | `make sample15-pack-runtime-test` |
| `sample16-authenticated-proxy` | current | authenticated proxy | static bearer authenticated single proxy server と fail-closed behavior を確認する最小 flow | `AUTH-PROXY-SERVER` | `make sample16-pack-runtime-test` |
| `sample17-multi-output-project` | current | multi-output capstone | 1 project から DataClass / DBAccess / HTML / OpenAPI を publish する総合 flow | `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON` | `make sample17-pack-runtime-test` |
| `sample18-mini-task-board-demo` | current | instruction-driven task board demo | AI が整理した仮想 prompt から TaskCard demo を作り、CRUD DBAccess と HTML / OpenAPI を publish する。`web-lab` に sample page も持つ | `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON` | `make sample18-pack-runtime-test` / `make sample18-http-runtime-smoke` |
| `sample19-json-first-content-model-demo` | current | JSON-first content model demo | DB を知らないユーザーが書いた JSON を、AI が normalized DB / DBAccess metadata へ解釈する入口 sample。`sample20+` の ebook CMS lane に進む前の bridge として、MySQL / MariaDB と SQLite config store profile の両方を検証する | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample19-pack-runtime-test` / `make sample19-pack-runtime-test-sqlite` |
| `sample20-content-publishing-demo` | current | content publishing demo | `sample19` の JSON-first content model を土台に、public article list/detail、HTML page、OpenAPI artifact まで publish する最小 ebook/content lane。runtime profile は MySQL / MariaDB canonical のみに絞る | `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON` | `make sample20-pack-runtime-test` |
| `sample21-ebook-catalog-api-demo` | current | ebook catalog API demo | content publishing model を ebook catalog に置き換え、Book / Author / Series / Genre と public catalog list/detail OpenAPI surface を publish する。EPUB は status / URL metadata に留める | `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON` | `make sample21-pack-runtime-test` |
| `sample22-ebook-chapter-workflow-demo` | current | ebook chapter workflow demo | ebook book に chapter workflow を足し、published chapter list/detail と editor create/update/reorder/publish API surface を publish する。EPUB は spine/nav metadata に留める | `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON` | `make sample22-pack-runtime-test` |
| `sample23-ebook-media-metadata-demo` | current | ebook media metadata demo | 同梱 EPUB fixture の URL / MIME type / file size / checksum を media delivery metadata として publish する。EPUB 生成・解析・upload は扱わない | `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON` | `make sample23-pack-runtime-test` |
| `sample24-ebook-public-reader-site-demo` | current | ebook public reader site demo | 公開本 / 章 / EPUB delivery metadata から HTML reader page と app 向け OpenAPI surface を publish する。EPUB は download link のみ扱う | `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON` | `make sample24-pack-runtime-test` |
| `sample25-ebook-editor-auth-cms-demo` | current | ebook editor auth CMS demo | 編集者向け chapter preview / draft update / publish API を legacy-compatible ProjectToken protected proxy として publish する。current static bearer baseline / full editor UI / user-role 管理 / revision history は扱わない | `DATACLASS-PHP`, `DBACCESS-PHP`, `OPENAPI-JSON`, `AUTH-PROXY-SERVER` | `make sample25-pack-runtime-test` |
| `sample26-ebook-headless-cms-capstone` | current | ebook headless CMS capstone | public reader HTML、app OpenAPI、legacy-compatible ProjectToken editor proxy、project metadata bundle を 1 project から publish する | `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON`, `AUTH-PROXY-SERVER`, `PROJECT-METADATA-BUNDLE` | `make sample26-pack-runtime-test` |
| `sample33-sqlite-to-mysql-promotion` | current | SQLite-to-MySQL promotion artifact chain | parent/record SQLite fixtureからmanifest、target schema、export、import checkpoint、verification、cutover、operator、rehearsal packageを接続する | `PROMOTION-REHEARSAL-PACKAGE` | `tests/Integration/Sample33SqliteMysqlPromotionTest.php` |
| `sample34-sqlite-to-firebird-promotion` | current | SQLite-to-Firebird promotion path | canonical snapshot + SQLite inspection fixtureからFirebird local durable promotion contract、target schema plan、SQLite export、import rehearsalを生成し、opt-in Docker smokeでlive importとbackup-restoreまで確認する。local profile switch gateは明示的に残す | `SQLITE-FIREBIRD-PROMOTION-CONTRACT`, `FIREBIRD-PROMOTION-SMOKE` | `tests/Integration/Sample34SqliteFirebirdPromotionTest.php`, `make sample34-firebird-backup-restore-smoke-docker` |

## phase 分け

### Phase 0. first touch

- `sample01-simple-table-runtime`
- 既存の current pack を tutorial lane の入口として維持する。
- user に最初に見せるのは、`live schema import -> data class sync -> source output generate` が 1 table で通る最短経路。

### Phase 1. Data Class lane

- `sample02-dataclass-nullable-default-status`
  - nullable / default / bool / status-like column を含む 1 table tutorial として current 化した。
  - DB Access には進まず、Data Class output の読み方を覚えることを優先する。
- `sample03-dataclass-lookup-and-helper`
  - physical `task_status` / `task_priority` と generated `TaskStatus` / `TaskPriority` の 2 lookup table で、複数 Data Class の sync と naming を確認する current tutorial とした。
  - この sample でいう `helper` は generated Data Class 内の独自メソッドではなく、lookup/caption を後段の formatter / service / custom layer へ逃がす前提を指す。
- `sample04-dataclass-parent-child-basic`
  - physical `post` / `post_comment` と generated `Post` / `PostComment` の 2 table と FK を import し、親子 schema を持つ複数 Data Class の sync と output を確認する current tutorial とした。
  - current の Data Class sync は FK から relation metadata を自動補完しないため、physical `post_comment.post_id` は generated `PostComment.postId` scalar field として同期される。
  - 「1 table ではないが、まだ DB Access 設計には入らない」境界をここに置く。

### Phase 2. DB Access lane

- `sample05-dbaccess-select-basic`
  - physical `notice` / generated `Notice` 1 table と `GetNoticeList` 1 function だけを使い、`da` / `dafunc` と generated `DBACCESS-PHP` の最小対応を見る current tutorial とする。
  - where / paging / user-supplied sort はまだ入れず、manual `project_db_access_function_select_target_fields` と fixed `sort_order_columns` だけで DB Access 出力の入口を固定する。
- `sample06-dbaccess-filter-sort-page`
  - physical `announcement` / generated `Announcement` 1 table に `Status` filter 1 本と `limit` argument を足し、一覧画面で最初に必要になる where / order / page size を current tutorial として固定する。
  - sort は physical `announcement.published_at desc, announcement.id desc` の fixed metadata とし、user-supplied order や複合条件は次段へ送る。
- `sample07-dbaccess-crud-basic`
  - physical `todo_item` / generated `TodoItem` 1 table と `InsertTodoItem` / `UpdateTodoItem` / `DeleteTodoItem` 3 function だけを使い、write metadata の最小構成を current tutorial として固定する。
  - `project_db_access_function_insert_target_fields` と `project_db_access_function_update_target_fields` は physical `title` / `status` / `body` に限定し、`project_db_access_function_update_delete_wheres` は physical `id = argument` 1 本だけに絞る。
- `sample08-dbaccess-join-read-model`
  - physical `blog_post` / `blog_author` / `blog_post_author_summary` と generated `BlogPost` / `BlogAuthor` / `BlogPostAuthorSummary` の 3 table を import し、join した row を read model DTO へ詰める最小 tutorial を current 化した。
  - `project_db_access_function_select_wheres` では physical `blog_post.blog_author_id = blog_author.id` の `anotherfield` join 1 本と、physical `blog_post.status = 'published'`、`blog_author.is_active = 1` の fixed condition 2 本だけに絞る。
- `sample09-dbaccess-aggregate-report`
  - physical `sales_record` / `sales_category` / `sales_category_report` と generated `SalesRecord` / `SalesCategory` / `SalesCategoryReport` の 3 table を import し、join + group by + count + sum + having を 1 function にまとめた current tutorial とした。
  - `project_db_access_function_select_target_fields` では physical `sales_record.sales_category_id` と `sales_category.name` を `group_by_target=1` にし、`count(sales_record.id)` と `sum(sales_record.amount)` を report field として出す。
  - `project_db_access_function_select_havings` は `count >= 2` と `sum >= 100` の fixed raw 条件 2 本だけに絞り、aggregate report の最小構成に固定する。
- `sample10-dbaccess-mini-crud-flow`
  - physical `support_ticket` / generated `SupportTicket` 1 table と `GetSupportTicketList` / `GetSupportTicket` / `InsertSupportTicket` / `UpdateSupportTicket` / `DeleteSupportTicket` の 5 function を 1 class にまとめ、small but real な CRUD flow を current tutorial として固定した。
  - list は physical `status` argument filter + `limit`、detail は physical `id` where 1 本、write は physical `title` / `status` / `assigned_to` / `body` / `updated_at` を対象にする。

### Phase 3. Source Output lane

- `sample11-html-template-output`
  - `SAMPLE11/HTML-PAGE` の curated HTML module tree を `html-module-catalog` strategy で publish する current tutorial とした。
  - `project_html_definitions` / `project_html_parameters` と `html_templates` / `html_template_parameters` の最小 metadata を seed し、HTML template 系 Source Output の入口を DBAccess lane と分けて固定する。
  - `LanguageResource` / i18n は tool scope から外れたため tutorial lane へ追加しない。
- `sample12-external-db-source-import`
  - `database_sources.source_key=sample12_lab` を seed し、`db-lab` 側の physical `external_article` / generated `ExternalArticle` table を `named-live-schema:sample12_lab` から import する current tutorial とした。
  - canonical table / DataClass metadata は seed せず、external source import と sync で作る。
  - first slice は `DATACLASS-PHP` publish までに絞り、OpenAPI / proxy runtime は後続 sample へ送る。
- `sample13-openapi-api-surface`
  - `ApiTask.GetApiTaskList` / `GetApiTask` を `OPENAPI-JSON` の single-function proxy target にし、`openapi-json` strategy で `openapi.json` を publish する current tutorial とした。
  - actual proxy runtime execution は扱わず、OpenAPI artifact と authenticated Swagger viewer の入口に絞る。
- `sample14-custom-proxy-runtime`
  - `CATALOG-SUMMARY` custom proxy を seed し、`dbtable.GetdbtableList` / `project_source_output.GetProjectSourceOutputList` の 2 step を `CUSTOM-PROXY-SERVER` に bind する current tutorial とした。
  - full generated bundle は大きいため、reference は actual output から主要 entrypoint / handler / build plan を選抜して固定する。
- `sample15-project-metadata-export-import`
  - physical `bundle_note` / generated `BundleNote` table を import / sync した project-core metadata を `PROJECT-METADATA-BUNDLE` として export する current tutorial とした。
  - import は同じ project への preview / apply に絞り、bundle reference と runtime export の一致、復元後 metadata 件数を固定する。
  - `database_sources` sidecar / secret file と別 project key への rename import は後続 scope へ送る。
- `sample16-authenticated-proxy`
  - `AuthTask.GetAuthTask` を `static-bearer` auth の `AUTH-PROXY-SERVER` に bind する current tutorial とした。
  - generated single proxy server output の reference compare に加え、Authorization missing / malformed / wrong / env missing が fail-closed になり、matching bearer token だけが通ることを検証する。
  - `GetFunc` / `ProjectTokenOrGetFunc` / `LoginCookieToken` は後続 scope へ送る。

### Phase 4. Capstone

- `sample17-multi-output-project`
  - physical `capstone_task` / generated `CapstoneTask` 1 table と `GetCapstoneTaskList` / `GetCapstoneTask` 2 function を使い、同じ `SAMPLE17` project から複数 Source Output を publish する final capstone とした。
  - outputs は `DATACLASS-PHP`、`DBACCESS-PHP`、`HTML-PAGE`、`OPENAPI-JSON` に絞り、project metadata bundle / auth / HTTP browser smoke は既存 sample へ分ける。
  - checker は import / sync 後に 4 output を publish し、actual generated reference tree と一致することを検証する。

### Phase 5. Instruction-driven demos

- `sample18-mini-task-board-demo`
  - 生の会話ログではなく、AI が sample 向けに整理した仮想 prompt から作る first demo とした。
  - physical `task_card` / generated `TaskCard` 1 table と `GetTaskCardList` / `GetTaskCard` / `InsertTaskCard` / `UpdateTaskCard` / `CompleteTaskCard` の 5 function を使う。
  - outputs は `DATACLASS-PHP`、`DBACCESS-PHP`、`HTML-PAGE`、`OPENAPI-JSON` とし、OpenAPI は read / write の 5 proxy path を含める。
  - `web-lab` の `/samples/sample18-task-board` で、起動後に簡単な task board UI を触れるようにする。
  - checker は import / sync 後に 4 output を publish し、actual generated reference tree と一致することを検証する。
  - HTTP smoke は login、page 表示、task 作成、task 編集まで確認する。
- `sample19-json-first-content-model-demo`
  - DB 設計を知らないユーザーが JSON で content model を見立てる入口 sample とした。
  - User JSON の nested `author` / `category` を、AI が physical `json_author` / `json_category` / `article_json_model` / `article_public_summary` と generated `JsonAuthor` / `JsonCategory` / `ArticleJsonModel` / `ArticlePublicSummary` に解釈する story にする。
  - `ArticleJsonModel.GetPublishedArticlePublicSummaryList` で published article の public summary を join read model として出す。
  - outputs は `DATACLASS-PHP`、`DBACCESS-PHP` に絞り、OpenAPI / HTML / editor workflow は `sample20+` へ送る。
  - checker は import / sync 後に 2 output を publish し、actual generated reference tree と一致することを検証する。
  - `sample19` は ebook 本体ではなく JSON-first entrance なので、MySQL / MariaDB と SQLite config store profile の両方を維持する。
- `sample20-content-publishing-demo`
  - `sample19` の JSON-first entrance を受けて、public に出す content publishing 面だけを最小化して作る first ebook/content lane とした。
  - physical `content_article` / generated `ContentArticle` 1 table と `GetPublishedContentArticleList` / `GetPublishedContentArticle` の 2 function だけを使う。
  - draft article は seed するが public DBAccess / OpenAPI には出さず、publish surface から除外されることを sample story に含める。
  - outputs は `DATACLASS-PHP`、`DBACCESS-PHP`、`HTML-PAGE`、`OPENAPI-JSON` とする。
  - EPUB は sample assets として表示/download 側で扱う方針に留め、sample20 では EPUB 生成も EPUB import も扱わない。
  - 本運用なら必要な editor workflow、権限、検索、versioning、asset lifecycle は Mtool sample の目的から外し、README で scope cut として説明する。
  - `sample20+` は ebook / content lane なので、runtime profile は MySQL / MariaDB canonical のみに絞る。
- `sample21-ebook-catalog-api-demo`
  - `sample20` の generic content publishing model を ebook catalog domain へ置き換える first catalog sample とした。
  - physical `ebook_series` / `ebook_author` / `ebook_genre` / `ebook_book` / link tables / `ebook_catalog_item` と generated `EbookSeries` / `EbookAuthor` / `EbookGenre` / `EbookBook` / `EbookCatalogItem` を使い、少し現実的な catalog schema を見せる。
  - `EbookCatalogItem.GetPublicEbookCatalogList` / `GetPublicEbookBook` で public catalog list/detail の OpenAPI surface を作る。
  - list は author slug、genre slug、series slug、title `LIKE`、limit を argument として持つ。optional filter UI はまだ扱わない。
  - EPUB は `EpubStatus` / `PrimaryEpubUrl` の metadata だけを持たせ、生成・import・reader 表示は後続 sample へ送る。
  - outputs は `DATACLASS-PHP`、`DBACCESS-PHP`、`OPENAPI-JSON` とする。
- `sample22-ebook-chapter-workflow-demo`
  - `sample21` の ebook catalog を受けて、book の子として chapter workflow を足す sample とした。
  - physical `ebook_workflow_book` / `ebook_workflow_chapter` / `ebook_workflow_published_chapter` と generated `EbookWorkflowBook` / `EbookWorkflowChapter` / `EbookWorkflowPublishedChapter` を使い、draft chapter が public API に出ない境界を固定する。
  - public read は `GetPublishedEbookWorkflowChapterList` / `GetPublishedEbookWorkflowChapter`、editor write は `InsertEbookWorkflowChapter` / `UpdateEbookWorkflowChapterDraft` / `UpdateEbookWorkflowChapterOrder` / `PublishEbookWorkflowChapter` に絞る。
  - `SpineOrder` / `NavLabel` / `EpubResourcePath` は HTML reader と EPUB nav/spine の両方に使える metadata として扱う。
  - editor UI、revision history、EPUB generation / parsing は後続または out of scope に送る。
- `sample23-ebook-media-metadata-demo`
  - `sample22` の EPUB-facing metadata を受けて、同梱 EPUB fixture の delivery metadata を DB / API に載せる sample とした。
  - physical `ebook_media_book` / `ebook_media_asset` / `ebook_media_book_asset` / `ebook_media_delivery` と generated `EbookMediaBook` / `EbookMediaAsset` / `EbookMediaBookAsset` / `EbookMediaDelivery` を使い、asset 本体ではなく URL / MIME type / file size / sha256 / version を管理する。
  - public read は `GetPublicEbookMediaDeliveryList` / `GetPublicEbookMediaAsset`、editor write は `InsertEbookMediaAsset` / `UpdateEbookMediaAssetMetadata` に絞る。
  - EPUB generation / parsing / upload / blob storage / CDN lifecycle は out of scope に送る。
- `sample24-ebook-public-reader-site-demo`
  - `sample21-23` の読者向け概念をまとめ、公開本 / 章 / EPUB download metadata を reader site と app API の形で出す sample とした。
  - physical `ebook_reader_book` / `ebook_reader_chapter` / `ebook_reader_media_delivery` と generated `EbookReaderBook` / `EbookReaderChapter` / `EbookReaderMediaDelivery` を使い、draft book / chapter を public surface から除外する。
  - public read は `GetPublicEbookReaderBookList` / `GetPublicEbookReaderBook` / `GetPublicEbookReaderChapterList` / `GetPublicEbookReaderChapter` / `GetPublicEbookReaderMediaDeliveryList` に絞る。
  - HTML-PAGE は curated reader page artifact とし、production routing、EPUB renderer、search、purchase は out of scope に送る。
- `sample25-ebook-editor-auth-cms-demo`
  - `sample24` の public reader surface と対になる、編集者向けの authenticated CMS API sample とした。
  - physical `ebook_editor_book` / `ebook_editor_chapter` と generated `EbookEditorBook` / `EbookEditorChapter` を使い、chapter preview、draft update、publish の最小 API に絞る。
  - editor API は legacy-compatible `ProjectToken` protected `AUTH-PROXY-SERVER` として publish し、missing / empty / wrong / env missing token が fail-closed になることも検証する。
  - current generated runtime security baseline は `sample16` の static bearer authenticated proxy に置き、`sample25` は ebook CMS lane の compatibility sample として維持する。
  - full editor UI、user / role 管理、audit log、revision history、approval workflow、EPUB generation は out of scope に送る。
- `sample26-ebook-headless-cms-capstone`
  - `sample19-25` の到達点として、JSON-first ebook model から public site / app API / editor auth API / metadata bundle を 1 project で説明する capstone とした。
  - physical `ebook_cms_book` / `ebook_cms_chapter` と generated `EbookCmsBook` / `EbookCmsChapter` に author、cover、EPUB delivery metadata を寄せ、sample として読める compact schema に留める。
  - outputs は `DATACLASS-PHP`、`DBACCESS-PHP`、`HTML-PAGE`、`OPENAPI-JSON`、`AUTH-PROXY-SERVER` とし、checker が `PROJECT-METADATA-BUNDLE` export も検証する。
  - 本運用に必要な user / role 管理、audit log、revision history、approval workflow、upload、EPUB build、search、payment / DRM は Production Notes へ送る。

## 各 sample の受け入れ条件

- `README.md` に次を明記する。
  - 役割
  - seed される row
  - import / sync / output の最小実行手順
  - 生成物の置き場
- `run.sh up` と `run.sh apply-seed` で fresh runtime を再現できる。
- `reference/` は actual output compare 用に最小限だけ置く。
- `tests/Integration/SampleN...Test.php` を追加し、`make sampleNN-pack-runtime-test` から呼べる。
- `make test` に入れるかどうかは、作成後に suite 時間と coverage を見て判断する。

## 実装順

1. `sample02-dataclass-nullable-default-status`
2. `sample03-dataclass-lookup-and-helper`
3. `sample04-dataclass-parent-child-basic`
4. `sample05-dbaccess-select-basic`
5. `sample06-dbaccess-filter-sort-page`
6. `sample07-dbaccess-crud-basic`
7. `sample08-dbaccess-join-read-model`
8. `sample09-dbaccess-aggregate-report`
9. `sample10-dbaccess-mini-crud-flow`

## 補足

- `pattern01-14` は tutorial の代替ではなく、generator / migration contract を守る internal sample として扱う。
- representative runtime project は引き続き `sample/legacy-projects/` に置き、tutorial numbering に混ぜない。
- tutorial lane は `sample26` まで current とした。`sample20+` は ebook CMS tutorial / demo として、MySQL / MariaDB canonical profile に絞って追加する。
- `sample19-26` は runtime pack、reference output、checker、study guide、比較表、tutorial 導線まで含めて、サンプル教材として完了扱いにする。今後この lane を触る場合は、製品機能追加ではなく、読みやすさ、README / study guide 導線、scope cut 表現、sample 粒度揃えを基準にする。
- `sample19-26` は当初の保守的な実装見立てより早く作れた。ざっくり 70-85% 程度早く、当初見立ての 15-30% 程度の時間で到達できたという評価。理由は production CMS ではなく Mtool sample として scope を切り、generated artifact を Mtool に任せ、seed / checker / reference 固定へ作業を集中できたため。詳細は `docs/reports/2026/2026-0619-ebook-headless-cms-sample-plan.md` の Implementation Retrospective を参照する。
- Mtool の基本方針は、複雑なものを core flow で全部扱うことではなく、正規化・単純化できる大部分のケースを最適化し、そこから外れるものを例外として扱うことに置く。`sample19-26` が早く作れたことは、この方針に沿った結果として記録する。
- 一般的な ORM / OR mapper 類似ツールとの客観比較は、最新版を `docs/mtool-positioning.md` にまとめる。今回時点の履歴は同 report の `Mtool Compared With ORM-like Tools` に残す。Mtool は application code 内の ORM というより、metadata-driven output generator / tutorial sample builder として評価する。
- `LanguageResource` / i18n tutorial は tool scope 外なので追加しない。
