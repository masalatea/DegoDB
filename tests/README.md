# Test Guide / テストガイド

新実装側の検証資産は `tests/` にまとめる。

- 自動テスト本体は PHPUnit を基準に `tests/Integration/` を起点に置き、必要に応じて `tests/Unit/`、`tests/Functional/` などへ広げる
- Docker を使う検証 scenario は `tests/scenarios/` に置く
- seed fixture や手動確認用の補助資産も、運用用の `sample/` ではなく `tests/` 側へ寄せる
- `tests/fixtures/legacy-dbclasses/` には sample / migration test が使う curated legacy dbclasses copy を置く。`original-codes/` は Docker に mount せず、test input としても直接使わない。
- runtime 置換は 2 段で進める。`Sample1` / `Sample2` / `Sample3` / `Sample4` / `Sample5` / `Sample6` / `Sample7` / `Sample8` / `Sample09` / `Sample10` は tutorial runtime lane、`sample/internal-patterns/pattern01-14-*` は complex/new form の sample gate として扱う。historical pattern test の `Sample9-22` 命名は互換 layer として残しつつ、tutorial 側の 2 桁 sample は `Sample09+` を使って衝突を避ける。

現時点では次を持つ。

- `tests/Integration/Sample1SimpleTableOutputTest.php`
  - `sample01-simple-table-runtime` の import / sync / output / reference compare を一括検証する
- `tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php`
  - `sample02-dataclass-nullable-default-status` の import / sync / dataclass output / reference compare を一括検証する
- `tests/Integration/Sample3DataclassLookupAndHelperOutputTest.php`
  - `sample03-dataclass-lookup-and-helper` の複数 lookup table import / sync / dataclass output / reference compare を一括検証する
- `tests/Integration/Sample4DataclassParentChildBasicOutputTest.php`
  - `sample04-dataclass-parent-child-basic` の親子 2 table import / sync / dataclass output / reference compare を一括検証する
- `tests/Integration/Sample5DbAccessSelectBasicOutputTest.php`
  - `sample05-dbaccess-select-basic` の 1 table import / sync / 1 class / 1 select function の db access output / reference compare を一括検証する
- `tests/Integration/Sample6DbAccessFilterSortPageOutputTest.php`
  - `sample06-dbaccess-filter-sort-page` の 1 table import / sync / filter / sort / limit 付き db access output / reference compare を一括検証する
- `tests/Integration/Sample7DbAccessCrudBasicOutputTest.php`
  - `sample07-dbaccess-crud-basic` の 1 table import / sync / insert / update / delete metadata 付き db access output / reference compare を一括検証する
- `tests/Integration/Sample8DbAccessJoinReadModelOutputTest.php`
  - `sample08-dbaccess-join-read-model` の 2 live table + 1 read model table import / sync / joined select metadata 付き db access output / reference compare を一括検証する
- `tests/Integration/Sample09DbAccessAggregateReportOutputTest.php`
  - `sample09-dbaccess-aggregate-report` の 2 live table + 1 report model table import / sync / grouped select + having metadata 付き db access output / reference compare を一括検証する
- `tests/Integration/Sample10DbAccessMiniCrudFlowOutputTest.php`
  - `sample10-dbaccess-mini-crud-flow` の 1 table import / sync / list/detail/create/update/delete metadata 付き db access output / reference compare を一括検証する
- `tests/Integration/Sample9TestPatternDefaultPropertyOutputTest.php`
  - `pattern01-default-property-split` の legacy dataclass wrapper/base migration output を検証する
- `tests/Integration/Sample10CompareOutputCompanionDeclarationsOutputTest.php`
  - `pattern05-companion-declarations-basic` の legacy dataclass + enum type class migration output を検証する
- `tests/Integration/Sample11DaDataclassMethodOnlyOutputTest.php`
  - `pattern03-method-only-split` の legacy dataclass method-only migration output を検証する
- `tests/Integration/Sample12DbtablecolumnsWrapperPropertyOutputTest.php`
  - `pattern02-wrapper-property-helper` の wrapper-property + helper-method migration output を検証する
- `tests/Integration/Sample13ReqMethodAndEnumOutputTest.php`
  - `pattern04-method-and-enum-basic` の method+enum migration output を検証する
- `tests/Integration/Sample14BuildSourceFuncCacheCompanionDeclarationsOutputTest.php`
  - `pattern08-companion-declarations-multi-helper` の 3-class companion declarations migration output を検証する
- `tests/Integration/Sample15BuildLogCompanionDeclarationsOutputTest.php`
  - `pattern06-companion-declarations-no-top-level` の no-top-level companion declarations migration output を検証する
- `tests/Integration/Sample16LiveCheckResultCompanionDeclarationsOutputTest.php`
  - `pattern07-companion-declarations-multiclass` の 3-class no-top-level companion declarations migration output を検証する
- `tests/Integration/Sample17SpecContentTopLevelDeclarationOutputTest.php`
  - `pattern09-top-level-declaration-single` の 1-class top-level declaration migration output を検証する
- `tests/Integration/Sample18ProjectUserTopLevelDeclarationOutputTest.php`
  - `pattern10-top-level-declaration-multiclass` の 3-class top-level declaration migration output を検証する
- `tests/Integration/Sample19HtmlTemplateTopLevelDeclarationOutputTest.php`
  - `pattern11-top-level-declaration-html-template` の 4-class top-level declaration migration output を検証する
- `tests/Integration/Sample20DaCustomProxyMethodAndEnumOutputTest.php`
  - `pattern12-method-and-enum-no-top-level` の no-top-level method-and-enum migration output を検証する
- `tests/Integration/Sample21ProjectMethodAndEnumOutputTest.php`
  - `pattern13-method-and-enum-multimethod` の multi-method + top-level helper method-and-enum migration output を検証する
- `tests/Integration/Sample22ProjectSourceOutputMethodAndEnumOutputTest.php`
  - `pattern14-method-and-enum-heavy-multimethod` の heavy multi-method + top-level helper method-and-enum migration output を検証する
- `tests/Integration/LegacyTopLevelDeclarationMigrationTest.php`
  - top-level helper / declaration lane の wrapper/base migration output を検証する
- `tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - self-generated runtime bundle を runtime resolver が読めることを no-DB fixture で検証する
- `tests/Integration/RuntimeReferencePromotionTest.php`
  - self-generated artifact を promoted runtime reference へ昇格する contract、durable snapshot capture、snapshot restore 時の metadata 混入防止を検証する
- `tests/Integration/RuntimeReplacementRolloutLaneTest.php`
  - current runtime replacement lane と sample gate mapping、reference manifest に保存された rollout metadata / promoted artifact provenance が崩れていないことを検証する
- `tests/Integration/SamplePackCatalogTest.php`
  - sample pack の category 分離、pack order、category guide README、共通 runner 名、file-based sample の curated fixture 入力、LanguageResource root 解決、`pattern01-14` に対する dedicated output test coverage が current layout から外れていないことを検証する
- `tests/Integration/DocsEntranceContractTest.php`
  - `README.md`、`docs/start-here.md`、`docs/README.md`、`docs/choose-your-path.md`、`docs/internal/README.md`、`docs/internal/repo-boundaries.md`、`docs/current-supported-workflow.md`、`docs/common-tasks.md`、`docs/glossary.md` の入口リンク、top-level external surface、internal docs index、boundary wording、sample lane 表記、full suite command が current 導線から外れていないことを検証する
- `tests/Integration/LegacyProjectSampleCatalogTest.php`
  - remaining legacy project packs の canonical project key、project/source output seed、resource manifest contract が current catalog と一致していることを検証する
- `tests/Integration/RuntimeReferenceStatusTest.php`
  - promoted runtime reference と latest runtime artifact の status 判定が `up-to-date` / `stale-reference` / provenance 欠落 / durable snapshot-only / artifact history 消失で崩れないことを検証する
- `tests/scenarios/mtool-single-proxy/`
  - `Project 1 = MTOOL` 上で single-function proxy generator を補助検証する

現時点の標準 runner は PHPUnit とし、Codeception を入れる場合でも root は `tests/` のまま維持する。

補助チェック:

- `make sample-pack-compose-smoke`
  - active runtime sample pack の `compose.yaml` override が root `compose.yaml` と merge でき、期待 services を解決できることを host-side で軽く検証する
- `make sample-pack-runtime-smoke`
  - representative runtime sample pack (`sample51-runtime-sql-server`) を `up -> apply-seed -> /health -> project row / source output row check` まで軽く検証する
- `make mtool-external-source-lab-smoke`
  - admin UI の `/settings/database-sources` で一時 external named source を作成し、`named-live-schema:{source_key}` import、`OPENAPI-JSON` / `DBTABLE-PROXY-SERVER` publish、lab の Swagger page load と published proxy route を localhost で確認してから source を削除する

基本実行:

```bash
make test
```

補足:

- `make test` と `make sampleNN-pack-runtime-test` は sample stack を検証後に `down -v` する
- 片付けずに中を見たい時だけ `KEEP_SAMPLE_STACK_RUNNING=1 make test` あるいは `KEEP_SAMPLE_STACK_RUNNING=1 make sample01-pack-runtime-test` を使う
