<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-Req_and_ProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Req_and_Project.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Req_and_Project.php` and extend `Req_and_ProjectDBAccessBase` for project-specific customizations.

    class Req_and_ProjectDBAccess extends Req_and_ProjectDBAccessBase
    {
    }
}

?>
