<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ProjectUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ProjectUser.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ProjectUser.php` and extend `ProjectUserDBAccessBase` for project-specific customizations.

    class ProjectUserDBAccessLegacy extends ProjectUserDBAccessBase
    {
    }
}

?>
