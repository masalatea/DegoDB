<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ApacheSettingBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ApacheSetting.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ApacheSetting.php` and extend `ApacheSettingDataBase` for project-specific customizations.

    class ApacheSettingData extends ApacheSettingDataBase
    {
    }
}

?>
