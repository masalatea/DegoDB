# 2026-05-15 Language Resource Seed Overlay Split

## 概要

- `ProjectSourceOutput(ClassType=LanguageResource)` の bootstrap seed を core `mtool/docker/mariadb/config-seed/` から外した。
- `mtool/docker/mariadb/config-seed-language-resource/` を追加し、LanguageResource source output 10 本は optional overlay としてだけ復元する形へ切り替えた。
- `LanguageResource*` DBAccess class/function/designer seed も optional overlay 側へ寄せ、core `019` / `020` から切り離した。
- `mtool/docker/compose/02_mtool_language_resource.compose.yaml` を追加し、`01_mtool.compose.yaml` の上に重ねたときだけ overlay seed を fresh initdb へ staged copy するようにした。
- `mtool/scripts/apply_config_sample_seed.sh` も `--scenario=01_mtool_language_resource` を理解するようにした。

## 変更点

- core seed
  - `mtool/docker/mariadb/config-seed/030_project1_language_resource_source_output_seed.sql`
  - `mtool/docker/mariadb/config-seed/031_project1_language_resource_source_output_placeholder_override.sql`
  - `mtool/docker/mariadb/config-seed/020_project_db_access_designer_seed.sql`
  - 上記 core files から LanguageResource 向け seed を外した。
- optional overlay
  - `mtool/docker/mariadb/config-seed-language-resource/030_project1_language_resource_source_output_seed.sql`
  - `mtool/docker/mariadb/config-seed-language-resource/031_project1_language_resource_source_output_placeholder_override.sql`
  - `mtool/docker/mariadb/config-seed-language-resource/035_project1_language_resource_db_access_class_function_seed.sql`
  - `mtool/docker/mariadb/config-seed-language-resource/036_project1_language_resource_db_access_designer_seed.sql`
  - `mtool/docker/mariadb/config-seed-language-resource/README.md`
  - LanguageResource parity bridge source output 10 本と legacy DBAccess metadata を sibling overlay pack へ移した。
- compose / helper
  - `mtool/docker/compose/02_mtool_language_resource.compose.yaml`
  - `mtool/scripts/apply_config_sample_seed.sh`
  - default `01_mtool` の上に overlay を足す経路を追加した。
- docs
  - `mtool/docker/README.md`
  - `mtool/docker/mariadb/config-seed/README.md`
  - `docs/internal/source-output-path-policy.md`
  - `docs/internal/language-resource-separation.md`
  - `docs/internal/mtool-admin-roadmap.md`

## 期待する状態

- `docker compose -f compose.yaml -f mtool/docker/compose/01_mtool.compose.yaml up -d`
  - core `MTOOL` が LanguageResource source output / DBAccess metadata なしで起動する。
- `docker compose -f compose.yaml -f mtool/docker/compose/01_mtool.compose.yaml -f mtool/docker/compose/02_mtool_language_resource.compose.yaml up -d`
  - legacy parity 用 LanguageResource source output 10 本と DBAccess metadata が復活する。

## verification

- `bash -n mtool/scripts/apply_config_sample_seed.sh`
- scratch DB 上で core schema + core seed を適用し、`project_source_outputs WHERE class_type='LanguageResource'` が `0` 件になることを確認する。
- 同じ scratch DB へ overlay を追加適用し、LanguageResource source output が `10` 件、`project_db_access_classes.source_name LIKE 'LanguageResource%'` が `7` 件、function/designer row も所定件数で戻ることを確認する。

## 残り

- scratch DB で core-only / core+overlay の実適用確認を残している。
- 次段では、helper contract と current page / route 側への吸収範囲を切り分ける。
