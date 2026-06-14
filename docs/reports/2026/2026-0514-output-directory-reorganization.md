# Output Directory Reorganization

## Summary

- fixed the runtime path base after moving the live code under `mtool/`
- switched default source output targets away from `published/`
- removed the ambiguous `mtool/shared` / `sample/source-outputs` / `mtool/source-outputs` direction
- settled on `mtool/app`, `mtool/reference`, `work/`, `sample/output-baselines/`, and `published/` responsibilities

## Implemented

- `mtool/app/runtime_storage_paths.php`
  - repo root is now the actual repository root, not `mtool/`
  - canonicalized legacy `shared/reference` and `sample/source-outputs` paths to the new layout
- `mtool/app/project_output_service.php`
  - default `source_output_dir`
    - all projects -> `work/source-outputs/{project_key}/{source_output_key}`
  - default `source_temp_output_dir`
    - `work/staging/source-outputs/{project_key}/{source_output_key}`
- `compose.yaml`
  - mounted `./sample` to `/var/www/sample`
  - mounted `./mtool/app` to `/var/www/mtool/app`
  - mounted `./mtool/reference` to `/var/www/mtool/reference`
- admin/lab source-output pages
  - replaced `published root` wording with `default output root` plus `migration mirror`
  - clarified that current raw output always lands under `work/source-outputs/`
  - clarified that `sample/output-baselines/` is curated durable baseline storage only
  - updated placeholders from `tmp/source-outputs/...` to `work/staging/source-outputs/...`
- seed SQL
  - switched source-output defaults from `published/source-outputs/...` and `sample/source-outputs/...` to `work/source-outputs/...`
  - populated `source_temp_output_dir` with `work/staging/source-outputs/...`
  - switched `source_template_dir` references from `mtool/shared/reference/...` to `mtool/reference/...`

## Directory Roles

- `work/source-outputs/`
  - disposable current raw output for all projects
- `sample/output-baselines/`
  - curated durable sample/reference baseline only
- `published/source-outputs/`
  - migration mirror only
- `mtool/app/`
  - current runtime application code shared by `admin` and `lab`
- `mtool/reference/`
  - durable runtime reference assets and canonical HTML module roots

## Follow-up

- restart/recreate containers so the new bind mounts are definitely in effect
- regenerate `MTOOL` outputs under `work/source-outputs/`
- compare reproducible trees against `published/source-outputs/`
- delete matching mirrors from `published/`
