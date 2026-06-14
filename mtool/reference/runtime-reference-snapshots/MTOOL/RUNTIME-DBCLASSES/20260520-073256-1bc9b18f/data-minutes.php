<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-minutesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-minutes.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-minutes.php` and extend `minutesDataBase` for project-specific customizations.

    class minutesData extends minutesDataBase
    {
    }
}

?>
