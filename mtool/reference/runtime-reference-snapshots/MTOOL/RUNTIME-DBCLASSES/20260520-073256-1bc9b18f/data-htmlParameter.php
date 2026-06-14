<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-htmlParameterBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-htmlParameter.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-htmlParameter.php` and extend `htmlParameterDataBase` for project-specific customizations.

    class htmlParameterData extends htmlParameterDataBase
    {
    }
}

?>
