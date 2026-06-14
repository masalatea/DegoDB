<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-SettingGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-SettingGroup.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-SettingGroup.php` and extend `SettingGroupDBAccessBase` for project-specific customizations.

    class SettingGroupDBAccessLegacy extends SettingGroupDBAccessBase
    {
    }
}

?>
