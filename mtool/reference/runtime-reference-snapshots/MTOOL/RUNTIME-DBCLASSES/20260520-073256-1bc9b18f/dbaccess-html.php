<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-htmlBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-html.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-html.php` and extend `htmlDBAccessBase` for project-specific customizations.

    class htmlDBAccess extends htmlDBAccessBase
    {
    }
}

?>
