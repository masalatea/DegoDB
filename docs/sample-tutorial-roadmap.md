# Sample Tutorial Roadmap

English companion:
This roadmap defines the user-facing tutorial lane under `sample/tutorials/`. It explains the learning order from `sample01` through `sample10`, the design principles behind the packs, and the acceptance criteria for each stage.

## 目的

- user-facing tutorial sample を `sample/tutorials/` に固定し、`sample01-*` から simple-to-complex に並べる。
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
| `sample01-simple-table-runtime` | current | 最初の end-to-end | 1 table (`Article`) | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample01-pack-runtime-test` |
| `sample02-dataclass-nullable-default-status` | current | nullable / default / status 付き Data Class | 1 table | `DATACLASS-PHP` | `make sample02-pack-runtime-test` |
| `sample03-dataclass-lookup-and-helper` | current | lookup / caption 向き Data Class | 2 tables | `DATACLASS-PHP` | `make sample03-pack-runtime-test` |
| `sample04-dataclass-parent-child-basic` | current | 親子 2 table の Data Class | 2 tables + FK | `DATACLASS-PHP` | `make sample04-pack-runtime-test` |
| `sample05-dbaccess-select-basic` | current | single-table select | 1 table + 1 db access class + 1 function | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample05-pack-runtime-test` |
| `sample06-dbaccess-filter-sort-page` | current | filter / sort / pagination | 1 table + select metadata | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample06-pack-runtime-test` |
| `sample07-dbaccess-crud-basic` | current | insert / update / delete | 1 table + write metadata | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample07-pack-runtime-test` |
| `sample08-dbaccess-join-read-model` | current | join read model | 2 live tables + 1 read model table + join metadata | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample08-pack-runtime-test` |
| `sample09-dbaccess-aggregate-report` | current | aggregate / report | 2 live tables + 1 report model table + count/sum/group/having metadata | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample09-pack-runtime-test` |
| `sample10-dbaccess-mini-crud-flow` | current | tutorial capstone | 1 table で list/detail/create/update/delete をまとめる最小 flow | `DATACLASS-PHP`, `DBACCESS-PHP` | `make sample10-pack-runtime-test` |

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
  - `TaskStatus` / `TaskPriority` の 2 lookup table で、複数 Data Class の sync と naming を確認する current tutorial とした。
  - この sample でいう `helper` は generated Data Class 内の独自メソッドではなく、lookup/caption を後段の formatter / service / custom layer へ逃がす前提を指す。
- `sample04-dataclass-parent-child-basic`
  - `Post` / `PostComment` の 2 table と FK を import し、親子 schema を持つ複数 Data Class の sync と output を確認する current tutorial とした。
  - current の Data Class sync は FK から relation metadata を自動補完しないため、`PostComment.PostId` は scalar field として同期される。
  - 「1 table ではないが、まだ DB Access 設計には入らない」境界をここに置く。

### Phase 2. DB Access lane

- `sample05-dbaccess-select-basic`
  - `Notice` 1 table と `GetNoticeList` 1 function だけを使い、`da` / `dafunc` と generated `DBACCESS-PHP` の最小対応を見る current tutorial とする。
  - where / paging / user-supplied sort はまだ入れず、manual `project_db_access_function_select_target_fields` と fixed `sort_order_columns` だけで DB Access 出力の入口を固定する。
- `sample06-dbaccess-filter-sort-page`
  - `Announcement` 1 table に `Status` filter 1 本と `limit` argument を足し、一覧画面で最初に必要になる where / order / page size を current tutorial として固定する。
  - sort は `Announcement.PublishedAt desc, Announcement.Id desc` の fixed metadata とし、user-supplied order や複合条件は次段へ送る。
- `sample07-dbaccess-crud-basic`
  - `TodoItem` 1 table と `InsertTodoItem` / `UpdateTodoItem` / `DeleteTodoItem` 3 function だけを使い、write metadata の最小構成を current tutorial として固定する。
  - `project_db_access_function_insert_target_fields` と `project_db_access_function_update_target_fields` は `Title` / `Status` / `Body` に限定し、`project_db_access_function_update_delete_wheres` は `Id = argument` 1 本だけに絞る。
- `sample08-dbaccess-join-read-model`
  - `BlogPost` / `BlogAuthor` / `BlogPostAuthorSummary` の 3 table を import し、join した row を read model DTO へ詰める最小 tutorial を current 化した。
  - `project_db_access_function_select_wheres` では `BlogPost.BlogAuthorId = BlogAuthor.Id` の `anotherfield` join 1 本と、`BlogPost.Status = 'published'`、`BlogAuthor.IsActive = 1` の fixed condition 2 本だけに絞る。
- `sample09-dbaccess-aggregate-report`
  - `SalesRecord` / `SalesCategory` / `SalesCategoryReport` の 3 table を import し、join + group by + count + sum + having を 1 function にまとめた current tutorial とした。
  - `project_db_access_function_select_target_fields` では `SalesRecord.SalesCategoryId` と `SalesCategory.Name` を `group_by_target=1` にし、`count(SalesRecord.Id)` と `sum(SalesRecord.Amount)` を report field として出す。
  - `project_db_access_function_select_havings` は `count >= 2` と `sum >= 100` の fixed raw 条件 2 本だけに絞り、aggregate report の最小構成に固定する。
- `sample10-dbaccess-mini-crud-flow`
  - `SupportTicket` 1 table と `GetSupportTicketList` / `GetSupportTicket` / `InsertSupportTicket` / `UpdateSupportTicket` / `DeleteSupportTicket` の 5 function を 1 class にまとめ、small but real な CRUD flow を current tutorial として固定した。
  - list は `Status` argument filter + `limit`、detail は `Id` where 1 本、write は `Title` / `Status` / `AssignedTo` / `Body` / `UpdatedAt` を対象にする。

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
- tutorial lane は `sample10` まで current とした。proxy / HTML / LanguageResource tutorial を `sample11+` として増やすかを次段で再判断する。
