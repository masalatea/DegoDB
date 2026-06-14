<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ProjectGroupTemplateBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ProjectGroupTemplate.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ProjectGroupTemplate.php` and extend `ProjectGroupTemplateDBAccessBase` for project-specific customizations.

    class ProjectGroupTemplateDBAccessLegacy extends ProjectGroupTemplateDBAccessBase
    {
    }
}

?>
