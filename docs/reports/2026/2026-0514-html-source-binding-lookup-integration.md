# 2026-05-14 HTML Source Binding Lookup Integration

## Summary

- `project_html_route_common.php` の legacy `ProjectSourceOutputPID -> current source output` 解決を、`project_html_source_bindings` 優先に切り替えた。
- `project_output_html_module_generator.php` の generated wrapper context でも、legacy source output PID map を `project_html_source_bindings` 優先に切り替えた。
- これにより HTML list/detail と generated compatibility wrapper は、HTML bucket に関して `project_source_outputs.notes` の bootstrap metadata より current binding row を先に使うようになった。

## Files

- `mtool/app/project_html_route_common.php`
- `mtool/app/project_output_html_module_generator.php`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/README.md`

## Verification

```zsh
php -l mtool/app/project_html_route_common.php
php -l mtool/app/project_output_html_module_generator.php
```

確認結果:

- 両ファイルとも `php -l` を通過した。
- この変更は lookup 優先順位の差し替えなので、既存 seed のままでは見た目の差が小さいが、canonical binding row がある bucket では route / wrapper とも binding table 側を使う実装になった。

## Remaining Focus

- live `html` / `htmlParameter` table 依存を current canonical へどう寄せるかを次段で詰める。
- `LanguageResource` 境界の固定へ進む。
