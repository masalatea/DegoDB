<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DBUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DBUser.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DBUser.php` and extend `DBUserDBAccessBase` for project-specific customizations.

    class DBUserDBAccess extends DBUserDBAccessBase
    {
    }
}

?>
