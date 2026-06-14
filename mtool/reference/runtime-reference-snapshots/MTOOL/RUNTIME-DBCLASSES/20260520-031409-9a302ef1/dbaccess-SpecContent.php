<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-SpecContentBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-SpecContent.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-SpecContent.php` and extend `SpecContentDBAccessBase` for project-specific customizations.

    class SpecContentDBAccess extends SpecContentDBAccessBase
    {
    }
}

?>
