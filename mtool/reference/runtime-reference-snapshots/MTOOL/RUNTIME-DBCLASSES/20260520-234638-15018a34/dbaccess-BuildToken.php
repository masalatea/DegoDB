<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-BuildTokenBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-BuildToken.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-BuildToken.php` and extend `BuildTokenDBAccessBase` for project-specific customizations.

    class BuildTokenDBAccess extends BuildTokenDBAccessBase
    {
    }
}

?>
