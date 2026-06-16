# 2026-06-16 Sample15 Project Metadata Export Import Pack

## Status

- status: `DONE`
- pack: `sample/tutorials/sample15-project-metadata-export-import`
- project key: `SAMPLE15`
- reference: `PROJECT-METADATA-BUNDLE`
- canonical target: `make sample15-pack-runtime-test`

## Summary

`sample15-project-metadata-export-import` を tutorial runtime pack として追加した。

目的は、generated code output ではなく、project-scoped canonical metadata を bundle として export し、import preview / apply で復元する flow を user-facing sample として固定すること。

## Runtime Design

- table: `BundleNote`
- source output metadata: `DATACLASS-PHP`
- bundle scope: `project-core`
- import mode: same-project `preview -> apply`
- sidecar: none

## Added Files

- `sample/tutorials/sample15-project-metadata-export-import/README.md`
- `sample/tutorials/sample15-project-metadata-export-import/compose.yaml`
- `sample/tutorials/sample15-project-metadata-export-import/run.sh`
- `sample/tutorials/sample15-project-metadata-export-import/seed/`
- `sample/tutorials/sample15-project-metadata-export-import/reference/PROJECT-METADATA-BUNDLE/`
- `mtool/scripts/check_sample15_project_metadata_export_import_outputs.php`
- `mtool/scripts/lib/sample15_project_metadata_export_import_check.php`
- `tests/Integration/Sample15ProjectMetadataExportImportTest.php`
- `docs/study/project-metadata-export-import.md`

## Reference Policy

Reference files are actual exported bundle files from the sample runtime:

- `manifest.json`
- `project.json`
- `memberships.json`
- `tables.json`
- `data-classes.json`
- `db-access.json`
- `source-outputs.json`

The checker loads the reference bundle with the same bundle loader used by import. Volatile manifest fields such as export time, requester, file checksum, and byte size are normalized before comparison.

## Verification

- `make sample15-pack-runtime-test`
  - `OK (1 test, 8 assertions)`

## Notes

- The runtime pack does not use `original-codes/` as an input.
- `database_sources` sidecar / secret file handling is intentionally out of this first slice.
- Importing to a different project key is intentionally out of this first slice because slug uniqueness and rename policy need their own focused treatment.
