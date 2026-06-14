<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-BuildLogBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-BuildLog.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-BuildLog.php` and extend `BuildLogDataBase` for project-specific customizations.

    class BuildLogData extends BuildLogDataBase
    {
    }
}

?>
