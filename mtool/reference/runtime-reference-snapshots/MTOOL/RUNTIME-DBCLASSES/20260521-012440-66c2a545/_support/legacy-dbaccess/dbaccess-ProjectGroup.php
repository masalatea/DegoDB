<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ProjectGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ProjectGroup.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ProjectGroup.php` and extend `ProjectGroupDBAccessBase` for project-specific customizations.

    class ProjectGroupDBAccessLegacy extends ProjectGroupDBAccessBase
    {
    }
}

?>
