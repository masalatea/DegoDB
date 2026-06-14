<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ProjectHostSettingBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ProjectHostSetting.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ProjectHostSetting.php` and extend `ProjectHostSettingDBAccessBase` for project-specific customizations.

    class ProjectHostSettingDBAccess extends ProjectHostSettingDBAccessBase
    {
    }
}

?>
