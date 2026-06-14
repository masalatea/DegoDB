<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ProjectHostSettingBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ProjectHostSetting.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ProjectHostSetting.php` and extend `ProjectHostSettingDataBase` for project-specific customizations.

    class ProjectHostSettingData extends ProjectHostSettingDataBase
    {
    }
}

?>
