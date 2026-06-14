<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DBBackupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DBBackup.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DBBackup.php` and extend `DBBackupDBAccessBase` for project-specific customizations.

    class DBBackupDBAccess extends DBBackupDBAccessBase
    {
    }
}

?>
