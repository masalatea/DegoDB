<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-MySQLShowColumnBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-MySQLShowColumn.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-MySQLShowColumn.php` and extend `MySQLShowColumnDataBase` for project-specific customizations.

    class MySQLShowColumnData extends MySQLShowColumnDataBase
    {
    }
}

?>
