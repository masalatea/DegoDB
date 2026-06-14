<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-BuildSourceCacheBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-BuildSourceCache.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-BuildSourceCache.php` and extend `BuildSourceCacheDataBase` for project-specific customizations.

    class BuildSourceCacheData extends BuildSourceCacheDataBase
    {
    }
}

?>
