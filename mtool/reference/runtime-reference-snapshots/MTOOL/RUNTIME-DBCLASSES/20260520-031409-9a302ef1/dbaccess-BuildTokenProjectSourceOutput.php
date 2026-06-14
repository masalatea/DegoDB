<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-BuildTokenProjectSourceOutputBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-BuildTokenProjectSourceOutput.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-BuildTokenProjectSourceOutput.php` and extend `BuildTokenProjectSourceOutputDBAccessBase` for project-specific customizations.

    class BuildTokenProjectSourceOutputDBAccess extends BuildTokenProjectSourceOutputDBAccessBase
    {
    }
}

?>
