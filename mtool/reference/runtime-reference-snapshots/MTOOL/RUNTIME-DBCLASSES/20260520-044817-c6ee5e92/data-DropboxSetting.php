<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DropboxSettingBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DropboxSetting.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DropboxSetting.php` and extend `DropboxSettingDataBase` for project-specific customizations.

    class DropboxSettingData extends DropboxSettingDataBase
    {
    }
}

?>
