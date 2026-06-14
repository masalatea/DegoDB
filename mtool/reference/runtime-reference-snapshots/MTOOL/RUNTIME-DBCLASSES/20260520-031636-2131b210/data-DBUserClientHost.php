<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DBUserClientHostBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DBUserClientHost.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DBUserClientHost.php` and extend `DBUserClientHostDataBase` for project-specific customizations.

    class DBUserClientHostData extends DBUserClientHostDataBase
    {
    }
}

?>
