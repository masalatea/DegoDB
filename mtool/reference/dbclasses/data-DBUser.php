<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DBUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DBUser.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DBUser.php` and extend `DBUserDataBase` for project-specific customizations.

    class DBUserData extends DBUserDataBase
    {
    }
}

?>
