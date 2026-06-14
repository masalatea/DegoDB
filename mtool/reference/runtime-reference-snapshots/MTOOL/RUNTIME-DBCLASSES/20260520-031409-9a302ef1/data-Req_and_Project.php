<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-Req_and_ProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Req_and_Project.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Req_and_Project.php` and extend `Req_and_ProjectDataBase` for project-specific customizations.

    class Req_and_ProjectData extends Req_and_ProjectDataBase
    {
    }
}

?>
