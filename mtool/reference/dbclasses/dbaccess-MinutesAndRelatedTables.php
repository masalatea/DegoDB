<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-MinutesAndRelatedTablesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-MinutesAndRelatedTables.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-MinutesAndRelatedTables.php` and extend `MinutesAndRelatedTablesDBAccessBase` for project-specific customizations.

    class MinutesAndRelatedTablesDBAccess extends MinutesAndRelatedTablesDBAccessBase
    {
    }
}

?>
