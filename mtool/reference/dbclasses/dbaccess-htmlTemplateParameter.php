<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-HtmlTemplateParameterBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-HtmlTemplateParameter.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-HtmlTemplateParameter.php` and extend `HtmlTemplateParameterDBAccessBase` for project-specific customizations.

    class HtmlTemplateParameterDBAccess extends HtmlTemplateParameterDBAccessBase
    {
    }
}

?>
