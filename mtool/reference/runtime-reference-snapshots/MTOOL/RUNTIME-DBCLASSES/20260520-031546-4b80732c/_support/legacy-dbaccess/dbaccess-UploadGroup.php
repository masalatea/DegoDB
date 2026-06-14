<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-UploadGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-UploadGroup.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-UploadGroup.php` and extend `UploadGroupDBAccessBase` for project-specific customizations.

    class UploadGroupDBAccessLegacy extends UploadGroupDBAccessBase
    {
    }
}

?>
