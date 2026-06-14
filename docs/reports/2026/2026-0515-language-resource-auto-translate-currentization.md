# 2026-05-15 Language Resource Auto Translate Currentization

## Superseded

- この slice は 2026-05-15 の後続変更で superseded になった。
- current app の `/projects/{project_key}/language-resources/auto-translate` route と translation service は同日中に削除済みで、現在は `LanguageResource` auto translate を current admin では扱わない。
- 現在の正本は file workflow 方針であり、詳細は [2026-0515-language-resource-file-source-of-truth-plan.md](<repo-root>/docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md) を参照する。

## 概要

- `LanguageResource` の `lang_res_auto_translate_ajax.php` 相当を current admin route へ移した。
- detail page に source picker / target translate UI を追加し、translation 実行自体は current JSON endpoint へ分離した。
- 外部 API 未設定の環境では `disabled` として明示的に失敗させる。local verification 用に `identity` provider も用意した。

## 追加したもの

- config
  - `mtool/app/config.php`
  - `compose.yaml`
  - `.env.example`
- translation service
  - `mtool/app/language_translation_service.php`
- route / endpoint
  - `mtool/app/project_language_resource_auto_translate_api_page.php`
  - `mtool/app/router.php`
  - `mtool/app/http.php`
- UI
  - `mtool/app/project_language_resource_detail_page.php`
  - `mtool/app/project_language_resource_route_common.php`
- summary
  - `mtool/app/project_detail_page.php`

## current behavior

- route
  - `POST /projects/{project_key}/language-resources/auto-translate`
- detail page
  - `Set As Source`
    - 対象 caption の text と `lang_for_google` を translation source として保持する。
  - `Auto Translate Here`
    - selected source から target language へ translate し、caption と `caption_auto_translated` hidden field を同時に更新する。
  - `Auto Translate Blank Captions`
    - blank caption のみ順に translate して current form に流し込む。
- provider
  - `disabled`
    - 既定値。`503` JSON で `auto-translate provider が設定されていません。` を返す。
  - `identity`
    - development-only。source text をそのまま返す。
  - `google-translate-v2`
    - `APP_TRANSLATION_GOOGLE_API_KEY` を使って Google Translate v2 REST API を呼ぶ。

## env

- `APP_TRANSLATION_PROVIDER`
  - `disabled` / `identity` / `google-translate-v2`
- `APP_TRANSLATION_GOOGLE_API_KEY`
  - `google-translate-v2` 使用時に必要
- `APP_TRANSLATION_TIMEOUT_SECONDS`
  - outbound request timeout

## verification

- `php -l`
  - `mtool/app/config.php`
  - `mtool/app/language_translation_service.php`
  - `mtool/app/project_language_resource_auto_translate_api_page.php`
  - `mtool/app/project_language_resource_detail_page.php`
  - `mtool/app/project_language_resource_route_common.php`
  - `mtool/app/router.php`
  - `mtool/app/http.php`
  - `mtool/app/project_detail_page.php`
- running `web-admin` render
  - detail page に `Auto Translate` section、`Set As Source`、`Auto Translate Here`、`Auto Translate Blank Captions` が出ることを確認した。
- disabled path
  - current container env のまま `POST /projects/MTOOL/language-resources/auto-translate` を叩き、`503` JSON と `provider=disabled` を確認した。
- service success path
  - local CLI で `identity` provider を使い、`Hello` が success で返ることを確認した。

## 残り

- `LanguageResource` の current route 残件は、機能面では optional module / code-native source-of-truth 境界の整理が主になる。
- 実運用で Google translation を使う場合は、API key の配布方針と admin site env 注入を固定する必要がある。
