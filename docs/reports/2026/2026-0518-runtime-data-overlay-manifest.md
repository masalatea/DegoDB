# 2026-05-18 Runtime Data Overlay Manifest

## Summary

- `RUNTIME-DBCLASSES` の `runtime-generation-manifest.json` に `data_generation_items` を追加し、`data-*` 101 class それぞれの generation / bootstrap-copy decision と reason を残すようにした。
- latest self-loop artifact `20260517-231526-bae87642` では `data_entity_count=101` / `plain_data_candidate_count=64` / `non_plain_data_candidate_count=37` / `bootstrap_data_class_count=37` を確認した。
- 残る `data-*` 37 class は latest manifest 上すべて `non-plain-bootstrap` であり、hidden な plain DTO backlog はないことを確認した。

## Background

- constructor delegate 解消後、self-host の残件は `canonical_data_class_count=64` だけでは読めなくなっていた。
- 次段で必要なのは、`plain DTO なのにまだ currentize されていない class` と、`helper / multi-class / bootstrap helper を含むため plain currentization 対象外の class` を分けることだった。
- そのため、count だけでなく per-source reason を manifest に残し、self-loop でも aggregate count を読めるようにした。

## Change

- `mtool/app/project_output_runtime_generator.php`
  - `app_project_output_runtime_overlay_canonical_data_classes()` が `data_entity_count`、`plain_data_candidate_count`、`non_plain_data_candidate_count`、`bootstrap_data_class_count` を返すようにした。
  - `data_generation_items` を manifest に載せ、各 `data-*` について `decision`、`reason_code`、`extra_method_names`、`class_count`、`top_level_function`、`default_property_value`、property list を確認できるようにした。
- `mtool/scripts/check_mtool_self_loop.php`
  - self-loop projection に上記 4 count を追加した。
- `mtool/reference/mtool-self-loop-expected-output.json`
  - latest self-loop の data overlay count を baseline に追加した。

## Verification

- `php -l mtool/app/project_output_runtime_generator.php`
- `php -l mtool/scripts/check_mtool_self_loop.php`
- `docker compose exec -T web-admin php /var/www/mtool/scripts/check_mtool_self_loop.php --requested-by=codex`

確認結果:

- `mode=canonical-dbaccess-partial-sql-regenerated`
- `canonical_data_class_count=64`
- `data_entity_count=101`
- `plain_data_candidate_count=64`
- `non_plain_data_candidate_count=37`
- `bootstrap_data_class_count=37`
- `legacy_delegate_function_count=0`
- `warnings=[]`

latest manifest の blocker 内訳は次のとおり:

- `class_count != 1`: `27`
- `extra_method_names != []`: `20`
- `has_top_level_function = true`: `16`
- `has_default_property_value = true`: `2`

上記は重複ありで、37 class の内訳を複数軸で数えている。

## Implication

- 次の self-host slice は、plain DTO の取りこぼし探索ではなく、`non-plain-bootstrap` のどのパターンを先に currentize するかに集中できる。
- 特に `extra_methods` だけで済む class、または helper を持っても multi-class / top-level function を含まない class を先に切り出す余地がある。
- latest self-loop では property mismatch warning は出ていないため、今の主 blocker は canonical property list mismatch ではなく bootstrap 側の class shape である。
