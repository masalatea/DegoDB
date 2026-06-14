<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-UploadServerPathBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-UploadServerPath.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-UploadServerPath.php` and extend `UploadServerPathDataBase` for project-specific customizations.

    class UploadServerPathData extends UploadServerPathDataBase
    {
    }
}

?>
