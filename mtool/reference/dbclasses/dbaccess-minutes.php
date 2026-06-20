<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-MinutesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Minutes.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Minutes.php` and extend `MinutesDBAccessBase` for project-specific customizations.

    class MinutesDBAccess extends MinutesDBAccessBase
    {
    }
}

?>
