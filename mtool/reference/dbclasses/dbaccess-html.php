<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-HtmlBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Html.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Html.php` and extend `HtmlDBAccessBase` for project-specific customizations.

    class HtmlDBAccess extends HtmlDBAccessBase
    {
    }
}

?>
