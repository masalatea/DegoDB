<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DBUserClientHostBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DBUserClientHost.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DBUserClientHost.php` and extend `DBUserClientHostDBAccessBase` for project-specific customizations.

    class DBUserClientHostDBAccess extends DBUserClientHostDBAccessBase
    {
    }
}

?>
