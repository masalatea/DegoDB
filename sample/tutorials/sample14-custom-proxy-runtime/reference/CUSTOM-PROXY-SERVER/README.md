# Custom Proxy Server Artifact

Generated from `SAMPLE14/CUSTOM-PROXY-SERVER`.

This bundle contains:
- PHP endpoint entrypoints for the targeted custom proxies
- minimal copied `data-*` / `dbaccess-*` runtime files for referenced sources
- generated base handler classes and default wrapper classes

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
- `mtool/extensions/SAMPLE14/CUSTOM-PROXY-SERVER/bootstrap.php`
- wrapper handler methods `authorizeByGetFunction()` / `authorizeByLoginCookieToken()` when the auth strategy requires them
- `mtool/extensions/SAMPLE14/CUSTOM-PROXY-SERVER/handlers/CatalogSummaryProxyHandler.php`
- `mtool/extensions/SAMPLE14/CUSTOM-PROXY-SERVER/handlers/TransactionPairProxyHandler.php`
