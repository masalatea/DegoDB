# Single Function Proxy Server Artifact

Generated from `SAMPLE26/AUTH-PROXY-SERVER`.

This bundle contains:
- PHP endpoint entrypoints for the targeted single-function proxies
- minimal copied `data-*` / `dbaccess-*` runtime files for referenced sources
- generated base handler classes and default wrapper classes

Request / response shape:
- request payloads stay function-local and direct
- auth fields (`TOKEN`, `LOGIN_COOKIE_TOKEN`) stay top-level when required
- select results return top-level `Result`; insert returns top-level `InsertID`

Environment variables:
- `MTOOL_PROXY_DB_HOST`
- `MTOOL_PROXY_DB_PORT`
- `MTOOL_PROXY_DB_USER`
- `MTOOL_PROXY_DB_PASSWORD`
- `MTOOL_PROXY_DB_NAME`
- `MTOOL_PROXY_PROJECT_TOKEN`
- `MTOOL_PROXY_CORS_ALLOW_ORIGIN`
- `MTOOL_PROXY_CORS_ALLOW_HEADERS`

Custom hook points:
- `mtool/extensions/SAMPLE26/AUTH-PROXY-SERVER/bootstrap.php`
- wrapper handler methods `authorizeByGetFunction()` / `authorizeByLoginCookieToken()` when the auth strategy requires them
- `mtool/extensions/SAMPLE26/AUTH-PROXY-SERVER/handlers/EbookCmsBookGetEditorEbookCmsChapterProxyHandler.php`
- `mtool/extensions/SAMPLE26/AUTH-PROXY-SERVER/handlers/EbookCmsBookUpdateEditorEbookCmsChapterDraftProxyHandler.php`
- `mtool/extensions/SAMPLE26/AUTH-PROXY-SERVER/handlers/EbookCmsBookPublishEditorEbookCmsChapterProxyHandler.php`
