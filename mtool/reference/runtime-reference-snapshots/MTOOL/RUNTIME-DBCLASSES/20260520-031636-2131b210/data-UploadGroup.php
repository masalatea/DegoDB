<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-UploadGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-UploadGroup.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-UploadGroup.php` and extend `UploadGroupDataBase` for project-specific customizations.

    class UploadGroupData extends UploadGroupDataBase
    {
    }
}

?>
