# 2026-05-14 HTML Template Canonical Settings

## 概要

- 旧 `systemsettings/htmltemplate` を current admin route に移した。
- global template metadata の canonical table として `html_templates` / `html_template_parameters` を追加した。
- project HTML parameter audit も copied reference ではなく canonical template metadata を優先して読むように切り替えた。

## 追加したもの

- schema
  - `docker/mariadb/config-initdb/016_html_template_metadata.sql`
- repository
  - `mtool/app/html_template_repository.php`
- route common
  - `mtool/app/html_template_route_common.php`
- page
  - `mtool/app/html_templates_page.php`
  - `mtool/app/html_template_detail_page.php`
  - `mtool/app/html_template_parameters_page.php`

## route

- `/settings/html-templates`
- `/settings/html-templates/{legacy_template_pid}`
- `/settings/html-templates/{legacy_template_pid}/parameters`

## current behavior

- canonical table が存在すれば `html_templates` / `html_template_parameters` を優先する。
- canonical table が空の初回だけ、legacy `htmlTemplate` / `htmlTemplateParameter` table があればそこから bootstrap する。
- legacy table が無い環境では copied MTOOL reference から bootstrap する。
- current create/update/delete は canonical table を正本に扱い、legacy PID を public identifier として維持する。

## verification

- `php -l`
  - `html_template_repository.php`
  - `html_template_route_common.php`
  - `html_templates_page.php`
  - `html_template_detail_page.php`
  - `html_template_parameters_page.php`
  - `project_html_repository.php`
  - `project_htmls_page.php`
  - `project_html_detail_page.php`
  - `project_html_parameters_page.php`
  - `http.php`
  - `router.php`
  - `domain_validation.php`
- running `db-config` に `016_html_template_metadata.sql` を適用した。
- `web-admin` container から repository を叩き、bootstrap 後の count を確認した。
  - `html_templates = 349`
  - `html_template_parameters = 7`
- smoke test
  - template create/update/delete
  - template parameter create/update/delete
  - temp row cleanup 後、count は `349 / 7` に戻ることを確認した。

## 残り

- generator/runtime 側で template metadata をどこまで current canonical 正本へ寄せ切るかの整理。
- `LanguageResource` の current canonical 化。
