<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ChattopicBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Chattopic.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Chattopic.php` and extend `ChattopicDBAccessBase` for project-specific customizations.

    class ChattopicDBAccess extends ChattopicDBAccessBase
    {
    }
}

?>
