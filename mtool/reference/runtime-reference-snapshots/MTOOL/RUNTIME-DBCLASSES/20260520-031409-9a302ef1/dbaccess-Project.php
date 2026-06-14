<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Project.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Project.php` and extend `ProjectDBAccessBase` for project-specific customizations.

    class ProjectDBAccess extends ProjectDBAccessBase
    {
    }
}

?>
