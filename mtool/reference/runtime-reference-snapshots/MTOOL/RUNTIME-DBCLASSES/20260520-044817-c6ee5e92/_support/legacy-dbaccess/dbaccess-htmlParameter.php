<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-htmlParameterBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-htmlParameter.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-htmlParameter.php` and extend `htmlParameterDBAccessBase` for project-specific customizations.

    class htmlParameterDBAccessLegacy extends htmlParameterDBAccessBase
    {
    }
}

?>
