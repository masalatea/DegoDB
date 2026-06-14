# 2026-05-13 Endpoint Test POST Bridge

## Summary

- `endpoint_test_json_ajax.php` の generated wrapper を更新し、known-project POST を current `endpoint_test_job_service.php` へ bridge するようにした
- GET/HEAD は従来どおり current `/runs/endpoints/{project_key}` へ redirect する
- POST bridge は current 側で job manifest / snapshot を保存しつつ、返却 HTML は legacy worker 互換の fragment (`Original Result` / `Result with Format` / `Result: NG`) を維持する
- unknown `ProjectPID` や shared bootstrap 不在時は引き続き `_legacy/endpoint_test_json_ajax.php` fallback を使う
- `endpoint_test_json_ajax.php` の currentization に合わせて rewrite map / roadmap / project detail の説明を更新した

## Files

- `shared/project_output_html_module_generator.php`
- `shared/project_detail_page.php`
- `scripts/show_html_db_rewrite_map.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`

## Verified

```zsh
docker compose ps
php -l shared/endpoint_test_job_service.php
php -l shared/lab_endpoint_test_page.php
php -l shared/project_output_html_module_generator.php
php -l shared/project_detail_page.php
php -l scripts/show_html_db_rewrite_map.php
php scripts/show_html_db_rewrite_map.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
php -l published/source-outputs/MTOOL/HTML-DB/endpoint_test_json_ajax.php
```

確認結果:

- Docker services は `db-config` / `db-lab` / `web-admin` / `web-lab` とも healthy
- `HTML-DB` publish artifact: `20260513-001706-6f576057`
- parity regression: `definition_count=36`, `success_count=36`, `failure_count=0`
- generated published wrapper の `endpoint_test_json_ajax.php` も lint 通過

## Next

1. unknown PID fallback をさらに縮める
2. `endpoint_common_include.php` / `endpoint_lib_include.php` / `endpoint_test_json_client_include.php` の cleanup 方針を決める
3. `html-authoring` cluster の current route 設計へ移る

## Notes

- current auth model には legacy の email verification / project membership 相当がまだないため、POST bridge は current session principal と current role 判定 (`admin` / `config` / `lab`) を使っている
- browser 経由の end-to-end 実打ちはまだしておらず、この slice では lint / publish / parity regression までを確認した
