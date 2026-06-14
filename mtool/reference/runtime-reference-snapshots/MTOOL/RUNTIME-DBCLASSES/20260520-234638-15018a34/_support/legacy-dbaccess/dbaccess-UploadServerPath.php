<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-UploadServerPathBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-UploadServerPath.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-UploadServerPath.php` and extend `UploadServerPathDBAccessBase` for project-specific customizations.

    class UploadServerPathDBAccessLegacy extends UploadServerPathDBAccessBase
    {
    }
}

?>
