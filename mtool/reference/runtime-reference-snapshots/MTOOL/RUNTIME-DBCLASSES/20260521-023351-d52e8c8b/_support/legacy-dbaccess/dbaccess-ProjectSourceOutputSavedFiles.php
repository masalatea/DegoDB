<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ProjectSourceOutputSavedFilesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ProjectSourceOutputSavedFiles.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ProjectSourceOutputSavedFiles.php` and extend `ProjectSourceOutputSavedFilesDBAccessBase` for project-specific customizations.

    class ProjectSourceOutputSavedFilesDBAccessLegacy extends ProjectSourceOutputSavedFilesDBAccessBase
    {
    }
}

?>
