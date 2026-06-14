<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-SettingGroupUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-SettingGroupUser.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-SettingGroupUser.php` and extend `SettingGroupUserDataBase` for project-specific customizations.

    class SettingGroupUserData extends SettingGroupUserDataBase
    {
    }
}

?>
