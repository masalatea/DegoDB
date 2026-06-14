# MTOOL Runtime DBClasses Extension Layer

This directory is the user-owned companion layer for `MTOOL / RUNTIME-DBCLASSES`.

Bootstrap reference lives under `mtool/reference/dbclasses`.
`create_project_output.php` first writes an artifact bundle under `work/artifacts/source-outputs/MTOOL/{artifact_key}/bundle/...`.
`work/source-outputs/MTOOL/RUNTIME-DBCLASSES` is updated only when that artifact is published.
Do not edit generated files directly.
Current emitted runtime file contract is defined in `docs/internal/generated-code-strategy.md`.

Published runtime keeps legacy basenames at the top level and splits actual implementation into:

- root `dbaccess-*.php` generated wrapper entry files
- `mtool/dbclasses/base/dbaccess-*Base.php`
- root `data-*.php` generated wrapper entry files
- `mtool/dbclasses/base/data-*Base.php`
- `mtool/dbclasses/_runtime_loader.php` for custom bootstrap and historical layered-runtime compatibility helpers
- `mtool/dbclasses/_support/legacy-dbaccess/` for copied legacy support only when delegation still remains, or compatibility placeholders when generated DBAccess base is standalone
- `mtool/dbclasses/_support/runtime-generation-manifest.json` for mode/count/artifact provenance

Current emitted runtime tree does not include `mtool/dbclasses/_base/` or `mtool/dbclasses/_wrappers/`.
Those paths remain historical self-generated bundle input only for `generated_catalog.php`, runtime build-plan, and migration helpers.

Keep project-specific companion code here instead. Main entry points are:

- `bootstrap.php`
- `data-*.php`
- `dbaccess-*.php`

Typical helper boundaries are:

- helpers
- mappers
- services
- policies

Current guidance:

- keep DTO / data classes thin
- avoid patching generated files
- move business rules and lookup helpers into this extension layer
- load helper / collaborator files from `bootstrap.php`
- override only the classes you need by adding the same basename here and extending the corresponding `*Base` class
