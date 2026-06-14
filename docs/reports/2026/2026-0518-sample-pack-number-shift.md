# 2026-05-18 Sample Pack Number Shift

## Summary

- `sample/` 配下の既存 sample pack をすべて `+1` し、`sample2-*` から `sample8-*` へ並び替えた。
- canonical project key も `SAMPLE1..7` から `SAMPLE2..8` へずらし、後続の simple `Sample 1` を入れる空きを作った。
- `LanguageResource` の file tree / legacy catalog は、旧 `SAMPLE2` / `SAMPLE4` / `SAMPLE6` から新 `SAMPLE3` / `SAMPLE5` / `SAMPLE7` へ移した。

## Changed Packs

- `sample1-sql-server` -> `sample2-sql-server` (`SAMPLE2`)
- `sample2-school-booking` -> `sample3-school-booking` (`SAMPLE3`)
- `sample3-whiteboard-reference` -> `sample4-whiteboard-reference` (`SAMPLE4`)
- `sample4-email-management` -> `sample5-email-management` (`SAMPLE5`)
- `sample5-simple-article-search` -> `sample6-simple-article-search` (`SAMPLE6`)
- `sample6-minutes` -> `sample7-minutes` (`SAMPLE7`)
- `sample7-misc` -> `sample8-misc` (`SAMPLE8`)

## Change

- pack directory 名、`compose.yaml` の project name、sample README の起動 path を新番号へ更新した。
- pack seed (`900_010_*` / `900_020_*`) の project key、project name、slug、output path、seed-local variable name を新番号へ更新した。
- `mtool/app/language_resource_file_catalog.php` と `mtool/app/legacy_language_resource_reference.php` の known sample project map を `SAMPLE3` / `SAMPLE5` / `SAMPLE7` へ更新した。
- `mtool/reference/sample*-legacy-language-resource-catalog.json` は `sample3` / `sample5` / `sample7` へ rename し、`project_key` を更新した。
- `mtool/resources/SAMPLE*` は `SAMPLE3` / `SAMPLE5` / `SAMPLE7` へ rename し、manifest の `project_key` を更新した。
- stable doc / runbook (`README.md`, `sample/README.md`, `docs/internal/source-output-path-policy.md`, `docs/internal/language-resource-separation.md`, `docs/internal/mtool-admin-roadmap.md`, `mtool/resources/README.md`, `mtool/scripts/apply_config_sample_seed.sh`) も新番号へ揃えた。

## Verification

- unwanted old pack name / old key が stable doc / current code / sample seed に残っていないことを検索で確認した。
- `sample/README.md` の一覧と root `README.md` の sample 起動例が `sample2-*` / `SAMPLE2..8` に揃っていることを確認した。
- `LanguageResource` map が `SAMPLE3` / `SAMPLE5` / `SAMPLE7` と新しい sample path / catalog path を指すことを確認した。

## Follow-up

- 次段で simple `Sample 1` を追加する。
- 追加後、`sample2-sql-server` README の役割説明など、`sample2-*` を暫定の先頭として書いた箇所が残っていれば再度中立化する。
