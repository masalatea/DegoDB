# Repo Boundaries / repository 境界

English companion:
This summary explains the responsibility of each top-level area in the repo. Read it when you need to decide where durable source of truth belongs, where disposable output should go, and where curated legacy references belong.

この文書は、AI と contributor が root directory ごとの責務を短時間で把握するための横断サマリです。  
詳細な path policy は [source-output-path-policy.md](source-output-path-policy.md)、`admin` と `lab` の責務分離は [site-boundaries.md](site-boundaries.md) を正本にします。

## 先に押さえる分類

| path | 区分 | current supported use | ここに置かないもの |
| --- | --- | --- | --- |
| `mtool/` | durable runtime / generator source | current 実装、script、reference、extension | disposable output、ad hoc scratch |
| `docker/` | durable base container asset | 共通 image / initdb | project 固有 seed、sample 固有 override |
| `sample/` | durable sample input | tutorial、internal pattern、representative pack | current raw output、runtime scratch |
| `tests/` | durable verification asset | integration test、scenario、fixture | user-facing sample input |
| `docs/` | durable documentation | 恒久文書、dated report | disposable work product |
| `work/` | disposable runtime work | output、artifact history、compare workspace、scratch | source of truth、durable reference |
| `mtool/reference/legacy-dbclasses/` | curated legacy reference | 旧生成 DB class 比較、migration context | full legacy source dump、current runtime input |
| `mtool/reference/legacy-mtool-build/` | curated legacy reference | 旧 mtool build ロジック、生成経路の確認 | current runtime input、Docker mount 前提の mainline |
| `mtool/reference/legacy-mtool-templates/` | curated legacy reference | 旧テンプレート、project setting、出力対応範囲の確認 | current runtime input、Docker mount 前提の mainline |

## Root ごとの見方

### `mtool/`

- current runtime / generator / script の正本
- `mtool/admin/` は control plane、`mtool/lab/` は検証 / compare / 実験 lane
- `mtool/reference/` は durable reference asset
- `mtool/reference/legacy-dbclasses/` は限定された curated legacy DB class reference
- `mtool/extensions/` は durable custom layer
- `mtool/resources/` は file-based resource catalog

AI が current implementation を読むときは、まず `mtool/` を source of truth として見る。  
generated output の scratch や compare 中間物はここへ戻さない。

### `docker/`

- repo 全体で共通の base image / initdb asset のみを置く
- sample pack や project ごとの compose override はここへ混ぜない

### `sample/`

- durable な sample pack input root
- active lane は次の 3 つに分かれる
  - `sample/tutorials/`
    - user-facing tutorial lane
    - `sample01-*` から simple-to-complex に並べる
  - `sample/internal-patterns/`
    - rewrite / generator / migration guard
    - `pattern01-*` から並べる
  - `sample/legacy-projects/`
    - representative runtime project pack
    - sanitized `50` 番台 legacy project packs
- current catalog 外の historical leftover は `sample/archive/` へ退避する

sample には durable input と curated `reference/` だけを置き、current raw output は置かない。  
sample 実行時の disposable state は `work/sample-packs/<pack>/` へ出す。

### `tests/`

- current gate を表す durable verification asset
- `tests/Integration/` は PHPUnit integration lane
- `tests/scenarios/` は Docker scenario lane
- `tests/fixtures/` は curated fixture input

AI が「今どこが守られているか」を知りたいときは、sample より先に `tests/` を見る。

### `docs/`

- date-less な恒久文書が source of truth
- `docs/reports/` は history / handoff / resume prompt
- stable rule を report だけに閉じ込めない

### `work/`

- disposable runtime output と中間物の root
- 主な中身:
  - `work/source-outputs/`
  - `work/artifacts/`
  - `work/staging/`
  - `work/compare-output/`
  - `work/job-history/`
  - `work/sample-packs/`
  - `work/tmp/`

`work/` は消えてよい前提で使う。  
一時 file は root `tmp/` ではなく `work/tmp/` に寄せる。

### `mtool/reference/legacy-*`

- `mtool/reference/legacy-dbclasses/` は旧生成 DB class の比較・移行確認に使う
- `mtool/reference/legacy-mtool-build/` は旧 mtool build ロジックと生成経路の確認に使う
- `mtool/reference/legacy-mtool-templates/` は旧テンプレート、project setting、出力対応範囲の確認に使う
- 新実装の runtime / generator / Docker container が直接の実行入力として使う path ではない

「旧 reference があるから runtime で読む」は current supported workflow ではありません。

## 判断ルール

### durable source of truth を置く場所

- current runtime / generator code: `mtool/`
- custom layer / extension: `mtool/extensions/`
- sample input: `sample/`
- verification asset: `tests/`
- documentation: `docs/`

### disposable file を置く場所

- current output
- staging
- artifact history
- compare workspace
- scenario-local state
- ad hoc scratch

上記はすべて `work/` 配下へ寄せる。

### curated legacy reference を置く場所

- 旧生成 DB class: `mtool/reference/legacy-dbclasses/`
- 旧 mtool build ロジック: `mtool/reference/legacy-mtool-build/`
- 旧 mtool template / project setting: `mtool/reference/legacy-mtool-templates/`
- 調査メモ: `docs/reports/`

旧実装を丸ごと repo に戻すのではなく、現在必要な文脈へ分割した curated reference として置く。

## よくある誤読

- `sample/reference/` は current raw output ではない
- `work/artifacts/...` は監査用 snapshot であり、日常の merge 先ではない
- `mtool/reference/dbclasses/` は durable runtime reference であって、`work/source-outputs/...` と同じ扱いではない
- `mtool/reference/legacy-*` は repo 内にあっても runtime dependency ではない

## 関連文書

- [../start-here.md](../start-here.md)
- [../current-supported-workflow.md](../current-supported-workflow.md)
- [source-output-path-policy.md](source-output-path-policy.md)
- [site-boundaries.md](site-boundaries.md)
