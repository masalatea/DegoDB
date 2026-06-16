# 2026-06-16 Sample13 OpenAPI API Surface Pack

## Status

- status: `DONE`
- target lane: `sample/tutorials/`
- target pack: `sample13-openapi-api-surface`
- project key: `SAMPLE13`
- main flow: table import -> DataClass sync -> single-function proxy target metadata -> `OPENAPI-JSON` publish

## Summary

`sample13-openapi-api-surface` を user-facing tutorial runtime pack として追加した。

この pack は、DBAccess function を API surface として外から読む最小導線を固定する。`ApiTask.GetApiTaskList` / `GetApiTask` を `OPENAPI-JSON` の target にし、`openapi-json` strategy で `openapi.json` と `build-plan.json` を publish する。

## Added Files

- `sample/tutorials/sample13-openapi-api-surface/`
  - `compose.yaml`
  - `run.sh`
  - `README.md`
  - `seed/`
  - `reference/OPENAPI-JSON/`
- `mtool/scripts/check_sample13_openapi_api_surface_outputs.php`
- `mtool/scripts/lib/sample13_openapi_api_surface_output_check.php`
- `tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php`
- `docs/study/openapi-api-surface.md`

## Verification

- `make sample13-pack-runtime-test`
  - `OK (1 test, 13 assertions)`

## Notes

- The reference output is actual generated output copied from `work/source-outputs/SAMPLE13/OPENAPI-JSON/`.
- `sample13` intentionally does not execute the generated proxy runtime. Custom proxy runtime and auth behavior stay in later tutorial candidates.
- OpenAPI public raw routes remain out of scope; the supported lane is authenticated viewer or admin artifact download.
