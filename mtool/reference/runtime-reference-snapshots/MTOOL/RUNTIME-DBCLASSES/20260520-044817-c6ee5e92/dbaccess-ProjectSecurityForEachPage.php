<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ProjectSecurityForEachPageBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ProjectSecurityForEachPage.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ProjectSecurityForEachPage.php` and extend `ProjectSecurityForEachPageDBAccessBase` for project-specific customizations.

    class ProjectSecurityForEachPageDBAccess extends ProjectSecurityForEachPageDBAccessBase
    {
    }
}

?>
