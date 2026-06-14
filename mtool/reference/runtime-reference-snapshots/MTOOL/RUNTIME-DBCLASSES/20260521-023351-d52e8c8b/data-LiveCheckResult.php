<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-LiveCheckResultBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-LiveCheckResult.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-LiveCheckResult.php` and extend `LiveCheckResultDataBase` for project-specific customizations.

    class LiveCheckResultData extends LiveCheckResultDataBase
    {
    }
}

?>
