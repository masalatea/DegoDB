<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ServerBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Server.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Server.php` and extend `ServerDataBase` for project-specific customizations.

    class ServerData extends ServerDataBase
    {
    }
}

?>
