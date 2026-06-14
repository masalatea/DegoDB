# 2026-05-25 Project Token Fail-Closed Hardening

## 要約

- generated single-function/custom proxy runtime の `ProjectToken` auth が、`MTOOL_PROXY_PROJECT_TOKEN` 未設定時に fail-open していたため、fail-closed に修正した。
- `ProjectTokenOrGetFunc` は互換を保ち、token path が使えない時でも get-function path には fallback できるようにした。
- Lab Swagger viewer では `project-token` / `project-token-or-get-function` notice を明確化し、`NoSecurity` operation も inline で見えるようにした。

## 変更点

- `mtool/app/project_output_proxy_generator.php`
  - generated runtime template の `authorizeRequest()` を修正した
  - `project-token` は `MTOOL_PROXY_PROJECT_TOKEN` 未設定時に `RuntimeException` を投げるようにした
  - `project-token-or-get-function` は token path failure reason を保持しつつ、get-function path の成功は許可するようにした
- `mtool/app/lab_swagger_service.php`
  - `project-token` / `project-token-or-get-function` notice に `MTOOL_PROXY_PROJECT_TOKEN` 必須であることを追記した
  - `no-security` operation にも inline notice を出すようにした
- `tests/Integration/OpenApiSourceOutputContractTest.php`
  - generated runtime class を eval して private `authorizeRequest()` を呼ぶ contract test を追加した
  - `MTOOL_PROXY_PROJECT_TOKEN` 未設定時の `project-token` fail-closed を固定した
  - 同条件でも `project-token-or-get-function` は get-function path に fallback できることを固定した

## 検証

```bash
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ProjectDbAccessSyncBootstrapContractTest.php
make test
```

- `OpenApiSourceOutputContractTest`: `OK (13 tests, 1616 assertions)`
- `ProjectDbAccessSyncBootstrapContractTest`: `OK (4 tests, 11 assertions)`
- full suite: `OK (110 tests, 4151 assertions)`

## 今の意味

- fixed `openapi.json` filename よりも先に対処すべきだった auth weakness は、current generated runtime template では閉じられた
- `project-token` を使う published proxy は、endpoint 側で `MTOOL_PROXY_PROJECT_TOKEN` を明示設定しない限り通らない
- `NoSecurity` default import lane はあえて残しつつ、viewer 上で認証なし endpoint だと読み取りやすくなった
