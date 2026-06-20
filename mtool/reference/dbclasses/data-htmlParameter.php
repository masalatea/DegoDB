<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-HtmlParameterBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-HtmlParameter.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-HtmlParameter.php` and extend `HtmlParameterDataBase` for project-specific customizations.

    class HtmlParameterData extends HtmlParameterDataBase
    {
    }
}

?>
