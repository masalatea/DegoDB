<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-BuildTokenTemplateCacheBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-BuildTokenTemplateCache.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-BuildTokenTemplateCache.php` and extend `BuildTokenTemplateCacheDataBase` for project-specific customizations.

    class BuildTokenTemplateCacheData extends BuildTokenTemplateCacheDataBase
    {
    }
}

?>
