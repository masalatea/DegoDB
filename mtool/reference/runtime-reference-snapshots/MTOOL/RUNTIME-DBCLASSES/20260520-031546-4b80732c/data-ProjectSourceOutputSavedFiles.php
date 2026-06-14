<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ProjectSourceOutputSavedFilesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ProjectSourceOutputSavedFiles.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ProjectSourceOutputSavedFiles.php` and extend `ProjectSourceOutputSavedFilesDataBase` for project-specific customizations.

    class ProjectSourceOutputSavedFilesData extends ProjectSourceOutputSavedFilesDataBase
    {
    }
}

?>
