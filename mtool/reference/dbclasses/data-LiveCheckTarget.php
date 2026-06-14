<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-LiveCheckTargetBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-LiveCheckTarget.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-LiveCheckTarget.php` and extend `LiveCheckTargetDataBase` for project-specific customizations.

    class LiveCheckTargetData extends LiveCheckTargetDataBase
    {
    }
}

?>
