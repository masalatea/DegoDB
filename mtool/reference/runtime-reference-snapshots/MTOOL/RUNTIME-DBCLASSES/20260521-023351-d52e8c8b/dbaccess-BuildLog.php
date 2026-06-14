<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-BuildLogBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-BuildLog.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-BuildLog.php` and extend `BuildLogDBAccessBase` for project-specific customizations.

    class BuildLogDBAccess extends BuildLogDBAccessBase
    {
    }
}

?>
