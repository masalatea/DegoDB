<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-MySQLShowColumnBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-MySQLShowColumn.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-MySQLShowColumn.php` and extend `MySQLShowColumnDBAccessBase` for project-specific customizations.

    class MySQLShowColumnDBAccessLegacy extends MySQLShowColumnDBAccessBase
    {
    }
}

?>
