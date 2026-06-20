<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-HtmlBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Html.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Html.php` and extend `HtmlDataBase` for project-specific customizations.

    class HtmlData extends HtmlDataBase
    {
    }
}

?>
