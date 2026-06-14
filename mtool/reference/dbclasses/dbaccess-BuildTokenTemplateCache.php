<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-BuildTokenTemplateCacheBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-BuildTokenTemplateCache.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-BuildTokenTemplateCache.php` and extend `BuildTokenTemplateCacheDBAccessBase` for project-specific customizations.

    class BuildTokenTemplateCacheDBAccess extends BuildTokenTemplateCacheDBAccessBase
    {
    }
}

?>
