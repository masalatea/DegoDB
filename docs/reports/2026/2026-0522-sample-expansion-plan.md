# 2026-05-22 Sample Expansion Plan

## Status

- first pass: `DONE`
- status updated at: `2026-05-27`
- completion basis:
  - `sample/tutorials/`
  - `sample/internal-patterns/`
  - `sample/legacy-projects/`
  - tutorial lane `sample01` から `sample10`
  - `sample/README.md`
  - `docs/sample-tutorial-roadmap.md`
- note:
  - この plan の topology 再編と tutorial lane `sample10` までの current 化は完了した。
  - `sample11+` を何にするかは別の新規タスクとして扱う。

## 結論

- user-facing な tutorial sample は `1` から simple-to-complex に並べる。
- rewrite / migration 用の internal sample は tutorial lane と混ぜない。
- 実装方針としては、番号を後ろへずらすより、category を分ける方が分かりやすい。
- したがって current sample は次の 3 lane に整理する。
  - `sample/tutorials/`
  - `sample/internal-patterns/`
  - `sample/legacy-projects/`

## 背景

- current `sample/patterns/sample01-15` は、sample pack としては整っているが、役割は user tutorial ではなく rewrite / generator / migration guard に寄っている。
- `sample01-simple-table-runtime` だけは tutorial の先頭に置いても自然だが、`sample02-15` は「default property」「companion declarations」「top-level declaration」など internal contract を学ぶ lane であり、初学者向けの順番ではない。
- 今のまま `sample16` や `sample21` を足すと、番号が user-facing tutorial ではなく historical internal lane に引きずられる。

## 推奨 topology

### [DONE] 1. `sample/tutorials/`

- user-facing の正本
- pack 名は `sample01-*` から始める
- simple-to-complex の順で固定する
- README / Make help / 今後の導線はここを優先する

### [DONE] 2. `sample/internal-patterns/`

- rewrite / migration / generator contract の guard 用
- tutorial lane とは分ける
- pack 名は `pattern01-*` のように `sampleNN` から外す
- historical な PHPUnit class 名や check script 名は互換 layer として当面維持してよい

### [DONE] 3. `sample/legacy-projects/`

- representative runtime project lane
- `sample51-57` はこのまま維持する

## 具体案

### tutorials

| 新カテゴリ | pack 名 | 役割 |
| --- | --- | --- |
| `sample/tutorials/` | `sample01-simple-table-runtime` | 最小 runtime tutorial。現 `sample01` をここへ移す |
| `sample/tutorials/` | `sample02-dataclass-nullable-default-status` | nullable / default / status を含む plain Data Class |
| `sample/tutorials/` | `sample03-dataclass-lookup-and-helper` | lookup table と caption/helper |
| `sample/tutorials/` | `sample04-dataclass-parent-child-basic` | 親子 2 table の Data Class |
| `sample/tutorials/` | `sample05-dbaccess-select-basic` | single-table select |
| `sample/tutorials/` | `sample06-dbaccess-filter-sort-page` | where / order / pagination |
| `sample/tutorials/` | `sample07-dbaccess-crud-basic` | insert / update / delete |
| `sample/tutorials/` | `sample08-dbaccess-join-read-model` | join read |
| `sample/tutorials/` | `sample09-dbaccess-aggregate-report` | aggregate / report |
| `sample/tutorials/` | `sample10-dbaccess-mini-crud-flow` | read-write end-to-end |

### internal-patterns

| 新カテゴリ | pack 名 | 旧 sample |
| --- | --- | --- |
| `sample/internal-patterns/` | `pattern01-default-property-split` | `sample02-default-property-split` |
| `sample/internal-patterns/` | `pattern02-wrapper-property-helper` | `sample03-wrapper-property-helper` |
| `sample/internal-patterns/` | `pattern03-method-only-split` | `sample04-method-only-split` |
| `sample/internal-patterns/` | `pattern04-method-and-enum-basic` | `sample05-method-and-enum-basic` |
| `sample/internal-patterns/` | `pattern05-companion-declarations-basic` | `sample06-companion-declarations-basic` |
| `sample/internal-patterns/` | `pattern06-companion-declarations-no-top-level` | `sample07-companion-declarations-no-top-level` |
| `sample/internal-patterns/` | `pattern07-companion-declarations-multiclass` | `sample08-companion-declarations-multiclass` |
| `sample/internal-patterns/` | `pattern08-companion-declarations-multi-helper` | `sample09-companion-declarations-multi-helper` |
| `sample/internal-patterns/` | `pattern09-top-level-declaration-single` | `sample10-top-level-declaration-single` |
| `sample/internal-patterns/` | `pattern10-top-level-declaration-multiclass` | `sample11-top-level-declaration-multiclass` |
| `sample/internal-patterns/` | `pattern11-top-level-declaration-html-template` | `sample12-top-level-declaration-html-template` |
| `sample/internal-patterns/` | `pattern12-method-and-enum-no-top-level` | `sample13-method-and-enum-no-top-level` |
| `sample/internal-patterns/` | `pattern13-method-and-enum-multimethod` | `sample14-method-and-enum-multimethod` |
| `sample/internal-patterns/` | `pattern14-method-and-enum-heavy-multimethod` | `sample15-method-and-enum-heavy-multimethod` |

## 実装順

### [DONE] Phase 1: topology を直す

1. `sample01-simple-table-runtime` を `sample/tutorials/` へ移す
2. `sample02-15` を `sample/internal-patterns/` へ移す
3. `sample_pack_catalog` を `tutorials / internal-patterns / legacy-projects` の 3 category に分ける
4. README / tests / Make help の説明を user-facing lane 優先に書き換える

### [DONE] Phase 2: compatibility layer を残す

1. historical な `Sample9-22` / `check_sample*` は当面維持する
2. old pack 名から new pack path への alias を必要最小限だけ残す
3. target 名は次のように分ける
   - tutorial: `sampleXX-pack-runtime-test`
   - internal pattern: `patternXX-output-test`

### [DONE] Phase 3: tutorial sample を増やす

1. `sample02-dataclass-nullable-default-status`
2. `sample05-dbaccess-select-basic`
3. `sample03` / `sample04` / `sample06-10`

## `make test` 方針

- `make test` の正面 lane は tutorial と internal guard の両方を持つ
- ただし重い runtime pack を全部入れない
- 初期状態では次を対象にする
  - tutorial: `sample01`, `sample02`, `sample05`
  - internal guard: current `pattern01-14`
- representative runtime project は引き続き `sample-pack-runtime-smoke` 側で扱う

## この方針を採る理由

- user は `sample01 -> sample02 -> sample03` の順で tutorial を読める
- internal guard は番号の意味を tutorial lane に汚染しない
- 直近でまた `sample01-15` を user-facing に見せ直すより、役割ごと分けた方が README と Make help を単純にできる
- 今後 `Proxy`、`HTML`、`LanguageResource` tutorial を追加しても、`sample/tutorials/` の番号体系だけで整理できる

## 次の具体アクション

1. `[DONE]` category 再編の rename plan を作る
2. `[DONE]` `sample_pack_catalog.php` の category model を 3 lane 化する
3. `[DONE]` `sample01` の tutorial 化を先に完了する
4. `[DONE]` その後で新しい tutorial `sample02` と `sample05` を作る

この時点では、`sample16` / `sample21` を増やすより、番号体系と category を先に正す方が優先度が高い。
