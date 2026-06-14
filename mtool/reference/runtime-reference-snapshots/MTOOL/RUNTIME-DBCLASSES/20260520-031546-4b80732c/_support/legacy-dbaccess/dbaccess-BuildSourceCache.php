<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-BuildSourceCacheBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-BuildSourceCache.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-BuildSourceCache.php` and extend `BuildSourceCacheDBAccessBase` for project-specific customizations.

    class BuildSourceCacheDBAccessLegacy extends BuildSourceCacheDBAccessBase
    {
    }
}

?>
