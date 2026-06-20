<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-MinutesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Minutes.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Minutes.php` and extend `MinutesDataBase` for project-specific customizations.

    class MinutesData extends MinutesDataBase
    {
    }
}

?>
