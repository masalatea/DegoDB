<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-dbtablecolumnsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-dbtablecolumns.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-dbtablecolumns.php` and extend `dbtablecolumnsDBAccessBase` for project-specific customizations.

    class dbtablecolumnsDBAccessLegacy extends dbtablecolumnsDBAccessBase
    {
    }
}

?>
