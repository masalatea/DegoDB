# 2026-06-20 Generated Name Migration Plan

## Status

- status: `FIRST_SLICE_APPLIED / READY_FOR_POSTGRESQL_CONTINUATION`
- created: `2026-06-20 JST`
- purpose: Mtool self-hosting と sample 群を壊さずに、physical / logical / generated naming へ一括移行できるようにする

## Background

PostgreSQL output support の first slice で、未引用 identifier は lower-case catalog name として扱われることを確認した。Mtool は double-quoted PostgreSQL SQL を標準にしないため、DB physical name と生成物名を分ける必要がある。

ただし、これは PostgreSQL だけの問題ではない。Mtool 自体が Mtool の出力したコードで動いているため、命名規則変更は user DB support の局所修正ではなく、self-host generated artifacts の移行問題として扱う必要がある。

## Priority Decision

PostgreSQL continuation was paused until this migration lane had a reproducible first slice. That first slice is now applied for the Mtool runtime reference and sample metadata fallout that is needed before resuming PostgreSQL output work.

先に作るもの:

1. 現行ルールで生成した output snapshot。
2. ルール変更後に同じ入力から生成した output snapshot。
3. snapshot 同士の file / symbol / keyword diff。
4. conflict report。
5. rollback して一括変換を再実行できる運用。

## Core Principle

一括変換後に動かない場合、成果物を場当たり修正しない。

Failure handling:

1. 失敗箇所を変換ルールまたは生成ルールの欠陥として記録する。
2. 作業ツリーを rollback する。
3. keyword map / symbol map / generator rule を修正する。
4. before snapshot から一括変換を再実行する。
5. 同じ tests / smoke を再実行する。

例外的に手で扱うもの:

- docs の自然文。
- migration 説明で旧名を意図的に残す箇所。
- 手書き運用補助スクリプト。
- 変換ルールでは表現できない互換レイヤー。

## Naming Model

Mtool の生成物は、次の名前を明示的に分ける。

- `physical_name`: DB catalog や file system 上で実際に使う名前。
- `logical_name`: Mtool 内部の概念名。
- `generated_name`: PHP / OpenAPI / route / class / property など出力 surface の名前。

Generated SQL must use `physical_name`.

Generated PHP / OpenAPI / docs examples may use `logical_name` / `generated_name` depending on the target surface.

## Naming Policy Decisions

These decisions are intentionally cross-DB. PostgreSQL exposed the issue, but the policy must apply to MySQL / MariaDB / SQLite / PostgreSQL consistently so Mtool does not grow dialect-only naming exceptions.

### Physical DB Name Style

Decision: new schemas, new samples, and new docs should recommend `snake_case` physical names.

Examples:

- `support_ticket`
- `updated_at`
- `project_user`

Considered alternatives:

- Keep mixed-case physical names such as `SupportTicket` as the recommended style.
  - Rejected because PostgreSQL unquoted identifiers fold to lower case, and Mtool does not want to standardize on double-quoted PostgreSQL SQL.
- Convert every existing physical name immediately.
  - Rejected for the first slice because Mtool is self-hosted by generated artifacts and needs a before / after migration audit before mass rewrite.

### Mixed-Case Physical Names

Decision: mixed-case physical DB names are deprecated for new work.

Existing MySQL / MariaDB projects may continue to work through compatibility. PostgreSQL unquoted operation does not target mixed-case physical names because `SupportTicket` is observed as `supportticket`.

Considered alternatives:

- Quote PostgreSQL identifiers and preserve mixed case.
  - Rejected because double-quoted SQL is fragile with some tools and was explicitly ruled out.
- Add PostgreSQL-only case restoration rules.
  - Rejected because they would create dialect-specific behavior and repeated bugs.

### Lowercase Names Without Word Boundaries

Decision: do not guess missing word boundaries.

Examples:

- `supportticket -> Supportticket`
- `updatedat -> Updatedat`
- `support_ticket -> SupportTicket`
- `updated_at -> UpdatedAt`
- `htmlTemplate_leftouterjoin_ParentHtmlTemplate -> HtmlTemplateLeftouterjoinParentHtmlTemplate`
- `daCustomProxyFunc_leftouterjoin_dafunc_and_da -> DaCustomProxyFuncLeftouterjoinDafuncAndDa`
- `ID_token -> IDToken`

If a logical word boundary matters, the physical name must carry it with `_`.

Generation rule:

- `_` and other non-alphanumeric delimiters are explicit segment boundaries.
- each segment keeps its existing spelling.
- only the first character of each segment is uppercased for Pascal/class surfaces.
- existing Camel/Pascal/acronym spelling inside a segment is preserved.
- dictionary-based boundary insertion is not performed.
- camel/property surfaces are produced by lowercasing only the first character of the Pascal result.

Considered alternatives:

- Guess `supportticket -> SupportTicket` from dictionaries or known model names.
  - Rejected because it is non-deterministic, project-specific, and risky in self-host migrations.
- Keep a project-specific exception list from day one.
  - Rejected for the first slice. Exception maps may be introduced later only as explicit migration data, not hidden generator behavior.

### Reserved Words And Unsafe Unquoted Names

Decision: unsafe unquoted physical names should be detected by validation / warning before generation or import apply. The generator must not silently rename them.

Examples of unsafe names:

- `user`
- `order`
- `group`
- `SupportTicket`
- `support-ticket`
- `2support_ticket`

Considered alternatives:

- Automatically rename unsafe physical names.
  - Rejected because generated SQL must use physical names; silent renames would point generated code at different DB objects.
- Quote unsafe names.
  - Rejected for PostgreSQL because the chosen policy avoids double-quoted identifier SQL.

### Explicit Name Fields In Metadata

Decision: table and column metadata should carry all three names where the boundary matters.

```text
physical_name: support_ticket
logical_name: SupportTicket
generated_name: SupportTicket or supportTicket depending on surface
```

The legacy `name` field remains during compatibility phases. Existing import/apply code may continue to key by `name` until the migration audit proves the broader change is safe.

Considered alternatives:

- Keep only `name` and infer meaning from context.
  - Rejected because it is the source of SQL name vs generated name confusion.
- Replace `name` immediately everywhere.
  - Rejected because that would be a broad self-host migration without enough diff visibility.

### Existing Sample Migration

Decision: migrate samples in phases through generated artifact set comparison.

The preferred order is:

1. Representative dry run on `sample10-dbaccess-mini-crud-flow`.
2. Representative dry run on `sample08-dbaccess-join-read-model`.
3. Expand to conflict-free generated sample references.
4. Review docs keyword occurrences separately.

Considered alternatives:

- Rewrite all samples immediately by search/replace.
  - Rejected because docs, expected JSON, OpenAPI schema names, route paths, and generated PHP symbols need separate classification.
- Leave samples permanently in legacy style.
  - Rejected because samples should eventually demonstrate the recommended snake_case physical naming policy.

### Failure Handling

Decision: if a mass rewrite fails, rollback and rerun from the conversion pipeline.

Do not patch generated artifacts locally to make tests pass. A failure means the migration rule, keyword map, symbol map, or generator rule is wrong or incomplete.

Considered alternatives:

- Let AI debug and patch the failing generated files in place.
  - Rejected because the next regeneration would lose those fixes and hide defects in the conversion rules.

## Audit Targets

The migration audit must track at least:

- file path rename。
- class / interface / trait / enum rename。
- namespace rename。
- property rename。
- method rename。
- constant rename。
- OpenAPI schema rename。
- route path / endpoint rename。
- DB table physical / logical mapping。
- DB column physical / logical mapping。
- config key rename。
- docs keyword occurrence。

## First Slice Tooling

Added script:

```sh
python3 mtool/scripts/generated_name_migration_audit.py capture \
  --root=sample/tutorials/sample10-dbaccess-mini-crud-flow/reference \
  --output-root=work/generated-name-migration/sample10/before \
  --manifest-output=work/generated-name-migration/sample10/before-capture.json \
  --pretty

python3 mtool/scripts/generated_name_migration_audit.py index \
  --root=work/generated-name-migration/sample10/before \
  --output=work/generated-name-migration/sample10/before-index.json \
  --pretty

python3 mtool/scripts/generated_name_migration_audit.py index \
  --root=work/generated-name-migration/sample10/after \
  --output=work/generated-name-migration/sample10/after-index.json \
  --pretty

python3 mtool/scripts/generated_name_migration_audit.py compare \
  --before=work/generated-name-migration/sample10/before-index.json \
  --after=work/generated-name-migration/sample10/after-index.json \
  --keyword-map=work/generated-name-migration/sample10/keyword-map.json \
  --output=work/generated-name-migration/sample10/compare.json \
  --pretty

python3 mtool/scripts/generated_name_migration_audit.py transform \
  --root=work/generated-name-migration/sample10/before \
  --output-root=work/generated-name-migration/sample10/after \
  --keyword-map=work/generated-name-migration/sample10/keyword-map.json \
  --manifest-output=work/generated-name-migration/sample10/after-transform.json \
  --pretty

python3 mtool/scripts/generated_name_migration_audit.py derive-keyword-map \
  --before=work/generated-name-migration/sample10/before-index.json \
  --after=work/generated-name-migration/sample10/after-index.json \
  --output=work/generated-name-migration/sample10/keyword-map-candidates.json \
  --pretty

python3 mtool/scripts/generated_name_migration_audit.py derive-keyword-map-samples \
  --before-root=work/generated-name-migration/before-all/samples \
  --after-root=work/generated-name-migration/before-all/samples \
  --output=work/generated-name-migration/before-all/sample-keyword-map-candidates.json \
  --pretty

python3 mtool/scripts/generated_name_migration_audit.py scan-keywords \
  --root=docs \
  --keyword-map=work/generated-name-migration/sample10/keyword-map.json \
  --output=work/generated-name-migration/sample10/docs-keywords.json \
  --pretty
```

All sample before snapshots can also be captured through Make:

```sh
make generated-name-migration-capture-samples-before \
  GENERATED_NAME_MIGRATION_RUN_ID=before-all

make generated-name-migration-capture-samples-after \
  GENERATED_NAME_MIGRATION_RUN_ID=before-all

make generated-name-migration-validate-sample-keyword-map \
  GENERATED_NAME_MIGRATION_RUN_ID=before-all \
  GENERATED_NAME_MIGRATION_KEYWORD_MAP=work/generated-name-migration/before-all/keyword-map.json

make generated-name-migration-transform-samples-after \
  GENERATED_NAME_MIGRATION_RUN_ID=before-all \
  GENERATED_NAME_MIGRATION_KEYWORD_MAP=work/generated-name-migration/before-all/keyword-map.json

make generated-name-migration-compare-samples \
  GENERATED_NAME_MIGRATION_RUN_ID=before-all

make generated-name-migration-derive-keyword-map \
  GENERATED_NAME_MIGRATION_RUN_ID=before-all \
  SAMPLE=sample10-dbaccess-mini-crud-flow

make generated-name-migration-derive-sample-keyword-map \
  GENERATED_NAME_MIGRATION_RUN_ID=before-all

make generated-name-migration-scan-sample-keywords \
  GENERATED_NAME_MIGRATION_RUN_ID=before-all \
  GENERATED_NAME_MIGRATION_KEYWORD_MAP=work/generated-name-migration/before-all/keyword-map.json
```

The derived keyword map is a candidate list, not an automatically approved rewrite rule. Use single-sample derivation for focused dry runs and all-sample derivation to find shared rename rules. Review `candidates[*].evidence` before passing its `keywords` section to transform, compare, or scan.

The keyword scan report includes both raw `occurrences` and `summary.by_keyword` / `summary.by_file` counts. Use the summaries to choose the next replacement batch; use raw occurrences when reviewing exact docs, seed SQL, generated PHP, or OpenAPI lines.

The first slice is intentionally non-mutating toward repo source files. It copies or transforms snapshots under `work/` and produces JSON reports, but does not edit generated artifacts in place.

## Naming Helper First Slice

Implemented:

- `mtool/app/generated_name.php`
  - `app_physical_name_to_logical_name()`
  - `app_logical_name_to_generated_name()`
  - `app_generated_name_map_for_physical_name()`
  - `app_generated_name_policy_uses_physical_logical_names()`
  - `app_physical_name_is_safe_unquoted_sql_identifier()`
- `tests/Integration/GeneratedNameTest.php`
  - `support_ticket -> SupportTicket`
  - `updated_at -> UpdatedAt`
  - `SupportTicket -> SupportTicket`
  - `supportticket -> Supportticket`
  - generated class surface and PHP property / parameter surface
  - unquoted SQL identifier safety policy
- `mtool/scripts/generated_name_migration_audit.py`
  - captures and indexes single-sample and all-sample snapshots.
  - compares before / after indexes with an explicit keyword map.
  - validates keyword maps against before snapshots before transform.
  - transforms snapshots into an after tree with an explicit keyword map, including path and text replacement.
  - fails on transform path collisions instead of overwriting generated files.
  - derives candidate keyword maps from before / after file and symbol differences.
  - scans docs or sample trees for old keyword occurrences.
- `mtool/app/project_table_import_source.php`
  - keeps existing `name` fields for compatibility.
  - adds `physical_name`, `logical_name`, and `generated_name` to imported table and column metadata.
- `mtool/app/project_table_import_service.php`
  - exposes unsafe unquoted physical names as import plan warnings.
  - counts unsafe table / column physical names without silently renaming or blocking existing MySQL-compatible metadata.
- `mtool/app/table_metadata_repository_pdo.php`
  - persists `physical_name` for tables and columns while keeping `name` compatible.
  - exposes derived `physical_name`, `logical_name`, and `generated_name` in table / column metadata snapshots.
- `mtool/app/data_class_repository_pdo.php`
  - persists `physical_name` for DataClass and fields while keeping `name` compatible.
  - exposes derived `physical_name`, `logical_name`, and `generated_name` in DataClass / field metadata snapshots.
- `docker/mariadb/config-initdb/033_generated_name_physical_names.sql`
  - adds `physical_name` columns to `dbtable`, `dbtablecolumns`, `dataclass`, and `dataclassfields`.
  - backfills existing rows from `name`.
- `mtool/app/project_table_import_service.php`, `mtool/app/project_data_class_sync_service.php`, and `mtool/app/project_metadata_bundle.php`
  - carry `physical_name` through import, sync, and bundle import paths.
- `mtool/app/project_metadata_bundle.php`
  - exports `physical_name` for table / column / DataClass / field metadata so bundle round trips do not collapse physical names back into `name`.
- `mtool/app/project_output_data_class_generator.php`
  - keeps default output unchanged.
  - adds opt-in `MTOOL_GENERATED_NAME_POLICY=physical-logical-v1` handling for DataClass file / class / property names.
- `mtool/app/project_output_db_access_generator.php`
  - keeps default output unchanged.
  - adds opt-in `MTOOL_GENERATED_NAME_POLICY=physical-logical-v1` handling for DBAccess PHP file / class / argument source-name fragments.
  - SQL table / column names remain physical names from DBAccess designer metadata.
- `mtool/app/project_output_proxy_generator.php`
  - keeps default output unchanged.
  - adds opt-in `MTOOL_GENERATED_NAME_POLICY=physical-logical-v1` handling for single-function proxy source-name fragments, function-name fragments, endpoint filenames, and generated handler / request / result class stems.
  - custom proxy basename / name handling remains unchanged in this slice because those names are user-defined proxy artifact names rather than DB physical names.
- `mtool/app/project_output_openapi_generator.php`
  - keeps default output unchanged.
  - adds opt-in `MTOOL_GENERATED_NAME_POLICY=physical-logical-v1` handling for OpenAPI component schema names, schema titles, field property names, and `$ref` targets.
  - endpoint filenames / paths remain tied to proxy output filenames.
- `tests/Integration/GeneratedNamePolicyOutputTest.php`
  - verifies DataClass output naming stays unchanged by default.
  - verifies opt-in DataClass output can use `physical_name` for class and property generated names.
  - verifies DBAccess output naming stays unchanged by default and can opt into generated names.
  - verifies single-function proxy output naming stays unchanged by default and can opt into generated source / function / endpoint / handler names.
- `tests/Integration/OpenApiSourceOutputContractTest.php`
  - verifies existing OpenAPI output stays unchanged by default.
  - verifies opt-in OpenAPI schema / property / `$ref` names can be generated from snake_case `physical_name`.

This is a compatibility-preserving first slice. Existing import/apply code still keys by `name`; the new fields make physical/logical/generated naming visible and preserve the original physical source name before any mass rewrite or generated output behavior change.

Current limitation: default generated output remains unchanged. `physical_name` is now persisted, but broad sample output changes should still be driven by before/after snapshots and a generated keyword map rather than hand-editing generated files. OpenAPI path / endpoint filename renames are tied to the proxy runtime endpoint filenames and must be validated in the same batch.

## Keyword Map Shape

The scanner accepts either object form:

```json
{
  "Supportticket": "SupportTicket",
  "updatedat": "updated_at"
}
```

or list form:

```json
[
  {"old": "Supportticket", "new": "SupportTicket"},
  {"old": "updatedat", "new": "updated_at"},
  {"old": "da", "new": "Da", "mode": "identifier-prefix"}
]
```

`mode` is optional. Default is `literal`.

- `literal`: raw string replacement.
- `identifier-prefix`: replace only when the old value starts at an identifier boundary and ends before a non-identifier, end-of-string, or uppercase continuation. This is for short legacy source names such as `da` and `html`; it allows `data-da.php -> data-Da.php` and `class daData -> class DaData`, but does not rewrite `data-*` or `SpecialHoliday`.

The keyword map can be derived from before / after symbol differences, then reviewed before use. It can also be hand-curated for representative dry runs.

`scan-keywords` uses the same `mode` rule as transform, so boundary-aware entries such as `da -> Da` do not produce broad false positives from unrelated words.

## Rollout Plan

1. Build the read-only audit lane.
2. Add compatibility-preserving generated naming helper and table import name tracking.
3. Capture Mtool self-host before / after snapshots.
4. Produce file / symbol / keyword diff reports.
5. Add conflict classification.
6. Run representative dry run on `sample10-dbaccess-mini-crud-flow` and `sample08-dbaccess-join-read-model`.
7. Expand to sample pack generated references where conflicts are zero.
8. Only then resume PostgreSQL output support. Current status: ready to resume after the first slice.

## Representative Snapshot Status

Current before snapshots captured under `work/generated-name-migration/`:

| Target | Snapshot | File count | Symbol count | Notes |
|---|---:|---:|---:|---|
| `sample10-dbaccess-mini-crud-flow/reference` | `sample10-current/before` | 5 | 30 | CRUD representative; DataClass + DBAccess generated PHP. |
| `sample08-dbaccess-join-read-model/reference` | `sample08-current/before` | 9 | 37 | JOIN/read-model representative; multiple DataClass files + DBAccess generated PHP. |
| `sample/tutorials/*/reference` | `sample-all-current` / `codex-before-all-smoke` | 255 | 1065 | All 26 tutorial samples captured and indexed through the batch lane. |

These are current-rule snapshots only. The next migration step is to generate corresponding `after` snapshots with the new naming rules, then run `compare` with an explicit keyword map.

Batch compare smoke:

- run id: `codex-compare-zero-smoke`
- before source: current `sample/tutorials/*/reference`
- after source: current `sample/tutorials/*/reference`
- result: `ok=true`
- sample count: `26`
- file rename / add / remove: `0 / 0 / 0`
- symbol rename / add / remove: `0 / 0 / 0`
- conflict count: `0`

This proves the all-sample before / after compare lane is stable before generator behavior changes are introduced.

Batch transform smoke:

- run id: `codex-transform-target-smoke`
- before source: current `sample/tutorials/*/reference`
- transform source: `work/generated-name-migration/codex-transform-target-smoke/samples/*/before`
- keyword map: smoke-only `SupportTicket -> GeneratedSupportTicket`
- sample count: `26`
- file count: `255`
- transformed path / text count: `4 / 4`
- compare result: `ok=true`
- file rename / add / remove: `4 / 0 / 0`
- symbol rename / add / remove: `17 / 0 / 0`
- conflict count: `0`

This proves a reviewed keyword map can produce an isolated after snapshot and the compare lane can track file / symbol renames without editing repo sample references.

Keyword map validation smoke:

- run id: `codex-transform-target-smoke`
- target: `work/generated-name-migration/codex-transform-target-smoke/samples/*/before`
- keyword map: smoke-only `SupportTicket -> GeneratedSupportTicket`
- result: `ok=true`
- sample count: `26`
- validation totals: `path_change_count=4`, `text_occurrence_count=56`, `error_count=0`, `warning_count=0`

Collision validation smoke:

- fixture: `codex-validation-samples-smoke`
- keyword map: `Foo -> Baz`, `Bar -> Baz`
- result: `ok=false`
- detected error: `path_collision` for `Foo.php` and `Bar.php` to `Baz.php`

This keeps invalid keyword maps from reaching the transform step.

Mtool runtime reference dry run:

- target: `mtool/reference/dbclasses`
- before snapshot: `work/generated-name-migration/mtool-runtime-reference-current/before`
- file count: `499`
- symbol count: `2132`
- full generated-name candidate map before segment-preserving rule:
  - entries: `25`
  - result: `ok=false`
  - reason: chained replacements from broad short keywords such as `da -> Da` and `html -> Html`
  - observed bad transform examples before strict validation: `data-* -> Data-*`, `SpecialHoliday -> SpecialHoliDay`
- segment-preserving full candidate map:
  - entries: `25`
  - result: `ok=false`
  - reason: chained replacement from broad short keyword `da -> Da`
- segment-preserving safe-first candidate map:
  - entries: `23`
  - excluded: `da -> Da`, `html -> Html`
  - validation: `ok=true`, `path_change_count=125`, `text_occurrence_count=3591`, `warning_count=13`
  - transform: `file_count=499`, `path_changed_count=125`, `text_changed_count=138`
  - compare: `ok=true`, `file_rename/add/remove=125/0/0`, `symbol_rename/add/remove=511/0/0`, `conflict_count=0`
  - examples now preserve existing segment casing:
    - `htmlTemplate_leftouterjoin_ParentHtmlTemplate -> HtmlTemplateLeftouterjoinParentHtmlTemplate`
    - `daCustomProxyFunc_leftouterjoin_dafunc_and_da -> DaCustomProxyFuncLeftouterjoinDafuncAndDa`
    - `minutes_and_RelatedTables -> MinutesAndRelatedTables`
- segment-preserving boundary-aware candidate map:
  - entries: `25`
  - `da -> Da` and `html -> Html` use `mode=identifier-prefix`
  - validation: `ok=true`, `path_change_count=166`, `text_occurrence_count=8861`, `warning_count=27`
  - transform: `file_count=499`, `path_changed_count=166`, `text_changed_count=177`
  - compare: `ok=true`, `file_rename/add/remove=166/0/0`, `symbol_rename/add/remove=679/0/0`, `conflict_count=0`
  - verified guard examples:
    - `data-SpecialHoliday.php` remains unchanged.
    - `data-da.php -> data-Da.php`
    - `dbaccess-html.php -> dbaccess-Html.php`

Applied result:

- `mtool/reference/dbclasses` was synchronized from the boundary-aware after tree.
- The first sync used timestamp/size based `rsync -a`; this left 9 same-size/same-timestamp symbol changes stale, so the final sync used `rsync -a --delete --checksum`.
- post-apply index compare against the after tree:
  - files: `added=0`, `removed=0`, `renames=0`
  - symbols: `added=0`, `removed=0`, `renames=0`
  - conflicts: `0`
- PHP lint over applied `mtool/reference/dbclasses/**/*.php`: `ok`
- generated self-loop artifact `20260620-144636-48106902` compared against applied reference:
  - files: `499` unchanged
  - symbols: `2132` unchanged
  - conflicts: `0`
- `mtool/reference/mtool-self-loop-expected-output.json` was updated to the new boundary-aware baseline.

Sample/reference follow-up:

- The old all-sample smoke keyword map `SupportTicket -> GeneratedSupportTicket` was confirmed to be a tooling smoke only, not an approved migration rule.
- Applying that smoke map to repo sample references was rolled back rather than patched in place.
- `sample10-dbaccess-mini-crud-flow` remains on the current generated `SupportTicket` surface.
- `sample14-custom-proxy-runtime` reference was updated only for generated-name fallout from the Mtool runtime reference rename:
  - `dbtableDBAccess -> DbtableDBAccess`
  - `param_dbtable_ProjectPID_where -> param_Dbtable_ProjectPID_where`
- `sample15-project-metadata-export-import` and `sample26-ebook-headless-cms-capstone` metadata bundle references were updated to include `physical_name`.
- No bulk sample keyword rewrite has been approved beyond generated artifacts proven by before / after snapshots.

Verification:

- Use plain `make test` for normal verification.
- If a persisted log is needed, use one fixed command shape and path so the approval prompt is needed only once:
  - `make test > /tmp/dego-make-test.log 2>&1; rc=$?; echo test_status=$rc; tail -180 /tmp/dego-make-test.log; exit $rc`
- Do not switch between ad hoc per-sample targets for overnight verification, because command-shape changes can trigger repeated approval prompts.
- Latest plain `make test`: `OK (227 tests, 7928 assertions)`.
  - The skipped test is the opt-in live PostgreSQL sample12 contract when `MTOOL_RUNTIME_PGSQL_DSN` is not provided.
- `git diff --check`: `ok`.
- `make mtool-self-loop-check` now uses the MTOOL compose stack and passed after `make db-config-migrate-mtool`.

The self reference is now replaced and verified. The important operational lesson is still that short substring keyword maps are unsafe for real application unless they use an explicit boundary-aware mode.

Additional rule from the dry run:

- Do not use broad substring keyword maps for short identifiers such as `da`, `html`, `id`, or similarly common fragments.
- Chained replacement is an error, not a warning.
- Short legacy source names need either generator-driven after output or explicit `identifier-prefix` replacement, not raw literal text replacement.

## Sample Migration Strategy

Samples should be migrated by generated artifact set, not by hand-editing one sample at a time.

Expected flow:

1. Capture current sample generated/reference outputs.
2. Regenerate with new naming rules.
3. Compare file / class / property / method / schema / route names.
4. Auto-apply only conflict-free generated artifacts.
5. Use docs keyword scan for README and tutorial prose.
6. Run sample pack tests and runtime smoke.

Docs are not auto-rewritten blindly. Old names may be intentionally retained in migration notes, compatibility sections, or historical reports.

## Stop Lines

Do not proceed to mass rewrite until:

- file rename conflicts are zero for the selected target set.
- symbol rename conflicts are zero for generated PHP / JSON artifacts.
- docs keyword report is reviewed or explicitly classified.
- rollback and rerun command sequence is documented.
- representative sample runtime tests pass from a clean regenerated state.
