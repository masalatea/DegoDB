<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-LiveCheckResultBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-LiveCheckResult.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-LiveCheckResult.php` and extend `LiveCheckResultDBAccessBase` for project-specific customizations.

    class LiveCheckResultDBAccessLegacy extends LiveCheckResultDBAccessBase
    {
    }
}

?>
