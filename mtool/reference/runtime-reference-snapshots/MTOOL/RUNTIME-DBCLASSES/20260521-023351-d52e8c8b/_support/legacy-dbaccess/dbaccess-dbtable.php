<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-dbtableBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-dbtable.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-dbtable.php` and extend `dbtableDBAccessBase` for project-specific customizations.

    class dbtableDBAccessLegacy extends dbtableDBAccessBase
    {
    }
}

?>
