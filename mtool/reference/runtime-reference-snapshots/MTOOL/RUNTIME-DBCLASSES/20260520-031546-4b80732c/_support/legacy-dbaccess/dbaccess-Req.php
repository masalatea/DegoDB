<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ReqBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Req.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Req.php` and extend `ReqDBAccessBase` for project-specific customizations.

    class ReqDBAccessLegacy extends ReqDBAccessBase
    {
    }
}

?>
