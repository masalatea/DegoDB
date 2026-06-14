<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-LastBuildBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-LastBuild.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-LastBuild.php` and extend `LastBuildDBAccessBase` for project-specific customizations.

    class LastBuildDBAccess extends LastBuildDBAccessBase
    {
    }
}

?>
