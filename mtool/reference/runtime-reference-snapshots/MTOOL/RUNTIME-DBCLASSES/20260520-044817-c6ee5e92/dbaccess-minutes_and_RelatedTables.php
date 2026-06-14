<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-minutes_and_RelatedTablesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-minutes_and_RelatedTables.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-minutes_and_RelatedTables.php` and extend `minutes_and_RelatedTablesDBAccessBase` for project-specific customizations.

    class minutes_and_RelatedTablesDBAccess extends minutes_and_RelatedTablesDBAccessBase
    {
    }
}

?>
