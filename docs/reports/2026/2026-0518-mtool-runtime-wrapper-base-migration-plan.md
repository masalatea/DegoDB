# 2026-05-18 Mtool Runtime Wrapper Base Migration Plan

## Status

- status: `PENDING`
- status updated at: `2026-05-27`
- role:
  - `2026-0527-broad-rewrite-temporary-closure-plan.md` を支える implementation detail / reference plan
  - この文書自体は broad rewrite current wave の active execution plan ではない
- milestone mapping:
  - `Phase 0` -> `M1. runtime contract truth normalization`
  - `Phase 1` -> `M2. DBACCESS wrapper/base migration`
  - `Phase 2` -> `M3. canonical generated data-* wrapper/base migration`
  - `Phase 3` から `Phase 5` -> next wave / `PENDING`

## 結論

- `sample1-simple-table` で固めた `root wrapper + base/*Base.php` 形式は、`MTOOL / RUNTIME-DBCLASSES` へはまだ全面適用していない。
- ただし思想レベルでは既に寄せており、「generated file を直接編集しない」「custom は generated file の外に置く」「継承で差し込む」という方針自体は current `MTOOL` にも入っている。
- 2026-05-18 late update として、bootstrap reader / catalog / build-plan 側は self-generated runtime bundle の `base/` / `_base/` / `_wrappers/` を読める状態まで進んだ。
- 置換は一気にはやらず、`DBACCESS`、`plain DTO data-*`、`non-plain bootstrap data-*`、runtime loader/custom bootstrap の順に分ける。
- `LanguageResource` 系は wrapper/base migration の対象としては扱わない。これは別途 `docs/internal/language-resource-separation.md` と retirement CLI 群で抜く前提であり、sample-first 候補からも除外する。

## 現状認識

### sample1 側

- `DATACLASS-PHP` は root `data-*.php` wrapper と `base/data-*Base.php` を出す。
- `DBACCESS-PHP` は root `dbaccess-*.php` wrapper と `base/dbaccess-*Base.php` を出す。
- `Base` は tool generated 専用で、ファイル先頭に「手動あるいは AI/Codex で編集しない」コメントを出す。
- `DBACCESS` base には旧 editable region 相当の per-function hook method を generator から出す。
- `custom` は generated file の中に持たず、wrapper 側で受ける。

### MTOOL 側

- default source output は依然として `RUNTIME-DBCLASSES` であり、local default definition も `generated-bootstrap-dbclasses` を使う。
- `canonical-dbaccess-php` / `canonical-dataclass-php` は `generated-wrapper-base-tree` 扱いだが、`RUNTIME-DBCLASSES` はまだ default `base-custom-wrapper-layer` 系である。
- current runtime generator は bootstrap reference を staging してから overlay しており、special runtime artifact として動いている。
- `data-*` は `64` class が canonical 生成、残る `37` class は `non-plain-bootstrap` として bootstrap copy を維持している。
- `dbaccess-*` は canonical metadata が十分な関数だけ SQL 本体まで再生成し、必要なら `_support/legacy-dbaccess/` に委譲する前提で動いている。
- latest generated bundle を `APP_REFERENCE_ROOT` に向けた no-DB focused check では、`generated_catalog` 系が `dbaccess method_candidate_count=626` と `data layout = generated-wrapper-base 84 / generated-layered-stub 17` を再現できる。
- ただし full self-loop を self-generated bundle 入力で完走させる最終確認は、現行 shell では `db-config` 接続が無いため保留である。

## 重要な差分

### 1. source output strategy が違う

- `sample1` は `canonical-dbaccess-php` / `canonical-dataclass-php` そのもの。
- `MTOOL` は runtime bundle 用の `generated-bootstrap-dbclasses` が主系。
- したがって「strategy 名ごと置き換える」のではなく、「runtime bundle の中で sample1 と同じ file contract を採る」方が安全である。

### 2. data 側の未解決 class が残っている

- `sample1` は plain DTO だけなので wrapper/base 化しやすい。
- `MTOOL` では `37` class が `non-plain-bootstrap` で、top-level helper、複数 class、default property value などを含む。
- ここを無理に同時移行すると、runtime bundle 全体の互換が崩れやすい。

### 3. runtime loader / legacy support が絡む

- `sample1` は単純な wrapper/base だけで済む。
- `MTOOL` runtime は custom layer bootstrap、legacy support、manifest など runtime 固有物を持つ。
- したがって end-state を sample1 型へ寄せつつも、途中段階では runtime loader 的な橋渡しを残す必要がある。

### 4. 実装と文書にズレがある可能性がある

- current documentation では `_base/` / `_wrappers/` / `_runtime_loader.php` を前提に書いている。
- 一方 current `project_output_runtime_generator.php` の canonical data / dbaccess 生成コードは root file へ直接書いている。
- まず actual generated tree を再確認し、doc と implementation の source of truth を揃える必要がある。

## 目標形

`MTOOL / RUNTIME-DBCLASSES` の generated 部分を、最終的に次の形へ揃える。

```text
mtool/dbclasses/
  data-Project.php
  dbaccess-Project.php
  base/data-ProjectBase.php
  base/dbaccess-ProjectBase.php
  _support/legacy-dbaccess/...
  _support/runtime-generation-manifest.json
```

### 方針

- top-level は user-visible wrapper とする。
- generated の本体は `base/*Base.php` に閉じる。
- `Base` は tool generated 専用と明示し、手動編集禁止コメントを generator から付与する。
- project-specific custom は引き続き `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/` に置く。
- `_support/legacy-dbaccess/` は中間段階では残す。
- old editable region は復活させず、必要な拡張点だけ hook / helper boundary として generator から出す。

## 置換計画

### Phase 0: 棚卸しと基準固定

- `RUNTIME-DBCLASSES` の actual generated tree を再生成し、現実の layout を確認する。
- `README.md`、`docs/internal/generated-code-strategy.md`、`mtool/extensions/MTOOL/RUNTIME-DBCLASSES/README.md` の記述と implementation を突き合わせる。
- `current truth` を implementation に合わせて正規化する。
- 2026-05-18 late update:
  - `generated_catalog.php` を layer-aware にし、top-level wrapper/stub から logical class/property/method catalog を返すようにした。
  - `app_project_output_runtime_bootstrap_data_file_info()` を self-generated runtime bundle 対応にし、`generated-wrapper-base` / `generated-layered-stub` を識別できるようにした。
  - `app_project_output_runtime_build_plan()` は already-layered runtime entry を再 layering せず passthrough するようにした。
- acceptance:
  - `RUNTIME-DBCLASSES` の actual tree を 1 つ確定できる。
  - doc/implementation mismatch を解消できる。

### Phase 1: DBACCESS を sample1 型へ寄せる

- `project_output_runtime_generator.php` の `dbaccess-*` 生成を、root wrapper + `base/dbaccess-*Base.php` 出力へ変更する。
- unsupported method は引き続き `_support/legacy-dbaccess/` へ delegate してよい。
- current root basename 互換は維持する。
- `Base` 先頭には sample1 と同じ「AUTO-GENERATED BASE FILE」「Do not edit manually or with AI/Codex」を出す。
- `DBACCESS` base には sample1 と同様に per-function hook method を generator から出す。
- custom override は root wrapper または extension layer 側で受ける。
- acceptance:
  - `check_mtool_self_loop.php` が通る。
  - representative `dbaccess-*` digest baseline を更新できる。
  - legacy delegate count は current 水準を悪化させない。

### Phase 2: generated 가능한 data-* を sample1 型へ寄せる

- 現在 canonical 生成できている `64` class について、root wrapper + `base/data-*Base.php` 出力へ変更する。
- `Base` header comment も sample1 と揃える。
- root wrapper は原則 empty class でよい。
- parent DTO 継承関係は sample1 と同じく `*Data extends *DataBase` で表す。
- acceptance:
  - current `canonical_data_class_count` を維持する。
  - representative `data-*` digest baseline を更新できる。
  - current plain DTO canonical 化の条件を崩さない。

### Phase 3: non-plain bootstrap data-* の扱いを分離する

- bootstrap-heavy な class を runtime 本体へ直接移す前に、まず dedicated sample pack で同形式を再現する。
- sample pack は actual tool output のみを置き、旧コードの構造を前提に挙動と file contract を確認する。
- 特に top-level helper、複数 class、default property value、constructor/body の癖を持つ class は、sample で再現できてから `MTOOL` へ横展開する。
- ただし retirement lane に入っている domain は、この phase の migration 候補に入れない。`LanguageResource` はここに含めず、removal/readiness 側で追う。
- 残る `37` class を、次の 2 群に分ける。
  - `A`: bootstrap class body を `Base` 化しやすいもの
  - `B`: top-level helper / 複数 class / default property などで直移行しにくいもの
- `A` は sample で成立した contract をそのまま使って bootstrap text を `base/*Base.php` 側へ移し、root wrapper を新設する。
- `B` は一時的に bootstrap copy 維持でもよいが、「なぜ残すか」を manifest に出し続ける。
- goal は eventually full wrapper/base だが、この phase では無理に全件完了を狙わない。
- 2026-05-18 update:
  - `sample11-da-dataclass-method-only` を追加し、`data-da.php` / `data-dataclass.php` の method-only `ADDITIONAL CLASS DEFINITION` を sample-first で wrapper/base 化した。
  - 同じ support lane を `MTOOL / RUNTIME-DBCLASSES` に適用し、self-loop 上の `canonical_data_class_count` は `84`、`bootstrap_data_class_count` は `17` になった。
- acceptance:
  - bootstrap-heavy class の少なくとも 1 系統で、旧コード構造を再現した sample pack を actual tool output で作れる。
  - `bootstrap_data_class_count` の減少が確認できる。
  - broken runtime class が増えない。
  - skip reason が manifest で追跡できる。

### Phase 4: runtime loader / custom bootstrap の整理

- wrapper/base 化が進んだ後に、`_runtime_loader.php` 相当の責務を見直す。
- `bootstrap.php` の読み込みだけを残すのか、wrapper 解決も loader で持つのかを決める。
- 可能なら sample1 に近い静的 require 構成へ寄せる。
- ただし external custom layer `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/` の contract は急に壊さない。
- acceptance:
  - custom extension layer の entrypoint が明確になる。
  - generated file と custom file の境界が文書・実装ともに一貫する。

### Phase 5: source output metadata / UI の最終整合

- `generated-bootstrap-dbclasses` という strategy 名を維持するか、runtime 向けの新名称へ切り替えるかを判断する。
- ここは先に file contract を固めてからでよい。
- UI caption / detail / help text も wrapper/base 前提へ更新する。
- acceptance:
  - admin UI と docs が current generator contract を正しく説明する。
  - runtime source output metadata が current reality と一致する。

## 検証計画

### 既存の回帰チェックを使う

- `mtool/scripts/check_mtool_self_loop.php`
- `make mtool-self-loop-check`

### 強化するポイント

- representative digest の対象に root wrapper / `base/*Base.php` の pair を入れる。
- `Base` header comment の存在を check する。
- `data_generation_items` で generated / bootstrap-copy / skip reason の差分を追う。
- `legacy_delegate_function_count` と `bootstrap_data_class_count` の trend を継続観測する。

### 将来の `make test` への編入

- `sample1` が固まった後で、`MTOOL` self-loop を `PHPUnit` または make aggregate に編入するのは有効。
- ただし runtime self-loop は sample1 より重いので、`default test` に入れるか `extended test` に分けるかは別判断にする。

## 実施順の提案

1. Phase 0 で actual runtime tree と docs のズレを正す  
2. Phase 1 で `DBACCESS` root wrapper/base 化  
3. Phase 2 で generated `data-*` root wrapper/base 化  
4. Phase 3 で remaining bootstrap-heavy `data-*` を圧縮  
5. Phase 4 で runtime loader/custom bootstrap を単純化  
6. Phase 5 で metadata / UI / docs を cleanup

## 今回の判断

- `sample1` の形式は `MTOOL` にそのまま横展開したい。
- ただし `MTOOL` runtime は bootstrap/legacy support をまだ抱えているため、まず `DBACCESS` から寄せ、`data-*` は generated subset と bootstrap-heavy subset を分けて進める。
- `LanguageResource` は migration 対象ではなく retirement 対象なので、next sample-first slice には使わない。
- `da` / `dataclass` の method-only editable-area slice は完了したため、next sample-first slice は別の surviving non-plain pattern を選ぶか、Phase 1 / Phase 2 の本線実装へ戻る。
- `DBACCESS` の root wrapper + `base/*Base.php` 化は引き続き重要だが、non-plain `data-*` の sample-first 検証では上記の surviving core class を使う。
