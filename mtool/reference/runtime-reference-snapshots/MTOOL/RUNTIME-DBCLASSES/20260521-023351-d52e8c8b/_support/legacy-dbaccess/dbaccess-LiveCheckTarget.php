<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-LiveCheckTargetBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-LiveCheckTarget.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-LiveCheckTarget.php` and extend `LiveCheckTargetDBAccessBase` for project-specific customizations.

    class LiveCheckTargetDBAccessLegacy extends LiveCheckTargetDBAccessBase
    {
    }
}

?>
