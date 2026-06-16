# 2026-06-16 Sample16 Authenticated Proxy Pack

## Status

- status: `DONE`
- pack: `sample/tutorials/sample16-authenticated-proxy`
- project key: `SAMPLE16`
- source output key: `AUTH-PROXY-SERVER`
- canonical target: `make sample16-pack-runtime-test`

## Summary

`sample16-authenticated-proxy` を tutorial runtime pack として追加した。

目的は、generated single proxy server の `ProjectToken` auth と fail-closed behavior を user-facing sample として固定すること。

## Runtime Design

- table: `AuthTask`
- DBAccess function: `AuthTask.GetAuthTask`
- auth type: `ProjectToken`
- output strategy: `single-proxy-server`
- target binding type: `single-function-proxy`
- source output: `AUTH-PROXY-SERVER`

## Added Files

- `sample/tutorials/sample16-authenticated-proxy/README.md`
- `sample/tutorials/sample16-authenticated-proxy/compose.yaml`
- `sample/tutorials/sample16-authenticated-proxy/run.sh`
- `sample/tutorials/sample16-authenticated-proxy/seed/`
- `sample/tutorials/sample16-authenticated-proxy/reference/AUTH-PROXY-SERVER/`
- `mtool/scripts/check_sample16_authenticated_proxy_outputs.php`
- `mtool/scripts/lib/sample16_authenticated_proxy_check.php`
- `tests/Integration/Sample16AuthenticatedProxyTest.php`
- `docs/study/authenticated-proxy.md`

## Reference Policy

Reference files are actual generated files from `work/source-outputs/SAMPLE16/AUTH-PROXY-SERVER/`.

The checker compares the generated bundle against the reference and normalizes volatile `build-plan.json` fields before comparison.

## Verification

- `make sample16-pack-runtime-test`
  - `OK (1 test, 12 assertions)`

## Notes

- The runtime pack does not use `original-codes/` as an input.
- The auth checker verifies missing `TOKEN`, empty `TOKEN`, missing `MTOOL_PROXY_PROJECT_TOKEN`, wrong token, and matching token cases.
- HTTP server startup is intentionally out of this first slice; the generated handler authorization boundary is loaded directly from the actual artifact.
