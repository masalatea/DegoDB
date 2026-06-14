<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ProjectSecurityForEachPageBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ProjectSecurityForEachPage.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ProjectSecurityForEachPage.php` and extend `ProjectSecurityForEachPageDataBase` for project-specific customizations.

    class ProjectSecurityForEachPageData extends ProjectSecurityForEachPageDataBase
    {
    }
}

?>
