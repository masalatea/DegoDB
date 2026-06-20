<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ChattopicAndProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ChattopicAndProject.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ChattopicAndProject.php` and extend `ChattopicAndProjectDBAccessBase` for project-specific customizations.

    class ChattopicAndProjectDBAccess extends ChattopicAndProjectDBAccessBase
    {
    }
}

?>
