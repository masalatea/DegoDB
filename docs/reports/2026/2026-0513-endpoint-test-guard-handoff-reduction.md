# 2026-05-13 Endpoint Test Guard Handoff Reduction

## Summary

- `shared/project_output_html_module_generator.php` の `endpoint_test_json_ajax.php` wrapper を更新し、guard 用 `_legacy/endpoint_test_json_ajax.php` fallback を current handoff に置き換えた
- GET/HEAD redirect は `ProxyURL` / 短い `POST_JSON` を query prefill した `/runs/endpoints/{project_key}` へ寄せる
- POST では malformed `ProjectPID`、unsupported verb、shared bootstrap missing を current handoff notice で処理し、legacy runtime を起動しない
- request JSON が長すぎるときは safe query string handoff を優先し、link には prefill しない
- publish artifact `20260513-041750-30301074` と `check_mtool_project1_outputs.php` `36/36 success` を確認した

## Files

- `shared/project_output_html_module_generator.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`
- `docs/reports/2026/2026-0513-endpoint-test-guard-handoff-reduction.md`

## Verified

```zsh
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
php -l published/source-outputs/MTOOL/HTML-DB/endpoint_test_json_ajax.php
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- `HTML-DB` publish artifact: `20260513-041750-30301074`
- generated published `endpoint_test_json_ajax.php` は lint 通過
- published wrapper から `_legacy/endpoint_test_json_ajax.php` 参照が消えた
- `check_mtool_project1_outputs.php`: `definition_count=36`, `success_count=36`, `failure_count=0`

## Remaining

- proxy 系 wrapper の actual save/reorder/action dispatch は current app bootstrap (`sharedRoot`) が必要
- `html-authoring` cluster は current route 設計と wrapper seam が未着手
