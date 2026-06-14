<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-htmlTemplateParameterBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-htmlTemplateParameter.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-htmlTemplateParameter.php` and extend `htmlTemplateParameterDBAccessBase` for project-specific customizations.

    class htmlTemplateParameterDBAccess extends htmlTemplateParameterDBAccessBase
    {
    }
}

?>
