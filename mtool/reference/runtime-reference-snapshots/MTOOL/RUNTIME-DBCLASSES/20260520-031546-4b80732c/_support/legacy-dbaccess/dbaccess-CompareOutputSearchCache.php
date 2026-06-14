<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-CompareOutputSearchCacheBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-CompareOutputSearchCache.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-CompareOutputSearchCache.php` and extend `CompareOutputSearchCacheDBAccessBase` for project-specific customizations.

    class CompareOutputSearchCacheDBAccessLegacy extends CompareOutputSearchCacheDBAccessBase
    {
    }
}

?>
