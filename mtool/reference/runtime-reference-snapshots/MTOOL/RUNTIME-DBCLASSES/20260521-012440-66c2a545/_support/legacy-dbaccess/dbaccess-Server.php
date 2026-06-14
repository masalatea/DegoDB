<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ServerBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Server.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Server.php` and extend `ServerDBAccessBase` for project-specific customizations.

    class ServerDBAccessLegacy extends ServerDBAccessBase
    {
    }
}

?>
