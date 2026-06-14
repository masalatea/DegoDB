<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DBBackupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DBBackup.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DBBackup.php` and extend `DBBackupDataBase` for project-specific customizations.

    class DBBackupData extends DBBackupDataBase
    {
    }
}

?>
