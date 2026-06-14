<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-htmlBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-html.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-html.php` and extend `htmlDataBase` for project-specific customizations.

    class htmlData extends htmlDataBase
    {
    }
}

?>
