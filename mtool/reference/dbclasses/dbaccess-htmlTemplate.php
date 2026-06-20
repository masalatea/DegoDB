<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-HtmlTemplateBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-HtmlTemplate.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-HtmlTemplate.php` and extend `HtmlTemplateDBAccessBase` for project-specific customizations.

    class HtmlTemplateDBAccess extends HtmlTemplateDBAccessBase
    {
    }
}

?>
