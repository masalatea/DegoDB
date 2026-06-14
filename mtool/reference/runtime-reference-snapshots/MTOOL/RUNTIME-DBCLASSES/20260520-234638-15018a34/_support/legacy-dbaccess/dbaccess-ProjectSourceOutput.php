<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ProjectSourceOutputBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ProjectSourceOutput.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ProjectSourceOutput.php` and extend `ProjectSourceOutputDBAccessBase` for project-specific customizations.

    class ProjectSourceOutputDBAccessLegacy extends ProjectSourceOutputDBAccessBase
    {
    }
}

?>
