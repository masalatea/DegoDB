# 2026-06-16 Sample17 Multi Output Project Pack

## Status

- status: `DONE`
- pack: `sample/tutorials/sample17-multi-output-project`
- project key: `SAMPLE17`
- source output keys: `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, `OPENAPI-JSON`
- canonical target: `make sample17-pack-runtime-test`

## Summary

`sample17-multi-output-project` を tutorial runtime pack として追加した。

目的は、current tutorial lane の final capstone として、1 project から複数 Source Output を publish する flow を user-facing sample として固定すること。

## Runtime Design

- table: `CapstoneTask`
- DBAccess functions:
  - `CapstoneTask.GetCapstoneTaskList`
  - `CapstoneTask.GetCapstoneTask`
- outputs:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - `HTML-PAGE`
  - `OPENAPI-JSON`

## Added Files

- `sample/tutorials/sample17-multi-output-project/README.md`
- `sample/tutorials/sample17-multi-output-project/compose.yaml`
- `sample/tutorials/sample17-multi-output-project/run.sh`
- `sample/tutorials/sample17-multi-output-project/seed/`
- `sample/tutorials/sample17-multi-output-project/reference/`
- `mtool/reference/html-modules/sample17/HTML-PAGE/current/`
- `mtool/scripts/check_sample17_multi_output_project_outputs.php`
- `mtool/scripts/lib/sample17_multi_output_project_check.php`
- `tests/Integration/Sample17MultiOutputProjectTest.php`
- `docs/study/multi-output-capstone.md`

## Reference Policy

Reference files are actual generated files from `work/source-outputs/SAMPLE17/`.

The checker publishes all four outputs and compares each generated tree with the corresponding reference tree.

## Verification

- `make sample17-pack-runtime-test`
  - `OK (1 test, 7 assertions)`

## Notes

- The runtime pack does not use `original-codes/` as an input.
- Project metadata bundle export / import remains covered by `sample15-project-metadata-export-import`.
- ProjectToken auth remains covered by `sample16-authenticated-proxy`.
