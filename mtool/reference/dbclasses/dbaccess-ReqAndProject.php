<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ReqAndProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ReqAndProject.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ReqAndProject.php` and extend `ReqAndProjectDBAccessBase` for project-specific customizations.

    class ReqAndProjectDBAccess extends ReqAndProjectDBAccessBase
    {
    }
}

?>
