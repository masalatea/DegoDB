<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ReqAndProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ReqAndProject.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ReqAndProject.php` and extend `ReqAndProjectDataBase` for project-specific customizations.

    class ReqAndProjectData extends ReqAndProjectDataBase
    {
    }
}

?>
