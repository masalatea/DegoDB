# 2026-0511 Self-Host Import Loop Plan

## Status

- status: `DONE`
- status updated at: `2026-05-27`
- completion scope:
  - self-host import / sync の first slice (`dbtable` / `dbtablecolumns` -> `dataclass` / `dataclassfields`) は完了済み
- follow-up ownership:
  - broader upstream/downstream absorption は `2026-0511-gradual-legacy-absorption-plan.md` に持たせる
  - broad rewrite current wave に関係する残実装は `2026-0527-broad-rewrite-temporary-closure-plan.md` で管理する
- note:
  - この文書は current execution plan ではない

## 目的

- Mtool 自身の DB 設計を新実装へ import し、その canonical metadata を使って次段の生成へ進める。
- 具体的には `dbtable` / `dbtablecolumns` -> `dataclass` / `dataclassfields` の upstream ループを閉じる。
- `README.md` と `docs/overview.md` に書いている `DB 構造 -> import -> Data Class -> DB Access -> Source Output` を、実装でも成立させる。

## 現状認識

- 2026-05-11 時点で upstream first slice は実装済みである。
  - `dbtable` / `dbtablecolumns` / `dataclass` / `dataclassfields` を `config_app` に追加した。
  - `/projects/{project_key}/tables/import` は preview / apply を持ち、`MTOOL` では live schema から canonical import を実行できる。
  - `/projects/{project_key}/data-classes/sync` は preview / apply を持ち、`dbtable.name == dataclass.name` の first-pass sync を実行できる。
  - `MTOOL` では import `20 tables / 233 columns`、sync `20 classes / 233 fields` を確認した。
- downstream half も継続して動いている。
  - `project_db_access_classes` / `project_db_access_functions` は canonical metadata として動作している。
  - `RUNTIME-DBCLASSES` は `canonical-dbaccess-partial-sql-regenerated` まで進んでいる。
- したがって、Mtool の self-host loop は first slice として一度閉じられた。次段は connector 拡張、UI 認知、generator 接続の強化である。

## Phase 1 完了条件

1. `config_app` に `dbtable` / `dbtablecolumns` / `dataclass` / `dataclassfields` が追加される。
2. `/projects/{project_key}/tables/import` が preview だけでなく apply を持ち、table / column metadata を保存できる。
3. `/projects/{project_key}/data-classes/sync` が preview だけでなく apply を持ち、`dbtable` から `dataclass` / `dataclassfields` を保存できる。
4. MTOOL で import + sync を 1 回通し、件数と主要 route を確認できる。
5. `RUNTIME-DBCLASSES` の data-side supplement のうち、table / dataclass metadata 由来のものは可能な範囲で正式 canonical slice に置き換わる。

### Phase 1 判定

- 1 から 4 までは達成済み。
- 5 は部分達成であり、upstream slice は入ったが runtime generator 側の暫定 supplement をまだ正式 metadata 読み出しへ統合し切っていない。

## スコープ

- 対象
  - canonical upstream metadata schema の追加
  - import / sync repository と service の追加
  - `tables/import` と `data-classes/sync` の apply 実装
  - MTOOL 自身の first import / first sync
  - runtime generator が upstream canonical slice を参照する最初の接続
- 非対象
  - DB 設計そのものをこのツールで行うこと
  - 旧 editable region 方式への回帰
  - DTO への業務ロジック追加
  - app 全体の self-generated runtime 切り替え完了

## 実装方針

- upstream metadata table は self-hosting compatibility を優先し、既存の generated `data-*.php` / `dbaccess-*.php` が前提にしている table / column contract に合わせる。
  - `dbtable`
    - `ProjectPID`, `PID`, `name`
  - `dbtablecolumns`
    - `ProjectPID`, `dbtablePID`, `PID`, `name`, `datatype`, `IsNull`, `IsKey`, `IsDefault`, `Extra`, `ColumnListOrder`, `memo`
  - `dataclass`
    - `ProjectPID`, `PID`, `name`, `StoreBasePath`, `IsAutoload`, `InheritParentDataClassName`, `LastModifiedDT`
  - `dataclassfields`
    - `ProjectPID`, `dataclassPID`, `PID`, `name`, `datatype`, `FieldListOrder`, `RefDataClassName`, `RefDataClassFieldName`
- route / repository 境界では `project_key` を受け、DB 保存時に `ProjectPID` へ解決する。
- `original-codes/` は reference only とし、実装は `shared/` と migration に書き直す。
- 旧 helper 的な振る舞いは DTO に戻さず、service / collaborator 側へ置く。
  - 例: datatype 変換、order 比較、required column 判定、`LastModifiedDT` 更新
- `tables/import` と `data-classes/sync` は preview と apply を明確に分ける。
- UI 上でも `tables/import` を project の最初の起点として見せる。
  - project hub から直接辿れること
  - downstream 画面から upstream import / sync へ戻るリンクがあること
  - DB schema 側の差分があれば「先に import / sync をしてください」と認知できること

## Import source / connector 方針

- DB import は UI 上の最初の操作点であり、単なる補助機能ではない。
- first target は MySQL とする。
- 接続先は Mtool 自身の DB に固定せず、将来的に次を扱える前提にする。
  - Local Mac 上の Docker DB
  - hosted STG DB
  - そのほか direct connection できる DB
- 仕組みとしては DB product 非依存の importer boundary を持ち、後続で PostgreSQL / SQLite などの connector を追加できるようにする。
- したがって first slice の実装でも、route / service から DB product 固有処理を分離する。

## Slice 1. Canonical metadata schema

- `docker/mariadb/config-initdb/` に upstream metadata 用 migration を追加する。
  - 想定番号は `023_*` と `024_*`
- 最初の slice で必要なもの
  - `dbtable`
    - primary key `PID`
    - project 単位の `name` unique
  - `dbtablecolumns`
    - primary key `PID`
    - `ProjectPID` + `dbtablePID` + `name` unique
    - `ColumnListOrder` で並びを保持
  - `dataclass`
    - primary key `PID`
    - project 単位の `name` unique
    - `LastModifiedDT` を DB 側で更新できる形にする
  - `dataclassfields`
    - primary key `PID`
    - `ProjectPID` + `dataclassPID` + `name` unique
    - `FieldListOrder` で並びを保持
- 追加列が必要でも、generated self-host DB Access が壊れない最小限に留める。

## Slice 2. Repository / service

- 新規 repository を追加する。
  - `shared/table_metadata_repository.php`
  - `shared/table_metadata_repository_pdo.php`
  - `shared/data_class_repository.php`
  - `shared/data_class_repository_pdo.php`
- 新規 service を追加する。
  - import preview / apply service
  - data class sync preview / apply service
- first slice の主要責務
  - import source から table / column 一覧を取得する
  - canonical row と diff を比較する
  - apply 時に insert / update / delete を行う
  - `dbtable.name == dataclass.name` の first-pass 対応で dataclass を作る
  - DB datatype から source datatype への変換を collaborator に切り出す
  - `LastModifiedDT` 更新を service で管理する

## Slice 3. Route / UI / CLI

- `tables/import`
  - GET: preview
  - POST: apply
  - table 単位 / column 単位 / all import を段階的に実装する
  - `PID` 列などの required column check を preview に残す
- `data-classes/sync`
  - GET: preview
  - POST: apply
  - `matched` / `not matched` / `order only mismatch` を区別して表示する
  - table ごとの sync と all sync を段階的に実装する
- CLI も用意する。
  - `scripts/import_project_tables.php` 相当
  - `scripts/sync_project_data_classes.php` 相当
- route common は canonical row が存在すればそれを優先し、未導入 project だけ bootstrap preview を fallback にする。

## Slice 4. MTOOL first proof

- MTOOL を最初の検証対象に固定する。
- import source は、旧実装の `MySQLShowColumn` 相当の live schema introspection を first slice の基準にする。
  - ただし canonical 保存後の source of truth は新 DB 内の metadata であり、`original-codes/` への恒久依存は持たない。
- 検証順
  1. MTOOL の table import preview
  2. apply
  3. data class sync preview
  4. apply
  5. 件数確認
  6. route 確認

## Slice 5. Runtime integration

- `shared/project_output_runtime_generator.php` の supplement は、上流の canonical slice で説明できるものから順に置き換える。
  - `CompareOutputSearchCache`
  - `ProjectSourceOutputSavedFiles`
  - `TestPatternSelection`
  - `UploadDropboxPathCache`
- `TestCondition` の stale property `ConditionOrder` は、generator 側の除外で持つのか、canonical metadata 側を補正するのかを上流 slice 実装時に決める。
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da` の `AuthType` / `SingleGetFuncPID` は、proxy metadata 側の責務として残す可能性が高い。upstream import/sync 完了後も bootstrap-only field のまま維持する案を許容する。

## 実装順

### 完了した順

1. migration 追加
2. repository 追加
3. import preview / apply service 追加
4. `tables/import` POST 実装
5. data class sync preview / apply service 追加
6. `data-classes/sync` POST 実装
7. MTOOL first import / first sync

### 次段

8. runtime generator の supplement 差し替え
9. connector boundary の一般化
10. `RUNTIME-DBCLASSES` 再生成と manifest 確認

## 検証項目

- syntax
  - 追加した PHP file に `php -l`
- DB
  - `dbtable`, `dbtablecolumns`, `dataclass`, `dataclassfields` の件数確認
- UI
  - `/projects/MTOOL/tables`
  - `/projects/MTOOL/tables/import`
  - `/projects/MTOOL/data-classes`
  - `/projects/MTOOL/data-classes/sync`
- CLI
  - import command
  - sync command
  - `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=RUNTIME-DBCLASSES --requested-by=codex`
- 生成結果
  - `_support/runtime-generation-manifest.json`
  - warning 件数
  - `canonical_data_class_count`

### 実測

- import
  - `20` tables
  - `233` columns
  - `16` tables without `PID`
- sync
  - `20` classes
  - `233` fields
- idempotence
  - import 2 回目は all same
  - sync 2 回目は all same

## 次段の主課題

- import source は現在 `MTOOL` 固定で `config_app` の live schema を見ている。将来の MySQL / PostgreSQL / SQLite connector を入れるには、DB product boundary をさらに明示する必要がある。
- `project hub` と downstream 画面では「先に import / sync を行う」認知をより強く出す余地がある。
- runtime generator 側では upstream canonical slice を正式入力にした再評価がまだ必要である。
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da` の bootstrap-only field を long-term にどう扱うかは未決である。

## リスクと判断ポイント

- import source を live DB introspection から始めるか、SQL dump / exported metadata から始めるかは判断が必要である。
  - first slice は旧 import flow と整合しやすい live introspection を優先する。
- upstream metadata table を modern naming に寄せる案もあるが、self-hosting DB Access との整合を考えると first slice は legacy-compatible naming を優先した方がよい。
- `dbtablecolumnsData` や `dataclassData` にあった helper method は、そのまま DTO に戻さない。
- まず MTOOL の one-project proof を通し、その後に project 横展開と migration refresh 手順を整理する。
