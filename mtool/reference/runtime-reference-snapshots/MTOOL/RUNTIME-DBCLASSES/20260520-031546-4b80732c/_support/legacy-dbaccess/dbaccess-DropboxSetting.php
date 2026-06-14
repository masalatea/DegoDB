<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DropboxSettingBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DropboxSetting.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DropboxSetting.php` and extend `DropboxSettingDBAccessBase` for project-specific customizations.

    class DropboxSettingDBAccessLegacy extends DropboxSettingDBAccessBase
    {
    }
}

?>
