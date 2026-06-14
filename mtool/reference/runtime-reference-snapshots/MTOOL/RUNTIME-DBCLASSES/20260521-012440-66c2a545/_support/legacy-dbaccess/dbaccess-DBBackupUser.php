<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DBBackupUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DBBackupUser.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DBBackupUser.php` and extend `DBBackupUserDBAccessBase` for project-specific customizations.

    class DBBackupUserDBAccessLegacy extends DBBackupUserDBAccessBase
    {
    }
}

?>
