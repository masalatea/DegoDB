<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DbtablecolumnsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Dbtablecolumns.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Dbtablecolumns.php` and extend `DbtablecolumnsDBAccessBase` for project-specific customizations.

    class DbtablecolumnsDBAccess extends DbtablecolumnsDBAccessBase
    {
    }
}

?>
