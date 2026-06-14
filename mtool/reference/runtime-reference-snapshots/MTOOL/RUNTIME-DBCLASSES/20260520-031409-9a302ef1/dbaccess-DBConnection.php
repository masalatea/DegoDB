<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DBConnectionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DBConnection.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DBConnection.php` and extend `DBConnectionDBAccessBase` for project-specific customizations.

    class DBConnectionDBAccess extends DBConnectionDBAccessBase
    {
    }
}

?>
