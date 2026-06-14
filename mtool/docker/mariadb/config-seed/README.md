# MTOOL Core Seed Pack

This directory holds the canonical `Project 1 = MTOOL` bootstrap seed pack.

- `compose.yaml` alone does not apply these files.
- `mtool/docker/compose/01_mtool.compose.yaml` stages this directory into `/docker-entrypoint-initdb.d`.
- `LanguageResource` 旧 overlay compose / seed pack は current path から外し、必要なら archive から明示的に取り出す。
- sample pack はこの seed を土台にし、その上へ `sample/<category>/<pack>/seed/*.sql` を追加する。

Design intent:

- base `compose.yaml` = empty `config_app`
- scenario 1 = `MTOOL`
- legacy archive = `LanguageResource` source outputs + DBAccess metadata の historical backup
- `Project 2+` = sample pack
- `032_refresh_source_output_directory_policy.sql` can be re-applied to an existing live DB to move `project_source_outputs` away from `published/...` and back onto the current path policy
