# 2026-05-14 Source Output Candidate Policy

## Summary

- `shared/project_source_output_new_page.php` は legacy add-flow の `source_output_key` / `name` 補完を `safe-prefill` / `warning-candidate` / `manual-only` の 3 段階に分けて扱うようにした。
- `safe-prefill` は一意に読めて current catalog と衝突しない候補だけを form に自動投入する。
- `warning-candidate` は duplicate / conflicting hint を blank のまま止めず、candidate card に候補と理由を出して手動確定へ戻す。
- `warning-candidate` の候補は page 内の `Use This Candidate` から form に反映できる。
- `manual-only` は current key collision、duplicate language resource の `-ALT` 使い切り、非 canonical path など、候補をそのまま出すと危険なケースに限定する。

## Policy Buckets

- `safe-prefill`
  - html: legacy path が 1 つの canonical html module key にだけ解決でき、current catalog に未使用。
  - proxy: `proxy_base_url` / dir / target server hint が同じ basename に収束し、current catalog に未使用。
  - language resource: legacy dir から base key が一意に決まり、current catalog に未使用。
  - DBAccess: `RUNTIME-DBCLASSES` がまだ存在しない project。
- `warning-candidate`
  - html/proxy: 複数 hint から別 candidate が出るが、その候補自体は current catalog に未使用。
  - language resource: base key は既存だが `-ALT` がまだ空いている duplicate dir。
- `manual-only`
  - current catalog と candidate key が衝突する。
  - language resource duplicate で base と `-ALT` の両方が既に埋まっている。
  - path / URL / basename が canonical policy に乗らず stable key を作れない。

## MTOOL Notes

- `Project 1 (Mtool)` の legacy inventory で intrinsic duplicate として残っているのは `ja.matsuesoft.com/lib` (`PSO 279`, `PSO 369`) だけである。
- 現在の MTOOL catalog では `LANGRES-PHP-JA-WEB-LIB` と `LANGRES-PHP-JA-WEB-LIB-ALT` の両方が既に seed 済みなので、この duplicate dir は `manual-only` 扱いになる。
- `RUNTIME-DBCLASSES` も現 catalog では既に存在するため、legacy DBAccess add-flow は `manual-only` 扱いになる。
- したがって現時点の `warning-candidate` は、将来の duplicate language resource で `-ALT` がまだ空いている場合か、html/proxy hint が衝突した future add-flow を主対象にする。

## Verification

```zsh
php -l shared/project_source_output_new_page.php

docker compose exec -T web-admin php -r 'require "/var/www/shared/bootstrap.php"; require "/var/www/shared/project_source_output_new_page.php"; $app = app_bootstrap(); $input = app_source_output_form_defaults(); $input["class_type"] = "html"; $input["source_output_dir"] = "/legacy/ftp/www/dev.matsuesoft.com/settings/security"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; echo json_encode(app_project_source_output_new_bridge_identity_policy($app, "MTOOL", "Mtool", $input, $hints), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;'

docker compose exec -T web-admin php -r 'require "/var/www/shared/bootstrap.php"; require "/var/www/shared/project_source_output_new_page.php"; $app = app_bootstrap(); $input = app_source_output_form_defaults(); $input["class_type"] = "ProxyServer"; $input["proxy_base_url"] = "https://dev.matsuesoft.com/proxy_alpha"; $input["source_output_dir"] = "/legacy/ftp/www/dev.matsuesoft.com/proxy_beta"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; echo json_encode(app_project_source_output_new_bridge_identity_policy($app, "MTOOL", "Mtool", $input, $hints), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;'

docker compose exec -T web-admin php -r 'require "/var/www/shared/bootstrap.php"; require "/var/www/shared/project_source_output_new_page.php"; $app = app_bootstrap(); $input = app_source_output_form_defaults(); $input["class_type"] = "DBAccess"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => "", "legacy_source_template_dir" => ""]; echo json_encode(app_project_source_output_new_bridge_identity_policy($app, "MTOOL", "Mtool", $input, $hints), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;'

docker compose exec -T web-admin php -r 'require "/var/www/shared/bootstrap.php"; require "/var/www/shared/project_source_output_new_page.php"; $app = app_bootstrap(); $input = app_source_output_form_defaults(); $input["class_type"] = "LanguageResource"; $input["program_language"] = "php"; $input["source_output_dir"] = "/legacy/ftp/www/ja.matsuesoft.com/lib"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; echo json_encode(app_project_source_output_new_bridge_identity_policy($app, "MTOOL", "Mtool", $input, $hints), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;'
```

結果:

- html の unique path 例は `safe-prefill` になり、`HTML-SETTINGS-SECURITY` を prefill 候補として返す。
- conflicting proxy hint 例は `warning-candidate` になり、`ALPHA-PROXY-SERVER` と `BETA-PROXY-SERVER` を candidate card 用に返す。
- DBAccess は current catalog の `RUNTIME-DBCLASSES` と衝突するため `manual-only` になる。
- `ja.matsuesoft.com/lib` は current catalog で base / `-ALT` を使い切っているため `manual-only` になる。
