# 2026-06-16 Sample14 Custom Proxy Runtime Pack

## Status

- status: `DONE`
- pack: `sample/tutorials/sample14-custom-proxy-runtime`
- project key: `SAMPLE14`
- source output key: `CUSTOM-PROXY-SERVER`
- canonical target: `make sample14-pack-runtime-test`

## Summary

`sample14-custom-proxy-runtime` を tutorial runtime pack として追加した。

目的は、legacy representative pack の `sample56-runtime-misc-proxy` から離して、custom proxy metadata と generated proxy server artifact の最小成功 path を学習できるようにすること。

## Runtime Design

- custom proxy key: `CATALOG-SUMMARY`
- generated proxy basename / name: `Catalog` / `Summary`
- auth type: `NoSecurity`
- steps:
  - `dbtable.GetdbtableList`
  - `ProjectSourceOutput.GetProjectSourceOutputList`
- output strategy: `custom-proxy-server`
- target binding type: `custom-proxy`

## Added Files

- `sample/tutorials/sample14-custom-proxy-runtime/README.md`
- `sample/tutorials/sample14-custom-proxy-runtime/compose.yaml`
- `sample/tutorials/sample14-custom-proxy-runtime/run.sh`
- `sample/tutorials/sample14-custom-proxy-runtime/seed/`
- `sample/tutorials/sample14-custom-proxy-runtime/reference/CUSTOM-PROXY-SERVER/`
- `mtool/scripts/check_sample14_custom_proxy_runtime_outputs.php`
- `mtool/scripts/lib/sample14_custom_proxy_runtime_output_check.php`
- `tests/Integration/Sample14CustomProxyRuntimeOutputTest.php`

## Reference Policy

Reference files are selected actual generated files from `work/source-outputs/SAMPLE14/CUSTOM-PROXY-SERVER/`.

The full custom proxy server artifact is intentionally not copied into the tutorial pack because it is much larger than the learning surface. The checker verifies selected key files and normalizes volatile `build-plan.json` fields before comparison.

## Verification

- `make sample14-pack-runtime-test`
  - `OK (1 test, 16 assertions)`

## Notes

- The runtime pack does not use `original-codes/` as an input.
- The sample uses existing curated generated catalog references for the two proxy steps.
- ProjectToken auth / fail-closed behavior is covered by `sample16-authenticated-proxy`.
