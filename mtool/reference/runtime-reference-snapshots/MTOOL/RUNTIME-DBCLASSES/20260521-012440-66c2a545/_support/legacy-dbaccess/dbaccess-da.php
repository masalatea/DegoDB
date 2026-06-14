<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-daBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-da.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-da.php` and extend `daDBAccessBase` for project-specific customizations.

    class daDBAccessLegacy extends daDBAccessBase
    {
    }
}

?>
