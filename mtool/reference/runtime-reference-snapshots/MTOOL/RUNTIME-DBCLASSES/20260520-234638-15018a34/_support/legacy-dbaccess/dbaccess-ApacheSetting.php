<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ApacheSettingBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ApacheSetting.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ApacheSetting.php` and extend `ApacheSettingDBAccessBase` for project-specific customizations.

    class ApacheSettingDBAccessLegacy extends ApacheSettingDBAccessBase
    {
    }
}

?>
