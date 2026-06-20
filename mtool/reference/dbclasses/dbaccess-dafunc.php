<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DafuncBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Dafunc.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Dafunc.php` and extend `DafuncDBAccessBase` for project-specific customizations.

    class DafuncDBAccess extends DafuncDBAccessBase
    {
    }
}

?>
