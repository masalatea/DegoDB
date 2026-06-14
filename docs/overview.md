# Concept Overview / 概念概要

English companion:
This is the concept-level overview of the tool. It explains the core flow from DB structure to generated outputs, the two API publication models, and how the permanent docs should be read together when you need the high-level architecture before implementation detail.

## 目的

- このツールの役割を、再構築途中の実装状況と切り分けて説明する。
- 「何を起点に metadata を作り、どの順で生成物へつなげるか」を最初に把握できるようにする。
- `README.md` が再構築手順へ寄りすぎないよう、ツール本来の利用フローを別文書として固定する。

## このツールが扱う範囲

- このツールの起点は DB 構造である。
- ただし DB 構造そのものを決める作業は、このツールのスコープ外である。
- まず要求に沿って DB の table / column 構造を決め、その後にこのツールへ import する。
- import 後は、その設計情報を canonical metadata として管理し、Data Class、DB Access、Source Output の生成へつなげる。

## 基本フロー

1. ツールの外で DB 構造を決める。
   - 要求に応じて table / column / key / relation を決める。
   - ツール都合の制約を多少考慮することはあっても、起点はあくまで DB 構造である。
2. DB 設計情報を import する。
   - 取り込み先は `dbtable` / `dbtablecolumns` である。
   - ここで初めてツールが設計情報を扱える状態になる。
3. Data Class metadata を整える。
   - `dbtable` / `dbtablecolumns` をもとに `dataclass` / `dataclassfields` へ同期する。
   - その metadata から Data Class を生成する。
4. DB Access metadata を整える。
   - `da` / `dafunc` と周辺 designer metadata を管理する。
   - select / insert / update / delete の条件と target field を設計する。
5. Source Output を生成する。
   - `ProjectSourceOutput` ごとに runtime / proxy / language resource などの出力先を定義する。
   - 生成物は Data Class と DB Access metadata をもとに出力される。
6. build / compare / run で確認する。
   - 生成結果の比較、build、run、review は `lab` 側の責務で扱う。

つまり、このツールの主線は `DB 構造` -> `import` -> `Data Class` -> `DB Access` -> `Source Output` である。

## API 公開モデル

`DB Access` を設計した後、どの `dafunc` をどう外へ公開するかを決める。
この段階で、新実装も次の 2 つを分けて扱う。

### 1. `Single Function Proxy`

- 基本の API 公開モデル
- `1 dafunc = 1 endpoint` をそのまま表す
- 名称、request、response、認証設定を function 直結で管理する
- DB Access の設計意図をもっとも素直に公開する経路であり、最初に選ぶべきモデル

### 2. `Custom Proxy`

- 上位の構成モデル
- 複数 `dafunc` を束ねて 1 use-case endpoint を組み立てる
- transaction、failure policy、step 順序などを proxy 単位で持つ
- `Single Function Proxy` で表現できないときだけ使う

重要なのは、`custom` が `single` の単なる包含や代用品ではないことである。
内部実装で共通部品を共有しても、metadata、UI、target assignment、source output の意味は分けて保つ。

## 中心データの流れ

- `dbtable`, `dbtablecolumns`
  - import された DB 構造の保存先
- `dataclass`, `dataclassfields`
  - table / column 情報を言語非依存の class 構造へ写した metadata
- `da`, `dafunc` と designer sub-resource
  - DB Access 系の function 設計
- `ProjectSourceOutput`
  - どの metadata から、どの言語 / どの形で source を出すかの定義

## 現在の新実装との対応

新実装の route も、意図としては上の flow に合わせている。

- `/projects/{project_key}/tables`
- `/projects/{project_key}/tables/import`
- `/projects/{project_key}/data-classes`
- `/projects/{project_key}/data-classes/sync`
- `/projects/{project_key}/db-access`
- `/projects/{project_key}/db-access/sync`
- `/projects/{project_key}/source-outputs`

proxy 系の target design は次の分担にする。

- `single-function proxy`
  - `db-access` / function detail 配下で扱う
  - function 単位の auth / target source output を編集する
- `custom proxy`
  - `/projects/{project_key}/proxy/custom` 配下で扱う
  - proxy metadata / step / target source output を編集する

ただし、2026-05-11 時点では再構築途中のため、実装状況は次の通りである。

- `tables/import`
  - `MTOOL` first slice では `live schema` から canonical import を実行できる
  - `db-lab` を読む `lab live schema` source も使え、Lab 側で変更した schema を admin 側 canonical metadata へ取り込める
  - 任意 host / 他 DB connector への一般化は今後の拡張対象であり、runtime reference fallback も残している
- `data-classes`
  - `MTOOL` first slice では canonical sync を実行できる
  - 一覧 / detail は canonical row を優先し、未導入 project だけ runtime reference fallback を使う
- `db-access`
  - class / function row の canonical sync と runtime regeneration はかなり進んでいる
- `single-function proxy`
  - canonical metadata table / function detail UI / source output target binding はある
  - sample/test source output に対する first-class artifact generator も接続済み
- `source-outputs`
  - `RUNTIME-DBCLASSES`、proxy server / client artifact の生成が動いている
- `custom proxy`
  - canonical metadata UI と artifact generator が先行している

つまり、現状の実装は一部で runtime reference / legacy fallback に依存しており、Project 1 の本流 proxy はまだ `custom` 側が先行しているが、`single` も別経路として actual build まで通る。目標フロー自体は `DB-first / import-first` のままであり、API 公開モデルも `single-first / custom-advanced` を維持する。

## 文書の読み分け

- `docs/README.md`
  - 新実装側ドキュメントの索引と区分け
- `README.md`
  - 再構築環境の起動手順と現状の入口
- `docs/internal/README.md`
  - 実装内部、architecture、migration map の索引
- `original-codes/docs/overview.md`
  - 旧システム全体の静的読解ベースの概要
