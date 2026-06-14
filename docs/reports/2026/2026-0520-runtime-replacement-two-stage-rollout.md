# 2026-05-20 Runtime Replacement Two-Stage Rollout

## 結論

- runtime 置換は、これ以降は 2 段で広げる。
- 第 1 段は simple form で、plain DTO、simple CRUD、既に current manifest/self-loop で一致確認が取れている形は `MTOOL` へ直接置換してよい。
- 第 2 段は complex/new form で、non-plain `data-*`、helper-heavy class、複数 declaration、未知の class/file contract は、専用 sample または focused integration を先に green にしてから `MTOOL` へ promote する。
- 2026-05-20 時点の「自身の Output で置き換える」進捗は、約 `70-75%` とみなす。

## この判断にした理由

- `project_output_runtime_generator.php` には `plain_data_candidate_count` / `non_plain_data_candidate_count` と legacy migration support flag 群が既に入っている。
- `project_output_service.php` もその support flag を見て runtime bundle の lane を切り替えている。
- full self-loop は self-generated artifact 入力と promoted default reference 入力の両方で通っており、default runtime reader も `self-generated-reference:canonical-dbaccess-partial-sql-regenerated` を自動判定する。
- したがって今の主問題は「自己生成できるか」ではなく、「どの形を即 promote してよく、どの形を sample gate に通すべきか」である。

## 2 段 rollout の運用

### 1. simple form は direct replacement

- plain DTO として property / parent 整合が取れている `data-*`
- simple CRUD / first-pass joined select など current SQL regenerate の coverage 内にある `dbaccess-*`
- 既に manifest 上で generated 扱いになり、self-loop を崩さない class / function

これらは dedicated sample を新設しなくても、manifest diff と既存 regression が green なら `MTOOL` runtime へ広げてよい。

### 2. complex/new form は sample gate 先行

- `default_property_value`
  - `tests/Integration/Sample9TestPatternDefaultPropertyOutputTest.php`
- companion declaration / enum type class
  - `tests/Integration/Sample10CompareOutputCompanionDeclarationsOutputTest.php`
- method-only `ADDITIONAL CLASS DEFINITION`
  - `tests/Integration/Sample11DaDataclassMethodOnlyOutputTest.php`
- wrapper-property + helper-method
  - `tests/Integration/Sample12DbtablecolumnsWrapperPropertyOutputTest.php`
- method + enum lane
  - `tests/Integration/Sample13ReqMethodAndEnumOutputTest.php`
- top-level helper / declaration lane
  - `tests/Integration/LegacyTopLevelDeclarationMigrationTest.php`

今後も、これらに似ない新形はまず sample で再現してから `MTOOL` へ横展開する。

## 進捗を `70-75%` とみる根拠

- self-generated artifact 入力でも promoted default reference 入力でも full self-loop が green
- current default mode が promoted self-generated runtime reference を読む
- `plain_data_candidate_count=63` / `non_plain_data_candidate_count=36` の両 lane が current runtime で吸収済み
- `bootstrap_data_class_count=0`
- `fallback_dbaccess_count=0`
- `legacy_delegate_function_count=0`
- `original-codes/` は Docker runtime から外れ、host-side reference only に固定済み

## まだ残っているもの

- current `MTOOL` baseline では runtime dbclasses 本体の simple lane はほぼ埋まっており、manifest 上も bootstrap copy / dbaccess fallback は残っていない
- file/blob parameter は `prepare()` + `bind_param("b")` + `send_long_data()` 契約を持つため、future に現れた場合はいまも explicit legacy-delegate のまま維持する
- `bootstrap_dbclasses.sh` は `work/` 配下 artifact だけでは durable rollback にならないため、まだ archive できない
- host-only helper / provenance metadata は整理が進んだが、legacy recovery 導線の縮退はまだ続く
- 今後見つかる complex/new form は、sample gate を通してから promote する必要がある

## 次の広げ方

1. simple form の未適用残件があれば、manifest/self-loop を崩さない範囲で `MTOOL` へ直接置換する
2. complex/new form の残件は、最小 sample 追加 -> `make test` green -> `MTOOL` promote の順で進める
3. `original-codes/` を runtime input に戻さない
