# 2026-0511 Gradual Legacy Absorption Plan

## Status

- status: `PENDING`
- status updated at: `2026-05-27`
- role:
  - legacy absorption 全体の parent framework / reference plan
  - broad rewrite current wave の active execution plan ではない
- current usage:
  - `DB Import -> Data Class -> DB Access -> residual classification -> Tmp promotion` の master order を保持する
  - ただし current temporary closure wave では residual 6 class の最終分類と `Tmp` 昇格本線は next wave に送る
  - 現在の実行管理は `2026-0527-broad-rewrite-temporary-closure-plan.md` に集約する

## 目的

- 旧 Mtool の機能を、新実装へ段階的に取り込み切る。
- いきなり全面置換はせず、`Tmp` 出力で比較しながら、安全な slice から昇格する。
- 旧機能の取りこぼしを防ぐため、legacy source ごとの対応状況を固定して追跡する。

## ゴール

- 最終的なゴールは「旧機能を漏れなく取り込むこと」であり、単なる schema 移植ではない。
- 一致の基準は byte 単位の旧式再現ではなく、semantic / functional parity を満たした上での新設計への吸収である。
- 実運用の self-loop 対象は `Project 1 (MTOOL)` を唯一の基準とし、他 project は同等機能を再現できることを確認する test/reference data として扱う。
- 将来の期待出力テストは `Project 1 (MTOOL)` の artifact を基準に追加する。
- 旧 runtime が依存していた source / table / helper / join DTO について、すべてを次のいずれかへ分類する。
  - 現行 canonical table へ移す
  - 新しい canonical table として追加する
  - `lab` / job / cache domain へ移す
  - physical table ではなく derived helper / query / wrapper として持つ
- どの source も、未分類のまま消さない。

## 現在の基準点

- self-host import の first slice は完了している。
  - `dbtable` / `dbtablecolumns`
  - `dataclass` / `dataclassfields`
  - import / sync UI と CLI
- `MTOOL` の最新確認 artifact は `20260511-050913-5c31f320`。
- `RUNTIME-DBCLASSES` は `canonical-dbaccess-partial-sql-regenerated`。
- legacy DB import baseline として [mtool/reference/mtool-legacy-table-schema.json](../../../mtool/reference/mtool-legacy-table-schema.json) を追加した。
  - legacy reference: `90` tables / `750` columns
  - current self-host mapped scope: `21 / 90` tables
- `DB Import` の source abstraction を first slice として実装し、`/tables/import` と CLI で source を切り替えられるようにした。
  - source option: `live-schema`, `legacy-reference`
  - `live-schema` preview/apply は idempotent に確認済み
    - source tables: `21`
    - canonical tables: `21`
    - table diff: `0 insert / 0 change / 0 delete`
    - column diff: `0 insert / 0 change / 0 delete`
  - `legacy-reference` preview は確認済みで、apply は preview-only として拒否される
    - source tables: `90`
    - canonical tables: `40`
    - table diff: `67 insert / 4 change / 17 delete`
    - column diff: `573 insert / 10 change / 209 delete`
  - これにより、残件 6 class のうち
    - `CompareOutputSearchCache`
    - `ProjectSourceOutputSavedFiles`
    - `TestCondition`
    - `TestPatternSelection`
    - `UploadDropboxPathCache`
    は current self-host slice の外にある legacy physical table 起因だと確認できた。
  - `daCustomProxyFunc_leftouterjoin_dafunc_and_da` は table import ではなく、後続の DB Access / derived DTO 整理で扱う。
- `DB Import` は full replace ではなく managed scope 単位で扱う形へ変更した。
  - `live-schema` は current self-host 21 table slice だけを管理する
  - `legacy-reference-test-module` を追加し、`Test*` 7 table / 44 column を canonical `dbtable` / `dbtablecolumns` へ import 済み
  - `legacy-reference-build-run-state` を追加し、build/cache/saved-files 12 table / 104 column を canonical `dbtable` / `dbtablecolumns` へ import 済み
  - import 後も `live-schema` preview/apply は `21 table / 238 column same` のまま維持され、追加 legacy slice を stale 扱いしないことを確認済み
  - 現在の canonical table metadata は `40` tables
  - `data-classes/sync` は `40` class / `386` field で same を確認済み
- 直近の主要件数は以下。
  - `generated_dbaccess_count=101`
  - `canonical_function_count=626`
  - `sql_regenerated_dbaccess_count=100`
  - `sql_regenerated_function_count=518`
  - `canonical_data_class_count=64`
  - warning は `0` 件
- proxy auth ownership は次のように整理済み。
  - single-function proxy / endpoint preview: `project_db_access_functions.single_proxy_*`
  - multi-step custom proxy build plan / artifact: `project_custom_proxies.auth_type` / `single_get_function_name`
  - enum と legacy default 解釈は shared resolver で共通化する
- single-function proxy target assignment については `project_db_access_function_source_output_targets` を追加し、function detail / source output detail から canonical metadata として編集・参照できるようにした。
  - `legacy_seed_tmp.dafuncSimpleProxySourceOutputTarget` 全体では `374` row あるが、`Project 1 (Mtool)` に限ると `17` row である。
  - その `17` row の内訳は legacy `ProjectSourceOutputPID=28` (`proxy_paypal`) が `16` 件、`PID=117` (`proxy_uploader`) が `1` 件で、current canonical `RUNTIME-DBCLASSES` / `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` には直接対応しない。
  - `ApacheHostSetting` 8 件を除くと残件は `Project` 6 / `PaypalSubscription` 1 / `DropboxUploadToken` 1 である。self-loop 上の project discovery は `DB-GETPROJECTLIST` custom proxy が `GetProjectbyOwnerOrUserSecurityList` を `DBIMPORT-PROXY-*` へ載せているため、simple proxy row は現時点では blocker ではない。
  - したがって、この table 自体は first slice として入ったが、legacy row の backfill / remap と proxy artifact generator への統合はまだ次段である。
- `scripts/check_mtool_self_loop.php` と `make mtool-self-loop-check` を追加し、`MTOOL` の import / sync / generate を一括で回して expected runtime manifest と representative generated file digest baseline を検証できるようにした。
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da` は intentional derived DTO として残すが、legacy `AuthType` / `SingleGetFuncPID` は join DTO に持たせず custom proxy 本体 metadata 側へ寄せる。

## 基本仮説

今後の計画は、「個別差分を downstream で潰す」のではなく、「上流から正しく作り直せば大半の差分は自然に消える」という前提で立てる。

前提は次の通りである。

1. まず DB 設計が決まる
2. その設計を tool に import する
3. import された table/column metadata から data class を作る
4. data class と table metadata から DB access metadata を組み立てる
5. その canonical metadata から source output を出す

この流れで、同じ設計を同じ層で比較している限り、説明不能な差分は基本的に残らないはずである。

したがって現状の差分は、主に次の理由で生じている。

1. 現在の import は `config_app` の現行 physical schema を起点にした first slice である
2. 現在の data-class sync は `dbtable.name == dataclass.name` の first-pass である
3. 旧 runtime には cache table、run-state table、join DTO、helper DTO が含まれている

つまり今は、

- 現行 physical schema 起点の canonical
- 旧 runtime の semantic/helper layer

が混在して比較されている。したがって、まず上流から一致を取り直すのが先である。

## 原則

- upstream first で進める
- big-bang 置換はしない
- `Tmp` 出力を先に作る
- 現行 release output は promotion 条件を満たすまで直接上書きしない
- `original-codes/` は reference only
- editable region 方式には戻らない
- generated/custom の境界は維持する
- DTO は薄く保つ
- business logic は helper / service / policy / collaborator / custom wrapper 側へ寄せる
- legacy に合わせるだけでなく、新しい方針に寄せた方がよい部分は canonical 設計へ取り込む
- ただしその場合も、何を改善したのか、何が旧機能と等価なのかを説明できるようにする

## 制御文書

- 対応表の source of truth は [docs/internal/legacy-new-db-mapping.md](<repo-root>/docs/internal/legacy-new-db-mapping.md)
- この対応表で、legacy source ごとの扱いを固定する
- promotion 判定は、対応表と regenerate 結果の両方で行う

## マスター順序

今回の主計画は、次の固定順序で進める。

1. `DB Import` を新旧一致させる
2. `Data Class` を新旧一致させる
3. `DB Access` を新旧一致させる
4. 残差だけを分類する
5. `Tmp` 出力で確認し、安全なものだけ昇格する

この順序を崩して downstream 側から個別差分を直すと、後で上流を直したときに再び差分が動く。

## Phase 1. DB Import 一致

この段階の目的は、`dbtable` / `dbtablecolumns` を旧設計と同じ source of truth に揃えることである。

ここでいう一致は、旧 SQL をそのまま複製することではない。新しい importer boundary、connector abstraction、UI 導線などは新設計を採る。ただし table/column contract は説明可能な形で旧機能を吸収する。

### やること

1. import source を「対象 DB の設計情報」に固定する
2. Mtool 自己再構築では、旧 Mtool 設計相当を import できる状態にする
3. `dbtable` / `dbtablecolumns` の以下を新旧一致で確認する
   - table 名
   - column 名
   - datatype
   - key / null / default / extra
   - order
4. UI 上も DB Import を最初の入口として明示する

### 完了条件

- 新旧の table/column 対応が説明不要なレベルで一致する
- import preview/apply が idempotent に動く
- mismatch の原因が import source 側に残っていない

## Phase 2. Data Class 一致

この段階の目的は、`dataclass` / `dataclassfields` を import 結果から正しく再構成し、旧 data class と同じ semantic layer へ寄せることである。

同時に、DTO は薄く保つという新方針も適用する。旧 helper が data class に載っていたとしても、そのまま戻さず helper / service / policy へ移す。

### やること

1. `dbtable` / `dbtablecolumns` を唯一の入力として data class sync を見直す
2. table/class、column/field の対応規則を固定する
3. first-pass の `dbtable.name == dataclass.name` だけでは吸えないものを整理する
4. bootstrap 依存や ad hoc supplement を減らす

### 完了条件

- data class の差分理由が upstream metadata で説明できる
- 未吸収 class がある場合も、「metadata 不足」か「intentional difference」かが明確になる
- downstream generator 側で property 差分を個別補修しなくて済む状態に近づく

## Phase 3. DB Access 一致

この段階の目的は、`da` / `dafunc` / 各 child metadata を canonical から再構成し、旧 DB access runtime と同じ semantic layer に揃えることである。

ここでも旧実装をそのまま再現するのではなく、normalized metadata、SQL regenerate、Base/Custom wrapper などの新設計へ寄せる。

### やること

1. `project_db_access_classes` を source class 一覧として再評価する
2. `project_db_access_functions` を function 定義の canonical として揃える
3. `where` / `target_fields` / `having` / `insert` / `update` / `delete` child metadata を揃える
4. single-function proxy target と custom proxy target の source output 責務を分けたまま canonical 化する
5. SQL regenerate を進め、delegate/fallback を段階的に減らす

### 完了条件

- function 数、target table、主要 parameter 定義が新旧一致する
- regenerate 結果の差分が DB access metadata で説明できる
- fallback / delegate は未実装部分に限定される

## Phase 4. 残差分類

上流 3 phase を通したあとに、それでも残る差分だけを分類する。

### 主な対象

- cache table
- build / run-state table
- saved-files table
- `*_leftouterjoin_*` 系 join DTO
- import helper / connector helper

### 分類先

- canonical physical table
- canonical run/job table
- derived query / helper / wrapper
- bootstrap-only temporary hold

## Phase 5. Tmp 出力と昇格

1. current canonical metadata から `Tmp` 出力を作る
2. legacy runtime / 現行 output と diff を見る
3. 差分を説明できる状態にする
4. promotion 条件を満たした slice だけ昇格する

## promotion 条件

- 上流 phase の完了条件を満たしている
- 対応表上、その source が未分類でない
- field 単位の差分理由を説明できる
- 旧機能の欠落がない
- 新しい設計へ寄せた差分は intentional improvement として説明できる
- custom 境界が壊れていない
- regenerate が通る
- warning が増えていないか、増えた理由が説明できる
- PHP 変更を含む場合は `php -l` を通す

## 直近の実装順

### 1. DB Import を first-class にする

- import source / preview / apply / UI 導線を「すべての起点」として固める
- `dbtable` / `dbtablecolumns` の新旧一致表を作る
- ここで table/column の不一致を先に潰す

### 2. Data Class sync を再構成する

- `dataclass` / `dataclassfields` を import 結果から再構成する
- 新旧 data class 一致を先に取る
- 残る差分は metadata 不足か intentional difference かを切り分ける

### 3. DB Access canonical を再構成する

- `da` / `dafunc` と child metadata を整える
- `sql_regenerated_*` を増やし、delegate/fallback を減らす

### 4. そのあとで feature / cache / derived を扱う

- HTML
- Language Resource
- Security
- Test
- Req / Spec / Minutes / Chat
- Upload / Host / DB infra
- build / run-state / cache
- join DTO / helper DTO

## 直近の差分 6 class の扱い

残件 6 class は、主対象ではなく upstream 整備後の検証点として扱う。

- `CompareOutputSearchCache`
- `ProjectSourceOutputSavedFiles`
- `TestCondition`
- `TestPatternSelection`
- `UploadDropboxPathCache`
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da`

この 6 件に対して先に局所修正を入れるのではなく、次の順で見る。

1. DB Import を直したあとも残るか
2. Data Class を直したあとも残るか
3. DB Access を直したあとも残るか
4. それでも残るなら residual classification 対象とする

この順なら、「metadata 不足」と「意図的差分」が自然に分かれる。

## 実務上の運用

- 置換作業の単位は「下流の 1 class 修正」ではなく「上流の 1 phase 完了」で見る
- ただし実装は小さく進める
- `Tmp` 出力の比較結果を残し、差分説明を先に固めてから promotion する
- 新旧対応表を更新せずに実装だけ先に進めない
- 上流 phase 完了前に downstream generator へ暫定補修を増やさない

## 完了条件

- `DB Import -> Data Class -> DB Access -> 残差分類 -> Tmp昇格` の順で説明可能な状態になる
- 対応表上の全 legacy source が `done`、`partial` のままではなく、最終的に
  - `done`
  - `intentional derived`
  - `run/job domain へ移管済み`
のいずれかになる
- promotion 済み slice について、`original-codes/` への runtime 依存がなくなる
- 最終的な runtime artifact が、旧機能を欠落なく吸収した状態になる
