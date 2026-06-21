# Glossary

English companion:
This glossary gives fast definitions for the repo's common terms. Use it to align on the meaning of DB structure, Data Class, DB Access, runtime reference, sample lanes, and boundary language before diving into the deeper design documents.

この文書は、AI が repo 内の用語を短時間で揃えるための簡潔な語彙集です。  
厳密な仕様は各恒久文書を正本にし、この文書では「まずどう読むか」を固定します。

## DB structure

### `DB 構造`

- table / column / key / relation を含む設計の起点
- この repo の外で決め、その後に Mtool へ import する

### `dbtable` / `dbtablecolumns`

- import された DB 構造を保持する canonical metadata
- table / column 情報を最初に受ける層

## Data Class

### `dataclass` / `dataclassfields`

- `dbtable` / `dbtablecolumns` を言語非依存の class 構造へ写した canonical metadata
- Data Class generator の入力になる

### `Data Class`

- table / column 由来の値を運ぶ generated class
- 新方針では DTO / value object として薄く保つ

## DB Access

### `da`

- DB Access class 相当の metadata
- どの function 群をどの class にまとめるかを表す

### `dafunc`

- DB Access function 相当の metadata
- select / insert / update / delete の対象、where、target field などを持つ

### `DB Access`

- `da` / `dafunc` から生成される DB access layer
- `Data Class` の次段で、query / write logic の generated source を表す

## Proxy / API

### `single-function proxy`

- `1 dafunc = 1 endpoint` の公開モデル
- 基本の API 公開経路として先に考える

### `custom proxy`

- 複数 `dafunc` を束ねる上位の公開モデル
- transaction、step 順序、failure policy などを持てる
- `single-function proxy` で表現しきれない時に使う

## Output / Runtime

### `source output`

- どの metadata から、どの言語 / どの形で source を出力するかを定義した単位
- runtime / proxy / language resource などの出力先を含む

### `current raw output`

- 直近の生成結果
- durable reference ではなく、通常は `work/source-outputs/` に出る disposable output

### `artifact`

- output を監査・比較・promote 用に束ねた snapshot
- `work/artifacts/` に履歴として残るが、通常は disposable 扱い

### `runtime reference`

- current runtime が authoritative に参照する durable generated tree
- `MTOOL / RUNTIME-DBCLASSES` では `mtool/reference/dbclasses/` が該当する

### `promote`

- verified artifact を durable runtime reference へ昇格する操作
- `make promote-runtime-reference` を使う

### `restore`

- durable snapshot から runtime reference を回復する操作
- `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` を使う

## Sample / Test lanes

### `tutorial lane`

- `sample/tutorials/` にある user-facing sample 群
- `sample01-*` から simple-to-complex に並べる

### `internal pattern lane`

- `sample/internal-patterns/` にある rewrite / generator / migration guard 群
- `pattern01-*` から並べる

### `representative project lane`

- `sample/legacy-projects/` にある実 project 由来の runtime pack 群
- `sample/legacy-projects/`

### `sampleNN-pack-runtime-test`

- tutorial lane の canonical test target 名
- 例: `sample01-pack-runtime-test`

### `patternNN-output-test`

- internal pattern lane の canonical test target 名
- historical `sample9-22-output-test` 互換 layer とは分けて使う

## Boundary terms

### `current supported workflow`

- 今の repo で green path として信頼してよい mainline
- archived helper や historical workaround は含めない

### `curated legacy reference only`

- repo 内にあっても、runtime / generator / Docker container が直接入力として使わない参照資産
- `mtool/reference/legacy-dbclasses/`、`mtool/reference/legacy-mtool-build/`、`mtool/reference/legacy-mtool-templates/` の扱いを表す言葉

### `durable`

- source of truth として repo に残す前提の資産
- 例: `mtool/`、`sample/`、`tests/`、date-less な `docs/`

### `disposable`

- 消えてよく、再生成や再起動で戻せる資産
- 例: `work/` 配下の output、staging、artifact history、scratch

## 関連文書

- [overview.md](overview.md)
- [current-supported-workflow.md](current-supported-workflow.md)
- [internal/README.md](internal/README.md)
- [sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)
