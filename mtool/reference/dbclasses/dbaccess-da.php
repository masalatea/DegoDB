<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DaBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Da.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Da.php` and extend `DaDBAccessBase` for project-specific customizations.

    class DaDBAccess extends DaDBAccessBase
    {
    }
}

?>
