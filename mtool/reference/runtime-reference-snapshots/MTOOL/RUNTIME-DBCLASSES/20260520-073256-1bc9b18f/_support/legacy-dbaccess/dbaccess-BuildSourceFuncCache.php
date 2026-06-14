<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-BuildSourceFuncCacheBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-BuildSourceFuncCache.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-BuildSourceFuncCache.php` and extend `BuildSourceFuncCacheDBAccessBase` for project-specific customizations.

    class BuildSourceFuncCacheDBAccessLegacy extends BuildSourceFuncCacheDBAccessBase
    {
    }
}

?>
