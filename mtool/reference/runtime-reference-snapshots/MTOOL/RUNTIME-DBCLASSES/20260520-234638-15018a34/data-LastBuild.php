<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-LastBuildBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-LastBuild.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-LastBuild.php` and extend `LastBuildDataBase` for project-specific customizations.

    class LastBuildData extends LastBuildDataBase
    {
    }
}

?>
