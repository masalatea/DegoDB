<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-UploadServerBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-UploadServer.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-UploadServer.php` and extend `UploadServerDBAccessBase` for project-specific customizations.

    class UploadServerDBAccessLegacy extends UploadServerDBAccessBase
    {
    }
}

?>
