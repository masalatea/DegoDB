# 2026-05-27 Broad Rewrite Temporary Closure Plan

## Status

- execution status: `DONE`
- status updated at: `2026-05-28`
- control rule:
  - broad rewrite current wave の active execution plan はこの文書 1 本に固定する
  - 関連する broad rewrite 親計画は `DONE` または `PENDING` の supporting/reference 扱いにする
- current milestone:
  - `[DONE]` `M1. runtime contract truth normalization`
  - `[DONE]` `M2. DBACCESS wrapper/base migration`
  - `[DONE]` `M3. canonical generated data-* wrapper/base migration`
  - `[DONE]` `Close. verification / docs / status freeze`
- current execution note:
  - `M1` は `2026-05-28` に完了
  - `M2` は `2026-05-28` に完了
  - `M3` は `2026-05-28` に完了
  - `Close` は `2026-05-28` に完了し、permanent docs の英語 companion 追加、docs rule 明文化、docker-based verification、status freeze まで完了
  - `make mtool-self-loop-check` は `db_access_sync_summary=117/117/701` baseline 追加後に green へ復帰した
  - full suite `make test` も `OK (134 tests, 6681 assertions)` で current baseline に更新した
- completion line:
  - `M1` から `M3` まで完了した時点を broad rewrite current wave の `中間完了` とする
  - `Close` 完了をもって current wave の status freeze を行う

## 結論

- 今回の終了線は `self-host / runtime 置換完了` ではなく、`機能移植完了を閉じやすい安定化完了` に置く。
- 実行主計画はこの `2026-0527-broad-rewrite-temporary-closure-plan.md` とし、実装 detail は `2026-0518-mtool-runtime-wrapper-base-migration-plan.md` を参照する。
- current wave では `DBACCESS` と canonical 生成できる `data-*` の wrapper/base contract を current reality として揃える。
- `non-plain bootstrap data-*`、runtime loader の大整理、page security / host assignment、HTML / Source Output bridge debt、residual 6 class の最終分類は次 wave に送る。

## Execution Milestones

### [DONE] M1. runtime contract truth normalization

- `2026-0518` の `Phase 0` に対応する。
- `RUNTIME-DBCLASSES` の actual generated tree、docs、generator 実装のズレを解消する。
- current repo 内で `wrapper/base contract` の truth を 1 つに固定する。
- completed at: `2026-05-28`
- result:
  - current emitted/promoted tree の visible layout を `docs/internal/generated-code-strategy.md` に固定
  - permanent docs / extension README / artifact scaffold wording を current emitted contract に同期
  - focused verification と rollout reason code 正規化まで完了

### [DONE] M2. DBACCESS wrapper/base migration

- `2026-0518` の `Phase 1` に対応する。
- `dbaccess-*` を root wrapper + `base/*Base.php` contract に寄せる。
- legacy delegate は一時維持可だが、見える contract は wrapper/base に揃える。
- completed at: `2026-05-28`
- result:
  - `legacy_delegate_function_count=0` の current runtime では `base/dbaccess-*Base.php` が standalone になり、legacy support require/extends を持たない
  - `_support/legacy-dbaccess/dbaccess-*.php` は circular wrapper ではなく compatibility placeholder class に正規化
  - focused PHPUnit は green、`make mtool-self-loop-check` でも runtime artifact hash / generation summary は一致
  - `db_access_sync` は generated runtime `99/611` とは別に canonical-bootstrap metadata table `18` 件を含む `117/701` を返すため、self-loop baseline は runtime `generation_summary` と `db_access_sync_summary` を分離して固定した

### [DONE] M3. canonical generated data-* wrapper/base migration

- `2026-0518` の `Phase 2` に対応する。
- canonical 生成できる `data-*` を root wrapper + `base/*Base.php` contract に寄せる。
- `non-plain bootstrap` 全件の解消は current wave の条件に含めない。
- completed at: `2026-05-28`
- result:
  - current promoted runtime reference は `canonical_data_class_count=99`、`data_entity_count=99`、`bootstrap_data_class_count=0`
  - non-plain candidate `36` class も emitted runtime contract では wrapper/base lane に吸収済み
  - `RuntimeReferenceLayoutContractTest` を補強し、promoted `data-*.php` / `base/data-*Base.php` の pair と manifest `data_generation_items[*].decision=generated` を focused verification で固定
  - `M3` slice では新たな generator 変更は不要で、active plan / handoff wording を current reality に同期した

### [DONE] Close. verification / docs / status freeze

- focused verification、digest / self-loop 確認、docs wording 更新、status freeze を行う。
- `README.md`、`docs/` 配下の一般/permanent docs は日英併記へ正規化する。
- `docs/internal/` 配下の permanent docs も同じ日英併記ルールで維持する。
- `docs/reports/` 配下の progress / handoff / slice report は日本語のみのままでよい。
- `M1` から `M3` が完了した後の close-out step とする。
- `Close` 完了をもって broad rewrite current wave の実行を止める。
- completed at: `2026-05-28`
- result:
  - `README.md`、`docs/*.md`、`docs/internal/*.md` の permanent docs 全 `27` file に `English companion:` 冒頭節を追加した
  - `docs/README.md` と `docs/internal/README.md` に `恒久 docs = 英語 companion 付き / docs/reports = 日本語のみ` rule と `top-level docs は外部ユーザ向け / internal docs は 1 段内側` の整理を明文化し、`DocsEntranceContractTest` で固定した
  - `DocsEntranceContractTest` は `OK (12 tests, 443 assertions)` で green を確認した
  - representative focused verification (`RuntimeReferenceLayoutContractTest`、`SelfGeneratedRuntimeResolverTest`、`RuntimeReplacementRolloutLaneTest`、`OpenApiSourceOutputContractTest`、`ProjectDbAccessBootstrapRuntimeContractTest`、`BlobContractGuardTest`) は全件 green を再確認した
  - `make mtool-self-loop-check` は current sync reality (`117/117/701`) を `db_access_sync_summary` として baseline 化した後に green を確認した
  - `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` は `OK (134 tests, 6681 assertions)` で green を確認した
  - progress snapshot / resume prompt / plan status を current frozen state へ更新した

## なぜこの切り方か

- `2026-0514-functional-migration-vs-self-host-plan.md` で、`機能移植完了` と `self-host / runtime 置換完了` は別物だと整理済みである。
- すでに current scope では docs、bundle、config DB externalization、OpenAPI visibility、tutorial lane、Project 1 parity `36/36` が揃っている。
- ここで broad rewrite まで full self-host を同時に狙うと、終了条件が再び曖昧になる。
- したがって、次の 1 回は `Phase 1 をきれいに閉じるための runtime contract 整理` に限定する。

## 今回の終了線

今回の wave を次で閉じる。

1. `RUNTIME-DBCLASSES` の actual generated tree と docs のズレを解消する
2. `dbaccess-*` を root wrapper + `base/*Base.php` へ寄せる
3. canonical 生成できる `data-*` を root wrapper + `base/*Base.php` へ寄せる
4. current tests / self-loop / docs wording をこの contract に揃える
5. `non-plain` の残件は `sample-gated residual` として明示したまま閉じる

言い換えると、今回の終了線は次である。

- `generated runtime の file contract が current repo 内で一貫する`
- `Phase 2 self-host へ入る前の土台が揃う`
- `まだ残すもの` と `今回閉じるもの` が文書上も実装上も一致する

## In Scope

### [IN / M1] 1. runtime contract の truth normalization

- `2026-0518` の Phase 0 を完了させる
- actual generated tree を再確認し、docs / implementation / emitted layout の食い違いを無くす
- source of truth を 1 つに固定する

### [IN / M2] 2. DBACCESS wrapper/base migration

- `2026-0518` の Phase 1 を今回の main deliverable にする
- `dbaccess-*` を root wrapper + `base/dbaccess-*Base.php` へ寄せる
- legacy delegate は一時維持可だが、contract は wrapper/base に揃える

### [IN / M3] 3. canonical generated data-* wrapper/base migration

- `2026-0518` の Phase 2 を今回の main deliverable にする
- canonical 生成できる plain / generated subset の `data-*` を wrapper/base contract に寄せる
- `non-plain bootstrap` 全件の解消までは要求しない

### [IN / Close] 4. verification / docs / status freeze

- `check_mtool_self_loop.php`
- `make mtool-self-loop-check`
- 必要な focused test
- docs / report / resume wording
- 一般/permanent docs の日英併記化

この 4 点をもって「今回ここまでで閉じる」を成立させる。

## Out Of Scope

今回の終了線には入れない。

### [OUT] 1. self-host / authoritative runtime switch

- generated runtime を authoritative source にすること
- generated runtime へ差し替えて app 全体を回すこと
- `2026-0514` の Phase 2 は次 wave

### [OUT] 2. non-plain bootstrap data-* の全面圧縮

- `2026-0518` の Phase 3 全件完了
- top-level helper / 複数 class / default property を含む重い残件の全面移行
- 今回は `sample-gated residual` のままでよい

### [OUT] 3. runtime loader / custom bootstrap の大整理

- `_runtime_loader.php` 相当の責務整理
- custom extension layer contract の再設計
- `2026-0518` の Phase 4 は次 wave

### [OUT] 4. metadata / UI / docs cleanup の深掘り

- strategy rename
- runtime source output caption の全面整理
- `2026-0518` の Phase 5 は次 wave

### [OUT] 5. page security / host assignment / HTML / Source Output bridge debt の最終解消

- これは self-host Phase 2 の gate 管理に残す
- 今回は blocker inventory を明示できれば十分

### [OUT] 6. residual 6 class の最終分類

- `CompareOutputSearchCache`
- `ProjectSourceOutputSavedFiles`
- `TestCondition`
- `TestPatternSelection`
- `UploadDropboxPathCache`
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da`

これは upstream 整備後の検証点として保持し、今回の first closure 対象にしない。

### [OUT] 7. LanguageResource debug / bridge cleanup

- core cutover は `DONE`
- 残る cleanup は broad rewrite の終了線を作る上で必須ではない

## 受け入れ条件

今回の wave を閉じてよい条件は次である。

1. `RUNTIME-DBCLASSES` の actual emitted layout を docs で正しく説明できる
2. `dbaccess-*` が wrapper/base contract へ揃う
3. canonical generated `data-*` が wrapper/base contract へ揃う
4. representative self-loop / digest / docs check が green
5. `non_plain_items` は残っていてよいが、`sample-gated residual` として説明可能である
6. 「次 wave で何をやるか」が out-of-scope として固定されている
7. current general/permanent docs は日英併記方針に揃い、progress/handoff docs は日本語のみ運用で区別できる

## 実施順

1. `M1 / 2026-0518 Phase 0`: actual tree と docs の truth normalization
2. `M2 / 2026-0518 Phase 1`: `DBACCESS` wrapper/base 化
3. `M3 / 2026-0518 Phase 2`: canonical generated `data-*` wrapper/base 化
4. `Close`: focused verification と digest baseline 更新
5. `Close`: progress snapshot / resume prompt / plan status 更新

## この wave を閉じた時の意味

この終了線を超えると、broad rewrite の現在地は次のように読める。

- `機能移植完了` を閉じるための runtime contract 整理は完了
- `self-host / runtime 置換完了` は未着手ではなく、次 wave の明確な対象
- residual / bridge / cleanup は「今残っているが、今回の終了条件ではない」と説明できる

## 次 wave の入口

今回を閉じた後に着手する候補は次の順でよい。

1. `non-plain bootstrap data-*` の圧縮
2. runtime loader / custom bootstrap の整理
3. self-host / authoritative runtime switch
4. page security / host assignment / HTML / Source Output bridge debt の最終吸収
5. residual 6 class の最終分類
6. LanguageResource debug / bridge cleanup
