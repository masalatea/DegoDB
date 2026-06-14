<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-UploadServerBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-UploadServer.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-UploadServer.php` and extend `UploadServerDataBase` for project-specific customizations.

    class UploadServerData extends UploadServerDataBase
    {
    }
}

?>
