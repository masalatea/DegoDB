# 2026-06-20 Generated OIDC JWT Runtime Verification

## Status

- status: `DONE`
- purpose: generated single proxy runtime で `oidc-jwt-bearer` auth policy を実行可能にする

## Summary

generated API / proxy runtime の auth policy v2 で、`oidc-jwt-bearer` を実行可能な認証方式として扱えるようにした。

この作業は Mtool admin/lab login の SSO ではない。Mtool が生成した API endpoint が、OIDC provider 由来の JWT bearer token を fail-closed に検証するための runtime work である。

## Implemented

- `oidc-jwt-bearer` policy contract の `implementation_status` を `implemented` に更新。
- generated proxy resolver で `oidc-jwt-bearer` を v2 auth policy として許可。
- generated single proxy runtime に JWT bearer verification を追加。
- JWT signature / temporal claims は `firebase/php-jwt` に委譲。
- runtime 側で issuer / audience / required claims を検証。
- JWKS source は `jwks_json_env`、`jwks_uri`、`discovery_url` から解決。
- generated handler base に auth policy payload を埋め込むようにした。

## Verification

- `php -l mtool/app/generated_runtime_auth_policy.php`
- `php -l mtool/app/db_access_endpoint_policy.php`
- `php -l mtool/app/project_output_proxy_generator.php`
- `php -l tests/Integration/AuthPolicyContractTest.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `AuthPolicyContractTest`: 8 tests, 25 assertions
- `OpenApiSourceOutputContractTest`: 21 tests, 1697 assertions
- `make sample16-pack-runtime-test`: 1 test, 35 assertions
- `make sample25-pack-runtime-test`: 1 test, 7 assertions
- `make sample26-pack-runtime-test`: 1 test, 11 assertions

## Notes

- Test coverage creates an RSA key and JWKS in-process, so the runtime contract is verified without a real external IdP.
- `jwks_json_env` is intended for deterministic runtime verification and offline sample / smoke coverage. Production deployments can use `jwks_uri` or `discovery_url`.
- OpenAPI security scheme expansion for OIDC-specific metadata remains a later polish item; bearer transport already uses the same HTTP Authorization header shape.
